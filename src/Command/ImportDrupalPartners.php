<?php

namespace App\Command;

use App\Entity\Partner;
use App\Entity\PartnerContact;
use App\Entity\PartnerDistributionMethod;
use App\Entity\PartnerFulfillmentPeriod;
use App\Entity\PartnerProfile;
use App\Entity\StorageLocationAddress;
use App\Entity\ValueObjects\Name;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\WorkflowInterface;

class ImportDrupalPartners extends Command
{
    protected static $defaultName = 'app:migrate:partner';
    protected $statusMap = [
        6 => Partner::STATUS_APPLICATION_PENDING,
        7 => Partner::STATUS_ACTIVE,
        8 => Partner::STATUS_NEEDS_PROFILE_REVIEW,
        9 => Partner::STATUS_REVIEW_PAST_DUE,
        10 => Partner::STATUS_INACTIVE,
        11 => Partner::STATUS_APPLICATION_PENDING_PRIORITY,
    ];
    protected $distributionMethodMap = [
        'volunteers' => 'Volunteers',
        'staff' => 'Program Staff',
        'courier' => 'Courier Service',
        'happybottoms' => 'HappyBottoms delivery service',
    ];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Registry
     */
    private $reg;

    public function __construct(EntityManagerInterface $em, Registry $reg)
    {
        $this->em = $em;
        $this->reg = $reg;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(
                'Creates the Partner custom fields that match the Drupal Portal'
            )
            ->addArgument(
                'filepath',
                InputArgument::REQUIRED,
                'JSON export file from Drupal'
            )
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Execute actual import. Otherwise, actions will only be reported.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->loadFulfillmentPeriods();
        $this->loadDistributionMethods();

        $rawContent = file_get_contents($input->getArgument('filepath'));
        $partnersIn = json_decode($rawContent);
        foreach ($partnersIn as $p) {
            $partner = new Partner($p->title, $this->reg);
            $partner->setTitle($p->title);
            $partner->setPartnerType(
                $p->field_agency_type->und[0] === 'hospital' ?
                    Partner::TYPE_HOSPITAL :
                    Partner::TYPE_AGENCY
            );
            $partner->setStatus($this->resolveStatus($p->workflow));
            $partner->setLegacyId($p->nid);

            $method = $p->field_pick_up_method ?
                $this->resolveDistributionMethod($p->field_pick_up_method->und[0]->value) :
                $this->em->getRepository(PartnerDistributionMethod::class)->findOneBy(['name' => 'Volunteers']);

            $partner->setDistributionMethod($method);

            $fulfillmentPeriod = $this->em
                ->getRepository(PartnerFulfillmentPeriod::class)
                ->findOneBy(['name' => 'Week 1']);

            $partner->setFulfillmentPeriod($fulfillmentPeriod);

            $partnerAddress = new StorageLocationAddress();
            $partnerAddress->setStreet1($p->field_mailing_address->und[0]->thoroughfare);
            $partnerAddress->setStreet2($p->field_mailing_address->und[0]->premise);
            $partnerAddress->setCity($p->field_mailing_address->und[0]->locality);
            $partnerAddress->setState($p->field_mailing_address->und[0]->administrative_area);
            $partnerAddress->setPostalCode($p->field_mailing_address->und[0]->postal_code);
            $partnerAddress->setCountry("United States of America");

            $partner->setAddress($partnerAddress);

            $execContact = new PartnerContact();
            $execContact->setName(Name::fromString($p->field_executive_director->und[0]->value));
            $execContact->setTitle('Executive Director');
            $execContact->setPhoneNumber($p->field_executive_director_phone->und[0]->value);
            if ($p->field_executive_director_email) {
                $execContact->setEmail($p->field_executive_director_email->und[0]->email);
            }

            $partner->addContact($execContact);

            $progContact = new PartnerContact();
            $progContact->setName(Name::fromString($p->field_program_contact->und[0]->value));
            $progContact->setTitle('Program Director');
            if ($p->field_program_phone) {
                $progContact->setPhoneNumber($p->field_program_phone->und[0]->value);
            }
            if ($p->field_program_email) {
                $progContact->setEmail($p->field_program_email->und[0]->email);
            }
            $progContact->setIsProgramContact(true);

            $partner->addContact($progContact);

            if ($p->field_pick_up_name) {
                $pickupContact = new PartnerContact();
                $pickupContact->setName(Name::fromString($p->field_pick_up_name->und[0]->value));
                $pickupContact->setTitle('Pick-up');
                $pickupContact->setPhoneNumber($p->field_pick_up_phone->und[0]->value);
                if ($p->field_pick_up_email) {
                    $pickupContact->setEmail($p->field_pick_up_email->und[0]->email);
                }

                $partner->addContact($pickupContact);
            }

            $profile = new PartnerProfile();
            $partner->setProfile($profile);

            $this->em->persist($partner);
        }

        $this->em->flush();

        return 0;
    }

    private function resolveStatus(int $drupalStatusId): string
    {
        $status = $this->statusMap[$drupalStatusId];

        if (!$status) {
            throw new \Exception(sprintf('Unknown status ID: %s', $drupalStatusId));
        }

        return $status;
    }

    private function resolveDistributionMethod(string $drupalMethod): PartnerDistributionMethod
    {
        $name = $this->distributionMethodMap[$drupalMethod];
        return $this->em->getRepository(PartnerDistributionMethod::class)->findOneBy(['name' => $name]);
    }

    private function loadFulfillmentPeriods(): void
    {
        $periods = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];

        foreach ($periods as $period) {
            $fp = new PartnerFulfillmentPeriod($period);
            $this->em->persist($fp);
        }

        $this->em->flush();
    }

    private function loadDistributionMethods()
    {
        $methods = [
            'volunteers' => 'Volunteers',
            'staff' => 'Program Staff',
            'courier' => 'Courier Service',
            'happybottoms' => 'HappyBottoms delivery service',
        ];

        foreach ($methods as $method) {
            $dm = new PartnerDistributionMethod($method);
            $this->em->persist($dm);
        }

        $this->em->flush();
    }
}
