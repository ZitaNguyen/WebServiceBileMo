<?php

namespace App\Security;

use App\Entity\Client;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    const ACCESS = 'access';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::ACCESS])) {
            return false;
        }

        // only vote on `User` objects
        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $client = $token->getUser();

        if (!$client instanceof Client) {
            // the user must be logged in; if not, deny access
            return false;
        }

        /** @var User $user */
        $user = $subject;

        return match($attribute) {
            self::ACCESS => $this->canAccess($user, $client),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canAccess(User $user, CLient $client): bool
    {
        // this assumes that the Post object has a `getOwner()` method
        return $client === $user->getClient();
    }
}