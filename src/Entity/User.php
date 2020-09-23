<?php

namespace App\Entity;

use App\Entity\ValueObjects\Name;
use App\Exception\UserInterfaceException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\Table(name="users")
 */
class User extends CoreEntity implements UserInterface
{
    public const ROLE_VIEW = 'ROLE_USER_VIEW';
    public const ROLE_EDIT = 'ROLE_USER_EDIT';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_USER = 'ROLE_USER';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * The name value object which holds the
     * first and last name of the user
     * @ORM\Embedded(class="App\Entity\ValueObjects\Name", columnPrefix=false)
     *
     * @var Name
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Group", inversedBy="users")
     *
     * @var Group[]|Collection
     */
    protected $groups;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var string The unhashed password
     */
    private $plainTextPassword;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Partner", inversedBy="users")
     *
     * @var ArrayCollection|Partner[]
     */
    protected $partners;

    /**
     * @var Partner
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Partner")
     */
    protected $activePartner;

    public function __construct($email)
    {
        $this->email = $email;
        $this->groups = new ArrayCollection();
        $this->partners = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    /**
     * @throws MissingMandatoryParametersException
     */
    public function setName(Name $name): self
    {
        if (!$name->isValid()) {
            throw new MissingMandatoryParametersException("Missing first and/or last name");
        }

        $this->name = $name;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->name;
    }

    /**
     * @return Group[]
     */
    public function getGroups(): array
    {
        return $this->groups->toArray();
    }

    /**
     * @param Group[]|Collection $groups
     * @return $this
     */
    public function setGroups($groups): self
    {
        if (is_array($groups) && !$groups instanceof Collection) {
            $groups = new ArrayCollection($groups);
        }

        $this->groups = $groups;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = [];
        foreach ($this->groups as $group) {
            $roles += $group->getRoles();
        }

        // guarantee every user at least has ROLE_USER
        $roles[] = self::ROLE_USER;

        return array_unique($roles);
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * This method assumes that the $password is already encoded
     *
     * Once set, the plainTextPassword will be deleted.
     *
     * {@internal use self::setPlainTextPassword instead}
     * @param string $encryptedPassword
     * @return User
     */
    public function setPasswordFromEncrypted(string $encryptedPassword): self
    {
        $this->password = $encryptedPassword;
        $this->plainTextPassword = null;

        return $this;
    }

    public function getPlainTextPassword(): ?string
    {
        return $this->plainTextPassword;
    }
    public function setPlainTextPassword(string $plainTextPassword): self
    {
        $this->plainTextPassword = $plainTextPassword;
        $this->password = null;

        return $this;
    }

    public function getPartners(): ?array
    {
        return $this->partners->getValues();
    }

    public function setPartners(array $partners): self
    {
        $this->partners = new ArrayCollection($partners);

        return $this;
    }

    public function isAssignedToPartner(Partner $partner)
    {
        foreach ($this->partners as $p) {
            if ($p->getId() === $partner->getId()) {
                return true;
            }
        }

        return false;
    }

    public function getActivePartner(): ?Partner
    {
        if (!$this->activePartner) {
            if ($this->isAdmin()) {
                return null;
            }

            $this->activePartner = $this->partners->first();
        }

        return $this->activePartner ?: null;
    }

    public function setActivePartner(?Partner $activePartner): void
    {
        if (!$this->isAssignedToPartner($activePartner)) {
            throw new UserInterfaceException('User is not assigned to that partner.');
        }

        $this->activePartner = $activePartner;
    }

    public function isAdmin()
    {
        return in_array(self::ROLE_ADMIN, $this->getRoles());
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        // not needed when using the "bcrypt" or "argon2i" algorithm in security.yaml
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainTextPassword = null;
    }
}
