<?php
namespace App\Security;

use App\Constants\UserRolesKeys;
use App\Entity\BankHistory;
use App\Entity\User;
use App\Entity\Donor;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BankHistoryVoter extends Voter
{
    // these strings are just invented: you can use anything
    const CONFIRM = 'bank_history.confirm_import';
    const LIST = 'bank_history.list';
    const UPDATE = 'bank_history.update';
    const DETAILS = 'bank_history.details';

    public function __construct(
        private Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::LIST , self::UPDATE, self::CONFIRM, self::DETAILS], true)) {
            return false;
        }

        if ($attribute == self::UPDATE && !($subject instanceof BankHistory)) {
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

        if ($this->security->isGranted(UserRolesKeys::ADMIN)) {
            return true;
        }

        /** @var BankHistory $bankHistory */
        $bankHistory = $subject;

        return match ($attribute) {
            self::LIST => $this->security->isGranted(UserRolesKeys::ADMIN),
            self::CONFIRM => $this->security->isGranted(UserRolesKeys::ADMIN),
            self::UPDATE => $this->security->isGranted(UserRolesKeys::ADMIN),
            self::DETAILS => $this->canViewDetails($bankHistory, $loggedInUser),
            default => throw new \LogicException('This code should not be reached!')
        };
    }


    private function canViewDetails(BankHistory $bankHistory, User $loggedInUser): bool
    {
        $donor = $bankHistory->getDonor();
        if ($donor === null) {
            return $this->security->isGranted(UserRolesKeys::ADMIN);
        }

        $user = $donor->getUser();
        if ($user != null) {
            if ($user->getId() === $loggedInUser->getId()) {
                return true;
            }
        }
        return $this->security->isGranted(UserRolesKeys::ADMIN);
    }

}