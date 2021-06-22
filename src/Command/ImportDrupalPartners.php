<?php

namespace App\Command;

use App\Entity\EAV\Attribute;
use App\Entity\EAV\AttributeDefinition;
use App\Entity\EAV\EavAddress;
use App\Entity\EAV\EavFile;
use App\Entity\Partner;
use App\Entity\PartnerContact;
use App\Entity\PartnerDistributionMethod;
use App\Entity\PartnerFulfillmentPeriod;
use App\Entity\PartnerProfile;
use App\Entity\StorageLocationAddress;
use App\Entity\ValueObjects\Name;
use App\Entity\ZipCounty;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Workflow\Registry;

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

    protected $optionMap = [
        'grants_foundation' => 'foundations',
        'grants_state' => 'state',
        'grants_federal' => 'federal',
        'corp_donations' => 'corporate',
        'indv_donations' => 'individual',
        'drives' => 'drives_self',
        'drive_others' => 'drives_others',
        'onsite_residential' => 'residential',
        'homeless_shelter' => 'homeless',
        'dv_shelter' => 'domestic',
    ];

    protected $fieldMap = [
        'shortname' => 'field_acronym',
        'designation' => 'field_agency_type',
        'designation_upload' => 'field_proof_of_agency_status',
        'mission' => 'field_agency_mission',
        'website' => 'field_website',
        'facebook' => 'field_facebook_page',
        'twitter' => 'field_twitter_account',
        'founded_year' => 'field_year_founded',
        'is_990' => 'field_form_990',
        '990_file' => 'field_990_scan',
        'program_name' => 'field_program_name',
        'program_description' => 'field_program_description',
        'program_length' => 'field_program_age',
        'program_has_case_management' => 'field_has_case_management',
        'program_is_evidence_based' => 'field_is_evidence_based',
        'program_evidence_based_description' => 'field_evidence_based_description',
        'program_improve_client' => 'field_program_client_improvment',
        'program_diaper_use' => 'field_diaper_use',
        'program_diaper_use_other' => 'field_diaper_use_other',
        'program_already_distribute' => 'field_currently_provide_diapers',
        'program_turn_away' => 'field_turn_away_count',
        'program_address' => 'field_program_address',
        'max_serve' => 'field_max_serve',
        'incorporate_services' => 'field_incorporate_plan',
        'has_designated_staff' => 'field_has_designated_staff',
        'has_designated_staff_position' => 'field_responsible_staff_position',
        'internet_access' => 'field_distribution_point_interne',
        'secure_area' => 'field_has_safe_storage',
        'storage_space' => 'field_storage_sapce',
        'can_pickup' => 'field_trusted_pickup',
        'max_income_requirement' => 'field_has_max_income_requirement',
        'max_income_requirement_description' => 'field_income_requirement_desc',
        'serve_over_2x_FPL' => 'field_greater_2_fpl',
        'is_income_verified' => 'field_has_income_verify',
        'income_verify_docs' => 'field_income_verify_documentatio',
        'has_internal_db' => 'field_has_internal_db',
        'is_maac' => 'field_is_maac',
        'ethnic_black' => 'field_population_black',
        'ethnic_white' => 'field_population_white',
        'ethnic_hispanic' => 'field_population_hispanic',
        'ethnic_asian' => 'field_population_asian',
        'ethnic_american_indian' => 'field_population_american_indian',
        'ethnic_island' => 'field_population_island',
        'ethnic_multi' => 'field_population_multi_racial',
        'ethnic_other' => 'field_population_other',
        'zipcodes' => 'field_zips_served',
        'fpl_below' => 'field_fpl',
        'fpl_1_2' => 'field_1_2_fpl',
        'fpl_gt_2x' => 'field_serve_200_fpl',
        'fpl_unknown' => 'field_poverty_unknown',
        'ages_served' => 'field_ages_served',
        'other_served' => 'field_other_served',
        'distribute_times' => 'field_distribution_times',
        'new_client_times' => 'field_new_client_times',
        'extra_client_documentation' => 'field_more_docs_required',
        'funding_sources' => 'field_sources_of_funding',
        'is_harvesters' => 'field_active_harvesters',
        'is_united_way' => 'field_active_united_way',
        'current_diaper_source' => 'field_current_diapers',
        'has_diaper_budget' => 'field_diaper_budget',
        'diaper_funding_source' => 'field_current_diapers_other',
        'pickup_method' => 'field_pick_up_method ',

    ];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Registry
     */
    private $reg;

    private $oldZips = [];

    public function __construct(EntityManagerInterface $em, Registry $reg)
    {
        ini_set('memory_limit', '-1');
        $this->em = $em;
        $this->reg = $reg;
        $this->oldZips = [];
        $zipsFile = fopen(__DIR__ . "/../Data/Install/portal_zip_county.csv", 'r');

        while ($line = fgetcsv($zipsFile)) {
            $this->oldZips[$line[0]] = [
                'zip' => $line[1],
                'county' => $line[2],
                'state' => $line[3],
            ];
        }

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
        $io = new SymfonyStyle($input, $output);

        $this->loadFulfillmentPeriods();
        $this->loadDistributionMethods();

        $rawContent = file_get_contents($input->getArgument('filepath'));
        $partnersIn = json_decode($rawContent);

        $progress = $io->createProgressBar(count($partnersIn));
        $progress->setFormat('debug');

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

            $this->processContacts($p, $partner);

            $profile = new PartnerProfile();
            $profile->setPartner($partner);

            $this->processProfile($p, $profile);

            $this->em->persist($partner);
            $this->em->flush();

            $progress->advance();

        }

        $progress->finish();
        $io->newLine();

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

    /**
     * @param $p
     * @param Partner $partner
     */
    private function processContacts($p, Partner $partner): void
    {
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

        if (property_exists($p, 'field_program_aternate_contact') && $p->field_program_aternate_contact) {
            $pickupContact = new PartnerContact();
            $pickupContact->setName(Name::fromString($p->field_program_alternate_contact->und[0]->value));
            $pickupContact->setTitle('Program Alternate Contact');
            $pickupContact->setPhoneNumber($p->field_alternate_contact_phone->und[0]->value);
            if ($p->field_pick_up_email) {
                $pickupContact->setEmail($p->field_alternate_contact_email->und[0]->email);
            }

            $partner->addContact($pickupContact);
        }
    }

    private function processProfile($p, PartnerProfile $profile)
    {
        foreach ($this->fieldMap as $new => $old) {
            if (!property_exists($p, $old)) {
                continue;
            }

            $definition = $this->em->getRepository(AttributeDefinition::class)->findOneBy(['name' => $new]);
            $attribute = new Attribute($definition);
            $attribute->setValues($this->getDrupalValues($p->$old));
            $profile->addAttribute($attribute);
        }
    }

    /**
     * @param mixed $field
     * @return mixed
     * @throws \Exception
     */
    private function getDrupalValues($field): array
    {
        if (empty($field)) {
            return [];
        } elseif (is_scalar($field)) {
            return [$field];
        } elseif (property_exists($field, 'und')) {
            if (property_exists($field->und[0], 'value')) {
                return array_map(function ($value) {
                    $value = $value->value;

                    if (is_string($value) && key_exists($value, $this->optionMap)) {
                        $value = $this->optionMap[$value];
                    }
                    return $value;
                }, $field->und);
            } elseif (property_exists($field->und[0], 'url')) {
                return array_map(function ($value) {
                    return $value->url;
                }, $field->und);
            } elseif (property_exists($field->und[0], 'filename')) {
                $values = array_map(function ($value) {
                    $file = new EavFile();
                    $file->setFilename($value->filename);
                    $file->setMimeType($value->filemime);
                    $file->setFilesize($value->filesize);
                    if (!property_exists($value, 'node_export_file_data')) {
                        return $file;
                    }
                    $file->setContent(base64_decode($value->node_export_file_data));

                    return $file;
                }, $field->und);

                return array_filter($values, function (EavFile $file) {
                    return !$file->isEmpty();
                });
            } elseif (property_exists($field->und[0], 'thoroughfare')) {
                return array_map(function ($value) {
                    $address = new EavAddress();
                    $address->setStreet1($value->thoroughfare);
                    $address->setStreet2($value->premise);
                    $address->setCity($value->locality);
                    $address->setState($value->administrative_area);
                    $address->setPostalCode($value->postal_code);
                    $address->setCountry("United States of America");

                    return $address;
                }, $field->und);
            } elseif (property_exists($field->und[0], 'target_id')) {
                return array_map(function ($value) {
                    $oldZip = $this->oldZips[$value->target_id];
                    $zips = $this->em
                        ->getRepository(ZipCounty::class)
                        ->findByZipAndCounty($oldZip['zip'], $oldZip['county']);

                    if (!$zips) {
                        $zips = $this->em
                            ->getRepository(ZipCounty::class)
                            ->findByZipAndCounty($oldZip['zip'], null);
                    }
                    return reset($zips) ?: null;
                }, $field->und);
            } else {
                throw new \Exception(sprintf('Unable to determine Drupal field value: %s', var_export($field)));
            }
        } else {
            throw new \Exception(sprintf('Unable to determine Drupal field value: %s', var_export($field)));
        }
    }
}
