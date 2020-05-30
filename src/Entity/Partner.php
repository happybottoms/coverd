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

    public const ROLE_VIEW_ALL = 'ROLE_PARTNER_VIEW_ALL';
    public const ROLE_VIEW_SELF = 'ROLE_PARTNER_VIEW_SELF';
    public const ROLE_EDIT = 'ROLE_PARTNER_EDIT';

    // State Machine Statuses
    public const STATUS_START = 'START';
    public const STATUS_APPLICATION_PENDING = 'APPLICATION_PENDING';
    public const STATUS_APPLICATION_PENDING_PRIORITY = 'APPLICATION_PENDING_PRIORITY';
    public const STATUS_NEEDS_PROFILE_REVIEW = 'NEEDS_PROFILE_REVIEW';
    public const STATUS_REVIEW_PAST_DUE = 'REVIEW_PAST_DUE';

    public const STATUSES = [
        self::STATUS_START,
        self::STATUS_APPLICATION_PENDING,
        self::STATUS_APPLICATION_PENDING_PRIORITY,
        self::STATUS_ACTIVE,
        self::STATUS_NEEDS_PROFILE_REVIEW,
        self::STATUS_REVIEW_PAST_DUE,
        self::STATUS_INACTIVE,
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
        if (isset($changes['legacyId'])) {
            $this->setLegacyId($changes['legacyId']);
            unset($changes['legacyId']);
        }

        if (isset($changes['title'])) {
            $this->setTitle($changes['title']);
            unset($changes['title']);
        }

        if (isset($changes['partnerType'])) {
            $this->setPartnerType($changes['partnerType']);
            unset($changes['partnerType']);
        }

        if (isset($changes['transition'])) {
            $this->applyTransition($changes['transition']);
            unset($changes['transition']);
        }

        $this->setUpdatedAt(new \DateTime());

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

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function applyTransition(string $transition)
    {
        $stateMachine = $this->workflowRegistry->get($this);
        try {
            $stateMachine->apply($this, $transition);
        } catch (LogicException $ex) {
            // TODO log this instead
            throw new \Exception(sprintf('%s is not a valid transition at this time. Exception thrown: %s', $transition, $ex->getMessage()));
        }
    }
}
