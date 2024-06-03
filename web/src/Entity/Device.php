<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Security\Voter\AccessUserDeviceVoter;
use App\Security\Voter\AccessVoter;
use App\Security\Voter\ActionVoter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity]
#[ORM\UniqueConstraint(columns: ['user_id', 'device_code'])]
#[ApiResource(
    normalizationContext: ['groups' => ['device:read']],
    denormalizationContext: ['groups' => ['device:write']],
)]
#[GetCollection(
    security: "is_granted('".User::ROLE_ADMIN."')"
)]
#[Post(
    security: "is_granted('".ActionVoter::DEVICE_CREATE."', object)"
)]
#[Delete(
    security: "is_granted('".ActionVoter::DEVICE_DELETE."', object)"
)]
#[Put(
    security: "is_granted('".User::ROLE_ADMIN."')"
)]
#[GetCollection(
    uriTemplate: '/devices/{email}/my-device',
    openapiContext: [
        'summary' => 'Get list of my devices.',
        'description' => 'Get list my devices.',
    ],
    security: "is_granted('".User::ROLE_ADMIN."') or user.getUserIdentifier() == request.get('email')"
)]
class Device implements TimestampableInterface
{
    use TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['device:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['device:read', 'user:read'])]
    private string $deviceCode;

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Groups(['device:read', 'device:write'])]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'devices')]
    private ?User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeviceCode(): string
    {
        return $this->deviceCode;
    }

    public function setDeviceCode(string $deviceCode): static
    {
        $this->deviceCode = $deviceCode;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
