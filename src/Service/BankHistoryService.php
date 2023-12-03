<?php
namespace App\Service;

use App\Constants\CategoryKeys;
use App\Entity\BankHistory;
use App\Repository\BankHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Donor;
use Doctrine\ORM\QueryBuilder;
use phpDocumentor\Reflection\Types\Void_;



class BankHistoryService
{
    public function __construct(
        private BankHistoryRepository $bankHistoryRepository,
        private EntityManagerInterface $entityManagerInterface,
    ) {
    }



    public function countDrafts(): int
    {
        $count = $this->bankHistoryRepository->count(['is_draft' => true]);
        return $count;
    }


    public function updateBankHistory(BankHistory $bankHistory, bool $forceFlush = true): void
    {
        $this->entityManagerInterface->persist($bankHistory);
        if (!$bankHistory->isDraft()) {
            // ekstra czynności gdy jesteśmy w draft
        }
        if ($forceFlush) {
            $this->entityManagerInterface->flush();
        }
    }

    public function getLastDonorTransactions(Donor $donor, int $last = 5): Donor|array
    {
        $result = $this->bankHistoryRepository->findBy([
            'donor' => $donor,
            'category' => CategoryKeys::DAROWIZNA,
            'is_draft' => false,
        ], ['date' => 'DESC'], $last);
        return $result;
    }

    public function getPagerQueryBuilderForDonor(Donor $donor, array $queryCriteria = []): QueryBuilder
    {
        $queryCriteria['donor'] = $donor;
        return $this->bankHistoryRepository->getPagerQueryBuilder($queryCriteria);
    }
}