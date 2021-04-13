<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Registry;

/**
 * Class Partner
 * @package App\Entities
 *
 * @ORM\Entity()
 * @Gedmo\Loggable()
 */
class Partner extends StorageLocation
{
    public const TYPE_AGENCY = 'AGENCY';
    public const TYPE_HOSPITAL = 'HOSPITAL';

    public const TYPES = [
        self::TYPE_AGENCY,
        self::TYPE_HOSPITAL,
    ];

    public const ROLE_EDIT_ALL = 'ROLE_PARTNER_EDIT_ALL';
    public const ROLE_MANAGE_OWN = 'ROLE_PARTNER_MANAGE_OWN';
    public const ROLE_VIEW_ALL = 'ROLE_PARTNER_VIEW_ALL';

    // State Machine Statuses
    public const STATUS_APPLICATION_PENDING = 'APPLICATION_PENDING';
    public const STATUS_APPLICATION_PENDING_PRIORITY = 'APPLICATION_PENDING_PRIORITY';
    public const STATUS_NEEDS_PROFILE_REVIEW = 'NEEDS_PROFILE_REVIEW';
    public const STATUS_REVIEW_PAST_DUE = 'REVIEW_PAST_DUE';
    public const STATUS_START = 'START';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_APPLICATION_PENDING,
        self::STATUS_APPLICATION_PENDING_PRIORITY,
        self::STATUS_INACTIVE,
        self::STATUS_NEEDS_PROFILE_REVIEW,
        self::STATUS_REVIEW_PAST_DUE,
        self::STATUS_START,
    ];

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\Versioned
     */
    protected $partnerType;

    /**
     * @var PartnerFulfillmentPeriod
     *
     * @ORM\ManyToOne(targetEntity="PartnerFulfillmentPeriod")
     * @Gedmo\Versioned
     */
    protected $fulfillmentPeriod;

    /**
     * @var PartnerDistributionMethod
     *
     * @ORM\ManyToOne(targetEntity="PartnerDistributionMethod")
     * @Gedmo\Versioned
     */
    protected $distributionMethod;

    /**
     * Number of previous months to average for use in forecasting.
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true);
     */
    protected $forecastAverageMonths;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true);
     */
    protected $legacyId;

    /**
     * @var PartnerProfile
     *
     * @ORM\OneToOne(
     *     targetEntity="PartnerProfile",
     *     inversedBy="partner",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $profile;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Client", mappedBy="partner")
     */
    protected $clients;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="partners")
     */
    protected $users;

    /** @var Registry */
    protected $workflowRegistry;

    public function __construct($title, Registry $workflowRegistry)
    {
        parent::__construct($title);

        $this->clients = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->status = self::STATUS_START;
        $this->workflowRegistry = $workflowRegistry;
    }

    public function __toString()
    {
        return sprintf('%s (%s)', $this->getTitle(), $this->id);
    }

    /**
     * @return string
     */
    public function getPartnerType()
    {
        return $this->partnerType;
    }

    public function setPartnerType(string $partnerType)
    {
        if (!in_array($partnerType, self::TYPES)) {
            throw new \Exception('%s is not a valid Partner Type', $partnerType);
        }

        $this->partnerType = $partnerType;
    }

    public function getFulfillmentPeriod(): PartnerFulfillmentPeriod
    {
        return $this->fulfillmentPeriod;
    }

    public function setFulfillmentPeriod(PartnerFulfillmentPeriod $fulfillmentPeriod = null)
    {
        $this->fulfillmentPeriod = $fulfillmentPeriod;
    }

    public function getDistributionMethod(): PartnerDistributionMethod
    {
        return $this->distributionMethod;
    }

    public function setDistributionMethod(PartnerDistributionMethod $distributionMethod = null)
    {
        $this->distributionMethod = $distributionMethod;
    }

    public function getForecastAverageMonths(): ?int
    {
        return $this->forecastAverageMonths;
    }

    public function setForecastAverageMonths(?int $forecastAverageMonths): void
    {
        $this->forecastAverageMonths = $forecastAverageMonths;
    }

    public function getLegacyId(): ?int
    {
        return $this->legacyId;
    }

    public function setLegacyId(int $legacyId = null): void
    {
        $this->legacyId = $legacyId;
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function applyChangesFromArray(array $changes): void
    {
        if (isset($changes['transition'])) {
            $this->applyTransition($changes['transition']);
            unset($changes['transition']);
        }

        parent::applyChangesFromArray($changes);
    }

    public function getProfile(): PartnerProfile
    {
        return $this->profile;
    }

    public function setProfile(PartnerProfile $profile): void
    {
        $this->profile = $profile;
    }

    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function getActiveClients(): Collection
    {
        return $this->clients->filter(function (Client $client) {
            return $client->isActive();
        });
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function applyTransition(string $transition): void
    {
        $stateMachine = $this->workflowRegistry->get($this);
        try {
            $stateMachine->apply($this, $transition);
        } catch (LogicException $ex) {
            // TODO log this instead
            throw new \Exception(
                sprintf(
                    '%s is not a valid transition at this time. Exception thrown: %s',
                    $transition,
                    $ex->getMessage()
                )
            );
        }
    }

    public function isReviewing(): bool
    {
        return in_array($this->status, [self::STATUS_NEEDS_PROFILE_REVIEW, self::STATUS_REVIEW_PAST_DUE]);
    }

    public function canPlaceOrders(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_NEEDS_PROFILE_REVIEW]);
    }

    /**
     * @return PartnerContact[]|ArrayCollection
     */
    public function getProgramContacts(): ArrayCollection
    {
        $programContacts = $this->contacts->filter(function (PartnerContact $contact) {
            return $contact->isProgramContact();
        });

        return $programContacts;
    }

    /**
     * @return PartnerContact
     */
    protected function createNewContact(): StorageLocationContact
    {
        return new PartnerContact();
    }
}
