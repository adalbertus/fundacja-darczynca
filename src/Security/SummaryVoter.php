<?php
namespace App\Security;

use App\Constants\UserRolesKeys;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SummaryVoter extends Voter
{
    // these strings are just invented: you can use anything
    const TOTALS = 'summary.totals';

    public function __construct(
        private Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::TOTALS], true)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($this->security->isGranted(UserRolesKeys::ADMIN)) {
            return true;
        }

        return match ($attribute) {
            self::TOTALS => $this->security->isGranted(UserRolesKeys::ADMIN),
            default => throw new \LogicException('This code should not be reached!')
        };
    }
}