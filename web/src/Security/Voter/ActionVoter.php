<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Device;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ActionVoter extends Voter
{
    public const DEVICE_CREATE = 'DEVICE_CREATE';
    public const DEVICE_DELETE = 'DEVICE_DELETE';

    public const CREATE_GROUP = [
        self::DEVICE_CREATE,
        self::DEVICE_DELETE,
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, self::CREATE_GROUP, true) && $subject instanceof UserInterface;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface || !$subject instanceof Device) {
            return false;
        }

        if ($this->isAdmin($user)) {
            return true;
        }

        return $user->getUserIdentifier() === $subject->getUser()->getUserIdentifier();
    }

    private function isAdmin(UserInterface $user): bool
    {
        return \in_array(User::ROLE_ADMIN, $user->getRoles());
    }
}
