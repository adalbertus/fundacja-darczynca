<?php
namespace App\Service;

use App\Constants\CategoryKeys;
use App\Entity\BaseEntity;
use App\Entity\DescriptionRegexp;
use App\Entity\Donor;
use App\Entity\RegexpPatternInterface;
use App\Exception\ArgumentEmptyException;
use App\Repository\BankHistoryRepository;
use App\Repository\DescriptionRegexpRepository;
use App\Repository\DonorRepository;
use App\Service\SupportedBankFormat;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class BankHistoryImportService
{
    private array $dataCache;
    public function __construct(
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private BankHistoryCsvReaderService $bankHistoryCsvReader,
        private BankHistoryRepository $bankHistoryRepository,
        private DescriptionRegexpRepository $descriptionRegexpRepository,
        private DonorRepository $donorRepository,
        private DonorService $donorService,
    ) {
        $this->dataCache = [];
    }

    public function _getCachedData(string $cacheKey): array
    {
        if (array_key_exists($cacheKey, $this->dataCache)) {
            return $this->dataCache[$cacheKey];
        }
        switch ($cacheKey) {
            case DescriptionRegexp::class:
                $this->dataCache[DescriptionRegexp::class] = $this->descriptionRegexpRepository->findAll();
                break;
            case Donor::class:
                $this->dataCache[Donor::class] = $this->donorRepository->findAll();
                break;
        }
        return $this->dataCache[$cacheKey];
    }

    public function _addToCache(string $cacheKey, BaseEntity $entity): void
    {
        if (array_key_exists($cacheKey, $this->dataCache)) {
            array_push($this->dataCache[$cacheKey], $entity);
        }
    }

    private function _bulkInsert(array $bankHistoryList): void
    {
        foreach ($bankHistoryList as $bankHistory) {
            $this->entityManager->persist($bankHistory);
        }
        $this->entityManager->flush(); // Persist objects that did not make up an entire batch
        $this->entityManager->clear();
    }

    /**
     * Import danych CSV
     * @param string $filename
     * @return array 
     */
    public function cleanOldAndImportUniqueFromCSV(string $filename): array
    {
        $this->cleanOldData();
        $bankHistoryList = $this->bankHistoryCsvReader->parse($filename, SupportedBankFormat::SantanderBank);
        $importCount = $this->saveOnlyUniqueCsv($bankHistoryList);

        return [
            'csv_records' => count($bankHistoryList),
            'import_records' => $importCount,
        ];
    }

    public function analyzeAndSave(): void
    {
        // $batchSize = $this->config->batchSize();
        $bankHistoryDraftList = $this->bankHistoryRepository->findAllDraft();
        foreach ($bankHistoryDraftList as $draftBankHistory) {
            $nDesc = StringHelper::normalize($draftBankHistory->getDescription());

            // rozpoznanie kategorii i podkategorii na bazie opisu przelewu (np. opłaty bankowe,
            // składki (ale bez analizy jaki rejon lub członek))
            $categories = $this->identyfyCategories($nDesc);
            $draftBankHistory->setCategory($categories['category']);
            $draftBankHistory->setSubCategory($categories['sub_category']);

            if ($draftBankHistory->getCategory() == CategoryKeys::DAROWIZNA) {
                $donor = $this->identyfyDonor($draftBankHistory->getSenderName());
                if ($donor) {
                    $draftBankHistory->setDonor($donor);
                }
            }

            $this->entityManager->persist($draftBankHistory);
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function findAllDraftAndValidate(): array
    {
        $list = $this->bankHistoryRepository->findAllDraft();
        foreach ($list as $bankHistory) {
            $this->validator->validate($bankHistory);
        }
        return $list;
    }


    public function acceptAllDraft(): void
    {
        $this->donorService->deleteUnusedAutoCreated();
        $this->bankHistoryRepository->acceptAllDraft();
    }

    public function cleanOldData(): void
    {
        $this->bankHistoryRepository->deleteAllDraft();
    }

    public function saveOnlyUniqueCsv($bankHistoryList = []): int
    {
        // aby zapobiec duplikatom sprawdzam czy w bank_history znajdują się
        // rekordy o takim samym md5 jak te importowane i ignoruje duplikaty

        $md5List = array_map(function ($bh) {
            return $bh->getMd5();
        }, $bankHistoryList);
        // wyszukanie wszystkich istniejących bank_history z importowanym md5
        $existingMd5 = $this->bankHistoryRepository->findExistingMd5FromMd5List($md5List);

        $uniqueBankHistoryList = [];
        foreach ($bankHistoryList as $bankHistory) {
            if (in_array($bankHistory->getMd5(), $existingMd5)) {
                continue;
            }
            $uniqueBankHistoryList[] = $bankHistory;
        }
        // zapisanie tych bankHistory, które się nie powtarzają
        $this->_bulkInsert($uniqueBankHistoryList);

        return count($uniqueBankHistoryList);
    }

    public function identyfyCategories(string $desc): array
    {
        // Podstawowe rozpoznawanie kategorii, tylko takie, które nie zależy od kontekstu

        $allRegexp = $this->_getCachedData(DescriptionRegexp::class);

        foreach ($allRegexp as $rdesc) {
            if (empty($rdesc->getExpression())) {
                continue;
            }

            $match = self::preg_match($rdesc->getExpression(), $desc);
            if ($match) {
                return ['category' => $rdesc->getCategory(), 'sub_category' => $rdesc->getSubCategory()];
            }
        }
        return ['category' => CategoryKeys::BRAK, 'sub_category' => CategoryKeys::BRAK];
    }

    public function entityRegexpMatch(RegexpPatternInterface|array $entities, string $ndesc): mixed
    {
        foreach ($entities as $entity) {
            /** @var RegexpPatternInterface $entity */
            $pattern = $entity->buildRegexpPattern();
            if ($pattern === false) {
                continue;
            }
            $match = self::preg_match($pattern, $ndesc);
            if ($match) {
                return $entity;
            }
        }
        return false;
    }

    public function identyfyDonor(string $senderName): mixed
    {
        // wczytuje wszystkich darczyńców, żeby nie trzeba było 2 razy uderzać do bazy
        $donors = $this->_getCachedData(Donor::class);

        $donorsMatched = $this->entityRegexpMatch($donors, StringHelper::normalize($senderName));
        if ($donorsMatched === false) {
            return $this->_createDonorAndAddToCache($senderName);
        }
        return $donorsMatched;
    }

    /**
     * Utworzenie nowego darczyńcy na bazie $senderName
     *
     * @param  mixed $senderName
     * @return Donor
     */
    public function _createDonorAndAddToCache(string $senderName): Donor
    {
        $donor = $this->donorService->createDonor($senderName);
        $this->_addToCache(Donor::class, $donor);
        return $donor;
    }

    private static function preg_match($expression, $text): mixed
    {
        try {
            if (empty($expression)) {
                return false;
            }
            if (empty($text)) {
                throw new ArgumentEmptyException();
            }
            // normalizacja usuwa również taki zapis .{1,5} - czyli wystąpienie
            // dowolnego znaku od 1 do 5 razy
            // $normExp = StringHelper::normalize($expression);
            // usuwam tylko polskie znaczki
            $normExp = StringHelper::normalizeRegexPattern($expression);
            $match = preg_match("/$normExp/i", $text);
            return $match;
        } catch (Exception $ex) {
            throw new \ErrorException("Nie udało się wykonać wyszukiwania. Wyrażenie [" . $expression . "], znormalizowane [" . $normExp . "], tekst [" . $text . "]");
        }
    }
}