<?php
namespace App\Security;

use App\Constants\UserRolesKeys;
use App\Entity\Donor;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DonorVoter extends Voter
{
    // these strings are just invented: you can use anything
    const INDEX = 'donor.index';
    const DETAILS = 'donor.details';
    const TRANSACTIONS = 'donor.transactions';
    const CREATE = 'donor.create';
    const UPDATE = 'donor.update';

    public function __construct(
        private Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::INDEX, self::DETAILS, self::TRANSACTIONS, self::UPDATE, self::CREATE], true)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $loggedInUser = $token->getUser();

        if (!$loggedInUser instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // if ($this->security->isGranted(UserRolesKeys::SUPER_ADMIN)) {
        //     return true;
        // }

        if ($this->security->isGranted(UserRolesKeys::ADMIN)) {
            return true;
        }

        /** @var Donor $donor */
        $donor = $subject;

        return match ($attribute) {
            self::INDEX => $this->canIndex(),
            self::DETAILS => $this->canViewDetails($donor, $loggedInUser),
            self::TRANSACTIONS => $this->canViewTransactions($donor, $loggedInUser),
            self::CREATE => $this->canCreate($loggedInUser),
            self::UPDATE => $this->canUpdate($donor, $loggedInUser),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canIndex(): bool
    {
        return $this->security->isGranted(UserRolesKeys::ADMIN);
    }

    private function canViewDetails(Donor $donor, User $loggedInUser): bool
    {
        $user = $donor->getUser();
        if ($user != null) {
            if ($user->getId() === $loggedInUser->getId()) {
                return true;
            }
        }
        return $this->security->isGranted(UserRolesKeys::ADMIN);
    }

    private function canViewTransactions(Donor $donor, User $loggedInUser): bool
    {
        return $this->canViewDetails($donor, $loggedInUser);
    }

    private function canCreate(User $loggedInUser): bool
    {
        return $this->security->isGranted(UserRolesKeys::ADMIN);
    }

    private function canUpdate(Donor $donor, User $loggedInUser): bool
    {
        return $this->security->isGranted(UserRolesKeys::ADMIN);
    }
}