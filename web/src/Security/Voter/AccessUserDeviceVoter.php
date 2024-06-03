<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class AccessUserDeviceVoter extends Voter
{
    public const DEVICE_DEVICE_ACCESS = 'DEVICE_DEVICE_ACCESS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::DEVICE_DEVICE_ACCESS;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        var_dump($subject::class);


        if (null === $user || !$subject instanceof User) {
            return false;
        }

        if ($this->isAdmin($user)) {
            return true;
        }

        var_dump($user->getId(), $subject->getId());

        return $user->getId() === $subject->getId();
    }

    private function isAdmin(UserInterface $user): bool
    {
        return \in_array(User::ROLE_ADMIN, $user->getRoles());
    }
}
