<?php
namespace App\Security;

use App\Constants\AccountKeys;
use App\Constants\UserRolesKeys;
use App\Entity\User;
use App\Entity\Deposit;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    // these strings are just invented: you can use anything
    const VIEW = 'user.view';
    const UPDATE = 'user.update';
    const SEARCH = 'user.search';

    public function __construct(
        private Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::UPDATE, self::SEARCH])) {
            return false;
        }

        if (is_null($subject)) {
            return true;
        }

        if (!$subject instanceof User) {
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

        // you know $subject is a Post object, thanks to `supports()`
        /** @var User $loggedInUser */
        $user = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($user, $loggedInUser),
            self::UPDATE => $this->canUpdate($user, $loggedInUser),
            self::SEARCH => $this->canSearch(),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canView(User $user, User $loggedInUser): bool
    {
        if ($this->canUpdate($user, $loggedInUser)) {
            return true;
        }

        return $user->getId() === $loggedInUser->getId();
    }

    private function canUpdate(User $user, User $loggedInUser): bool
    {
        return $user->getId() === $loggedInUser->getId();
    }

    private function canSearch(): bool
    {
        if ($this->security->isGranted(UserRolesKeys::ADMIN)) {
            return true;
        }

        return false;
    }
}