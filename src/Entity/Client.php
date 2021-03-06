<?php

namespace App\Entity;

use App\Entity\EAV\AttributedEntityTrait;
use App\Entity\Orders\BulkDistributionLineItem;
use App\Entity\ValueObjects\Name;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Moment\Moment;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Workflow\Registry;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientRepository")
 * @Gedmo\Loggable()
 */
class Client extends CoreEntity implements AttributedEntityInterface
{
    use AttributedEntityTrait;

    // State Machine Statuses
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_CREATION = 'CREATION';
    public const STATUS_INACTIVE = 'INACTIVE';
    public const STATUS_INACTIVE_BLOCKED = 'INACTIVE_(BLOCKED)';
    public const STATUS_INACTIVE_DUPLICATE = 'INACTIVE_(DUPLICATE)';
    public const STATUS_INACTIVE_EXPIRED = 'INACTIVE_(EXPIRED)';
    public const STATUS_NEEDS_REVIEW = 'NEEDS_REVIEW';
    public const STATUS_REVIEW_PAST_DUE = 'REVIEW_PAST_DUE';
    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_CREATION,
        self::STATUS_INACTIVE,
        self::STATUS_INACTIVE_BLOCKED,
        self::STATUS_INACTIVE_DUPLICATE,
        self::STATUS_INACTIVE_EXPIRED,
        self::STATUS_NEEDS_REVIEW,
        self::STATUS_REVIEW_PAST_DUE,
    ];

    public const ACTIVE_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_NEEDS_REVIEW,
        self::STATUS_REVIEW_PAST_DUE,
    ];

    public const ROLE_EDIT_ALL = "ROLE_CLIENT_EDIT_ALL";
    public const ROLE_MANAGE_OWN = "ROLE_CLIENT_MANAGE_OWN";
    public const ROLE_VIEW_ALL = "ROLE_CLIENT_VIEW_ALL";
    public const ROLE_CLIENT_OVERRIDE_EXPIRATIONS = "ROLE_CLIENT_OVERRIDE_EXPIRATIONS";

    /**
     * The unique auto incremented primary key.
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * The name value object which holds the
     * first and last name of the Client
     *
     * @var Name
     *
     * @ORM\Embedded(class="App\Entity\ValueObjects\Name", columnPrefix=false)
     * @Gedmo\Versioned
     */
    protected $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    protected $publicId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     * @Gedmo\Versioned
     */
    protected $birthdate;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\Versioned
     */
    protected $parentFirstName;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\Versioned
     */
    protected $parentLastName;

    /**
     * @var Partner|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Partner", inversedBy="clients")
     * @Gedmo\Versioned
     */
    protected $partner;

    /**
     * @var BulkDistributionLineItem[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Orders\BulkDistributionLineItem", mappedBy="client")
     */
    protected $distributionLineItems;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Gedmo\Versioned
     */
    protected $isExpirationOverridden;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     * @Gedmo\Versioned
     */
    protected $ageExpiresAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     * @Gedmo\Versioned
     */
    protected $distributionExpiresAt;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    protected $pullupDistributionMax;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $pullupDistributionCount;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Gedmo\Versioned
     */
    protected $status;

    /** @var Registry */
    protected $workflowRegistry;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $mergedTo;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", nullable=true)
     * @Gedmo\Versioned
     */
    protected $lastReviewedAt;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $isDuplicate;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $isExpired;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $isBlocked;

    public function __construct(Registry $workflowRegistry)
    {
        $this->attributes = new ArrayCollection();
        $this->name = new Name();
        $this->distributionLineItems = new ArrayCollection();
        $this->isExpirationOverridden = false;
        $this->pullupDistributionMax = 6;
        $this->pullupDistributionCount = 0;
        $this->workflowRegistry = $workflowRegistry;
        $this->status = self::STATUS_CREATION;
        $this->isDuplicate = false;
        $this->isExpired = false;
        $this->isBlocked = false;
    }

    public function __toString()
    {
        return sprintf('%s (%s)', $this->name, $this->getId());
    }


    public function getId()
    {
        return $this->id;
    }

    public function getPublicId(): ?string
    {
        return $this->publicId;
    }

    public function setPublicId(?string $publicId): void
    {
        $this->publicId = $publicId;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function setName(Name $name): void
    {
        if (!$name->isValid()) {
            throw new MissingMandatoryParametersException('Missing first and/or last name');
        }

        $this->name = $name;
    }

    public function setFirstName($firstName)
    {
        $this->name->setFirstname($firstName);
    }

    public function setLastName($lastName)
    {
        $this->name->setLastname($lastName);
    }

    /**
     * @return \DateTime
     */
    public function getBirthdate(): \DateTime
    {
        return $this->birthdate;
    }

    /**
     * @param \DateTime $birthdate
     */
    public function setBirthdate(\DateTime $birthdate): void
    {
        $this->birthdate = $birthdate;
    }

    /**
     * @return string
     */
    public function getParentFirstName(): ?string
    {
        return $this->parentFirstName;
    }

    /**
     * @param string $parentFirstName
     */
    public function setParentFirstName(?string $parentFirstName): void
    {
        $this->parentFirstName = $parentFirstName;
    }

    /**
     * @return string
     */
    public function getParentLastName(): ?string
    {
        return $this->parentLastName;
    }

    /**
     * @param string $parentLastName
     */
    public function setParentLastName(?string $parentLastName): void
    {
        $this->parentLastName = $parentLastName;
    }

    /**
     * @return bool
     */
    public function isExpirationOverridden(): bool
    {
        return $this->isExpirationOverridden;
    }

    /**
     * @param bool $isExpirationOverridden
     */
    public function setIsExpirationOverridden(bool $isExpirationOverridden): void
    {
        $this->isExpirationOverridden = $isExpirationOverridden;
    }

    /**
     * @return \DateTime
     */
    public function getAgeExpiresAt(): \DateTime
    {
        return $this->ageExpiresAt;
    }

    /**
     * @param \DateTime $ageExpiresAt
     */
    public function setAgeExpiresAt(\DateTime $ageExpiresAt): void
    {
        $this->ageExpiresAt = $ageExpiresAt;
    }

    /**
     * @return \DateTime
     */
    public function getDistributionExpiresAt(): ?\DateTime
    {
        return $this->distributionExpiresAt;
    }

    /**
     * @param \DateTime $distributionExpiresAt
     */
    public function setDistributionExpiresAt(?\DateTime $distributionExpiresAt): void
    {
        $this->distributionExpiresAt = $distributionExpiresAt;
    }

    public function getPullupDistributionMax(): ?int
    {
        return $this->pullupDistributionMax;
    }

    /**
     * @param int $pullupDistributionMax
     */
    public function setPullupDistributionMax(?int $pullupDistributionMax): void
    {
        $this->pullupDistributionMax = $pullupDistributionMax;
    }

    /**
     * @return int
     */
    public function getPullupDistributionCount(): int
    {
        return $this->pullupDistributionCount ?: 0;
    }

    /**
     * @param int $pullupDistributionCount
     */
    public function setPullupDistributionCount(int $pullupDistributionCount): void
    {
        $this->pullupDistributionCount = $pullupDistributionCount;
    }

    public function isPullupLimitReached(): bool
    {
        return $this->pullupDistributionCount >= $this->pullupDistributionMax;
    }

    public function getPartner(): ?Partner
    {
        return $this->partner;
    }

    public function setPartner(?Partner $partner): void
    {
        $this->partner = $partner;
    }

    /**
     * @return BulkDistributionLineItem[]|ArrayCollection
     */
    public function getDistributionLineItems()
    {
        return $this->distributionLineItems;
    }

    public function calculateAgeExpiration()
    {
        if ($this->isExpirationOverridden) {
            return;
        }

        $expiration = Moment::fromDateTime($this->getBirthdate());
        $this->ageExpiresAt = $expiration->addYears(4)->addMonths(1)->startOf('month');
    }

    public function calculateDistributionExpiration()
    {
        if ($this->isExpirationOverridden) {
            return;
        }

        $lines = $this->distributionLineItems->getValues();
        $first = array_reduce($lines, function (?\DateTime $carry, BulkDistributionLineItem $line) {
            if (is_null($carry) || $line->getOrder()->getDistributionPeriod() < $carry) {
                return $line->getOrder()->getDistributionPeriod();
            }
        }, null);

        if (!$first) {
            return null;
        }

        $firstMoment = Moment::fromDateTime($first);

        $this->distributionExpiresAt = $firstMoment->addYears(3)->addMonths(1)->startOf('month');
    }

    /**
     * Status is set by the workflow
     */
    public function setStatus(?string $status): void
    {
        if (empty($status)) {
            return;
        }

        if (!in_array($status, static::STATUSES)) {
            throw new \Exception(sprintf('%s is not a valid Status', $status));
        }

        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Merged To client id
     */
    public function setMergedTo(string $mergedTo): void
    {
        $this->mergedTo = $mergedTo;
    }

    public function getMergedTo(): string
    {
        return $this->mergedTo;
    }

    public function canReview(): bool
    {
        return in_array($this->status, [self::STATUS_REVIEW_PAST_DUE, self::STATUS_NEEDS_REVIEW]);
    }

    public function getLastReviewedAt(): ?\DateTime
    {
        return $this->lastReviewedAt;
    }

    public function setLastReviewedAt(?\DateTime $lastReviewedAt): void
    {
        $this->lastReviewedAt = $lastReviewedAt;
    }

    public function getLastCompleteDistributionLineItem(): ?BulkDistributionLineItem
    {
        $lines = $this->getDistributionLineItems();

        $lines = $lines->filter(function (BulkDistributionLineItem $line) {
            return $line->getOrder()->isComplete();
        });

        return array_reduce(
            $lines->getValues(),
            function (?BulkDistributionLineItem $carry, BulkDistributionLineItem $line) {
                if (!$carry) {
                    return $line;
                }
                return $carry->getDistributionPeriod() > $line->getDistributionPeriod() ? $carry : $line;
            },
            null
        );
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES);
    }

    /**
     * Partners can only transfer clients to themselves if they are (regular) Inactive or Creating
     */
    public function canPartnerTransfer(): bool
    {
        return in_array($this->status, [self::STATUS_INACTIVE, self::STATUS_CREATION]);
    }

    public function isDuplicate(): ?bool
    {
        return $this->isDuplicate;
    }

    public function setIsDuplicate(bool $isDuplicate): self
    {
        $this->isDuplicate = $isDuplicate;

        return $this;
    }

    public function isExpired(): ?bool
    {
        return $this->isExpired;
    }

    public function setIsExpired(bool $isExpired): self
    {
        $this->isExpired = $isExpired;

        return $this;
    }

    public function isBlocked(): ?bool
    {
        return $this->isBlocked;
    }

    public function setIsBlocked(bool $isBlocked): self
    {
        $this->isBlocked = $isBlocked;

        return $this;
    }
}
