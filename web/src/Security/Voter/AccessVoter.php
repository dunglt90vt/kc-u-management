<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class AccessVoter extends Voter
{
    public const USER_ACCESS = 'USER_ACCESS';

    public const DEVICE_ACCESS = 'DEVICE_ACCESS';

    public const ACCESS_GROUP = [
        self::USER_ACCESS,
        self::DEVICE_ACCESS,
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, self::ACCESS_GROUP, true) && $subject instanceof UserInterface;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface || !$subject instanceof UserInterface) {
            return false;
        }

        if ($this->isAdmin($user)) {
            return true;
        }

        return $user->getUserIdentifier() === $subject->getUserIdentifier();
    }

    private function isAdmin(UserInterface $user): bool
    {
        return \in_array(User::ROLE_ADMIN, $user->getRoles());
    }
}
