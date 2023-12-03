<?php
namespace App\Service;

use App\Constants\UserRolesKeys;
use App\Exception\DonorDeletionException;
use App\Entity\User;
use App\Entity\Donor;
use App\Entity\DonorSearchPattern;
use App\Repository\BankHistoryRepository;
use App\Repository\DonorRepository;
use App\Service\StringHelper;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;

class DonorService
{
    public function __construct(
        private DonorRepository $donorRepository,
        private EntityManagerInterface $entityManager,
        private UserService $userService,
        private BankHistoryRepository $bankHistoryRepository
    ) {
    }


    /**
     * Utworzenie darczyńcy na podstawie nadawcy przelewu.
     *
     * @param  mixed $senderName Nadawca przelewu
     * @param  mixed $autoFlush Automatyczne zapisywanie nowej encji
     * @return Donor
     */
    public function createDonor(string $senderName, bool $autoFlush = true): Donor
    {
        $nextAutoNumber = $this->donorRepository->count(['is_auto' => true]) + 1;

        $donor = (new Donor())
            ->setName("Darczyńca nr {$nextAutoNumber}")
            ->setComment("Darczyńca utworzony automatycznie")
            ->setIsAuto(true);

        $shortenSenderName = substr(StringHelper::normalize($senderName), 0, 32);
        $shortenSenderName = StringHelper::normalizeRegexPattern($shortenSenderName);
        $donorSearchPattern = new DonorSearchPattern();
        $donorSearchPattern->setSearchPattern($shortenSenderName);

        $donor->addDonorSearchPattern($donorSearchPattern);

        $this->entityManager->persist($donor);

        if ($autoFlush) {
            $this->entityManager->flush();
        }

        return $donor;
    }

    public function deleteUnusedAutoCreated()
    {
        $this->donorRepository->deleteUnusedAutoCreated();
    }

    public function addOrRemoveUserBasedOnEmails(Donor $donor, string $emails): void
    {
        $results = $this->userService->createUsersByEmailIfNeeded($emails, UserRolesKeys::DONOR);
        if (count($results) > 0) {
            $current = current($results);
            $donor->setUser($current['user']);
            $this->userService->setRandomPasswordAndSendNewAccountNotification($current['user'], false, 'emails/donor/new_account.html.twig');
        } else {
            $donor->setUser(null);
        }
    }

    public function getDonorByUser(User $user): ?Donor
    {
        return $this->donorRepository->findOneBy(['user' => $user]);
    }

    public function deleteAndTransferIfNeeded(Donor $toBeDeleted, ?Donor $toBeTransfered): void
    {
        if ($toBeTransfered != null) {
            $this->bankHistoryRepository->moveTransactions($toBeDeleted, $toBeTransfered);
        }

        $count = $this->bankHistoryRepository->count(['donor' => $toBeDeleted]);
        if ($count > 0) {
            throw new DonorDeletionException("Nie można usunąć darczyńcy {$toBeDeleted} ponieważ ma przypisane transakcje bankowe!");
        }

        $this->entityManager->remove($toBeDeleted);
        $this->entityManager->flush();
    }
}