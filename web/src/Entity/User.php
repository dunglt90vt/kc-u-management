<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Security\Voter\AccessVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\Unique;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
)]
#[GetCollection(
    security: "is_granted('".self::ROLE_ADMIN."')"
)]
#[Post(
    security: "is_granted('".self::ROLE_ADMIN."')"
)]
#[Delete(
    security: "is_granted('".self::ROLE_ADMIN."')"
)]
#[Get(
    security: "is_granted('".AccessVoter::USER_ACCESS."', object)"
)]
#[Put(
    security: "is_granted('".AccessVoter::USER_ACCESS."', object)"
)]
class User implements UserInterface, TimestampableInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableEntityTrait;

    public const ROLE_ADMIN = 'ROLE_ADMIN';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Unique]
    #[Groups(['user:read', 'user:write'])]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:write'])]
    private ?string $password = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Unique]
    #[Groups(['user:read', 'user:write'])]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    #[Groups(['user:read', 'user:write'])]
    private array $roles = [];

    #[ORM\Column(nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?int $limitDevice = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?\DateTimeInterface $lastLogin = null;

    /**
     * @var Collection<Device>
     */
    #[ORM\OneToMany(targetEntity: Device::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Groups(['user:read'])]
    private Collection $devices;

    public function __construct()
    {
        $this->devices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getLimitDevice(): ?int
    {
        return $this->limitDevice;
    }

    public function setLimitDevice(?int $limitDevice): static
    {
        $this->limitDevice = $limitDevice;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): static
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getDevices(): Collection
    {
        return $this->devices;
    }

    /**
     * @param Device[] $devices
     */
    public function setDevices(array $devices): static
    {
        $this->devices = new ArrayCollection($devices);

        return $this;
    }

    public function addDevice(Device $device): static
    {
        if (!$this->devices->contains($device) && $this->limitDevice < $this->devices->count()) {
            $this->devices->add($device);
        }

        return $this;
    }

    public function removeDevice(Device $device): static
    {
        if ($this->devices->contains($device)) {
            $this->devices->remove($device);
        }

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function hasRole(string $role): bool
    {
        return \in_array($role, $this->roles, true);
    }
}
