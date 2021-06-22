<?php

namespace App\Command;

use App\Entity\EAV\AttributeDefinition;
use App\Entity\EAV\AttributeOption;
use App\Entity\EAV\AttributeValue;
use App\Entity\EAV\PartnerProfileAttributeDefinition;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportDrupalPartnerFieldConfig extends Command
{
    protected static $defaultName = 'app:migrate:partner-field-config';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(
                'Creates the Partner custom fields that match the Drupal Portal'
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
        foreach ($this->getData() as $key => $field) {
            $definition = new PartnerProfileAttributeDefinition();
            $definition->setName($field['name']);
            $definition->setLabel($field['label']);
            $definition->setDescription($field['description']);
            $definition->setRequired($field['required']);
            $definition->setType($field['type']);
            $definition->setDisplayInterface($field['interface']);
            $definition->setOrderIndex($key);
            if (isset($field['options'])) {
                foreach ($field['options'] as $value => $name) {
                    $option = new AttributeOption();
                    $option->setName($name);
                    $option->setValue($value);
                    $definition->addOption($option);
                }
            }

            $this->em->persist($definition);
        }

        $this->em->flush();

        return 0;
    }

    public function getData(): array
    {
        return [
            [
                'name' => 'shortname',
                'label' => 'Short Name',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_STRING,
                'interface' => AttributeValue::UI_TEXT,
            ],
            [
                'name' => 'designation',
                'label' => 'Your Agency is a',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_OPTION_LIST,
                'interface' => AttributeValue::UI_CHECKBOX_GROUP,
                'options' => [
                    '501c3' => '501(c)3',
                    'religious' => 'Religious Organization',
                    'government' =>  'Government Organization',
                ],
            ],[
                'name' => 'designation_upload',
                'label' => 'Proof of agency status',
                'description' => 'Please attach one of the following:

    * 501(c)3 Letter
    * Letter of Good Standing from Denominational Headquarters
    * Government Letterhead',
                'required' => true,
                'type' => AttributeDefinition::TYPE_FILE,
                'interface' => AttributeValue::UI_FILE_UPLOAD,
            ],[
                'name' => 'mission',
                'label' => 'Describe agency mission/service provided to the community',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_TEXT,
                'interface' => AttributeValue::UI_TEXTAREA,
            ],[ //TODO: TYPE_URL and UI_URL
                'name' => 'website',
                'label' => 'Website',
                'description' => '',
                'required' => false,
                'type' => AttributeDefinition::TYPE_URL,
                'interface' => AttributeValue::UI_URL,
            ],[ //TODO: TYPE_URL and UI_URL
                'name' => 'facebook',
                'label' => 'Facebook Page',
                'description' => 'ex: https://www.facebook.com/happybottoms/',
                'required' => false,
                'type' => AttributeDefinition::TYPE_URL,
                'interface' => AttributeValue::UI_URL,
            ],[
                'name' => 'twitter',
                'label' => 'Twitter Account',
                'description' => 'ex. @happybottomsorg',
                'required' => false,
                'type' => AttributeDefinition::TYPE_STRING,
                'interface' => AttributeValue::UI_TEXT,
            ],[
                'name' => 'founded_year',
                'label' => 'Year Agency Founded',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_INTEGER,
                'interface' => AttributeValue::UI_NUMBER,
            ],[
                'name' => 'is_990',
                'label' => 'Do you file an IRS Form 990? ',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => '990_file',
                'label' => 'Attach most recent 990 copy',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_FILE,
                'interface' => AttributeValue::UI_FILE_UPLOAD,
            ],[
                'name' => 'program_name',
                'label' => 'Program Name',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_STRING,
                'interface' => AttributeValue::UI_TEXT,
            ],[
                'name' => 'program_description',
                'label' => 'Program Description',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_TEXT,
                'interface' => AttributeValue::UI_TEXTAREA,
            ],[
                'name' => 'program_length',
                'label' => 'How long has this program been in operation?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_STRING,
                'interface' => AttributeValue::UI_TEXT,
            ],[
                'name' => 'program_has_case_management',
                'label' => 'Does the program include a case management component?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'program_is_evidence_based',
                'label' => 'Is the program service model evidence-based?',
                'description' => '',
                'required' => false,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'program_evidence_based_description',
                'label' => 'Describe how the program service model is evidence-based',
                'description' => '',
                'required' => false,
                'type' => AttributeDefinition::TYPE_TEXT,
                'interface' => AttributeValue::UI_TEXTAREA,
            ],[
                'name' => 'program_improve_client',
                'label' => 'How does this program work to improve the circumstances of the clients you serve?',
                'description' => '',
                'required' => false,
                'type' => AttributeDefinition::TYPE_TEXT,
                'interface' => AttributeValue::UI_TEXTAREA,
            ],[
                'name' => 'program_diaper_use',
                'label' => 'How will the diapers be used by this program?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_OPTION_LIST,
                'interface' => AttributeValue::UI_CHECKBOX_GROUP,
                'options' => [
                    'emergency' => 'Emergency supplies for families (off site)',
                    'homeless' => 'Homeless shelter',
                    'domestic' => 'Domestic violence shelter',
                    'residential' => 'On-site residential program',
                    'outreach' => 'Outreach',
                    'recovery' => 'Alcohol/Drug Recovery',
                    'daycare' => 'Daycare',
                    'foster' => 'Foster Care',
                    'other' => 'Other',
                ],
            ],[
                'name' => 'program_diaper_use_other',
                'label' => 'Other, Please Specify',
                'description' => '',
                'required' => false,
                'type' => AttributeDefinition::TYPE_TEXT,
                'interface' => AttributeValue::UI_TEXT,
            ],[
                'name' => 'program_already_distribute',
                'label' => 'Do you currently distribute diapers or provide 
                    diapers as part of your regular programming?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'program_turn_away',
                'label' => 'If applying for diapers to be used in a child care program, 
                    do you turn away clients if they cannot provide their own diapers?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'program_address',
                'label' => 'Program Address',
                'description' => 'If different from mailing address',
                'required' => false,
                'type' => AttributeDefinition::TYPE_ADDRESS,
                'interface' => AttributeValue::UI_ADDRESS,
            ],[
                'name' => 'max_serve',
                'label' => 'What is the maximum number of diaper clients your agency could serve per month?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_INTEGER,
                'interface' => AttributeValue::UI_NUMBER,
            ],[
                'name' => 'incorporate_services',
                'label' => 'How do you plan to incorporate diaper distribution in to the other 
                    services you provide with this program?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_TEXT,
                'interface' => AttributeValue::UI_TEXTAREA,
            ],[
                'name' => 'has_designated_staff',
                'label' => 'Will you have a staff member designated to handle dat-to-day 
                    responsibilities of the HappyBottoms program?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'has_designated_staff_position',
                'label' => 'If yes, which staff position will handle HappyBottoms\' responsibilities?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_TEXT,
                'interface' => AttributeValue::UI_TEXT,
            ],[
                'name' => 'internet_access',
                'label' => 'Does the location where you would distribute diapers have internet access?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'secure_area',
                'label' => 'Do you have a secure, locked area of adequate size to store HappyBottoms diapers?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'storage_space',
                'label' => 'Please describe the storage space',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_TEXT,
                'interface' => AttributeValue::UI_TEXTAREA,
            ],[
                'name' => 'can_pickup',
                'label' => 'Will a trustworthy person (staff or volunteer) be available to pick up
                    your agency\'s diapers from our Waldo warehouse?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'max_income_requirement',
                'label' => 'Does your agency/program have a maximum income requirement to receive your services?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'max_income_requirement_description',
                'label' => 'If yes, what are your guidelines?',
                'description' => '',
                'required' => false,
                'type' => AttributeDefinition::TYPE_TEXT,
                'interface' => AttributeValue::UI_TEXT,
            ],[
                'name' => 'serve_over_2x_FPL',
                'label' => 'Do you serve clients with incomes over 200% of FPL?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'is_income_verified',
                'label' => 'Do you verify income?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'income_verify_docs',
                'label' => 'If yes, what documentation do you require?',
                'description' => '',
                'required' => false,
                'type' => AttributeDefinition::TYPE_TEXT,
                'interface' => AttributeValue::UI_TEXT,
            ],[
                'name' => 'has_internal_db',
                'label' => 'Do you have an internal database to track client demographics, including household income?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'is_maac',
                'label' => 'Are you a MAAC agency or on the MAAC Linc system?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'ethnic_black',
                'label' => 'Black/African American',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_INTEGER,
                'interface' => AttributeValue::UI_NUMBER,
            ],[
                'name' => 'ethnic_white',
                'label' => 'White/Caucasian',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_INTEGER,
                'interface' => AttributeValue::UI_NUMBER,
            ],[
                'name' => 'ethnic_hispanic',
                'label' => 'Hispanic/Latino',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_INTEGER,
                'interface' => AttributeValue::UI_NUMBER,
            ],[
                'name' => 'ethnic_asian',
                'label' => 'Asian',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_INTEGER,
                'interface' => AttributeValue::UI_NUMBER,
            ],[
                'name' => 'ethnic_american_indian',
                'label' => 'American Indian',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_INTEGER,
                'interface' => AttributeValue::UI_NUMBER,
            ],[
                'name' => 'ethnic_island',
                'label' => 'Native Pacific/Other Native Island',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_INTEGER,
                'interface' => AttributeValue::UI_NUMBER,
            ],[
                'name' => 'ethnic_multi',
                'label' => 'Multi-racial',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_INTEGER,
                'interface' => AttributeValue::UI_NUMBER,
            ],[
                'name' => 'ethnic_other',
                'label' => 'Other',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_INTEGER,
                'interface' => AttributeValue::UI_NUMBER,
            ],[
                'name' => 'zipcodes',
                'label' => 'Zipcodes/Counties Served',
                'description' => '',
                'required' => false,
                'type' => AttributeDefinition::TYPE_ZIPCODE,
                'interface' => AttributeValue::UI_ZIPCODE,
            ],[
                'name' => 'fpl_below',
                'label' => 'Federal Poverty Level or below',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_INTEGER,
                'interface' => AttributeValue::UI_NUMBER,
            ],[
                'name' => 'fpl_1_2',
                'label' => '1-2 times above FPL',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_INTEGER,
                'interface' => AttributeValue::UI_NUMBER,
            ],[
                'name' => 'fpl_gt_2x',
                'label' => 'Greater than 2 times FPL',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_INTEGER,
                'interface' => AttributeValue::UI_NUMBER,
            ],[
                'name' => 'fpl_unknown',
                'label' => 'Unknown',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_INTEGER,
                'interface' => AttributeValue::UI_NUMBER,
            ],[
                'name' => 'ages_served',
                'label' => 'Ages',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_STRING,
                'interface' => AttributeValue::UI_TEXT,
            ],[
                'name' => 'other_served',
                'label' => 'Other',
                'description' => '',
                'required' => false,
                'type' => AttributeDefinition::TYPE_STRING,
                'interface' => AttributeValue::UI_TEXT,
            ],[
                'name' => 'pickup_method',
                'label' => 'My agency will use the following to pick up our monthly diaper order',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_OPTION_LIST,
                'interface' => AttributeValue::UI_RADIO,
                'options' => [
                    'volunteers' => 'Volunteers',
                    'staff' => 'Program Staff',
                    'courier' => 'Courier Service',
                    'happybottoms' => 'HappyBottoms delivery service',
                ],
            ],[
                'name' => 'distribute_times',
                'label' => 'Please list the days and times you plan to distribute diapers',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_TEXT,
                'interface' => AttributeValue::UI_TEXTAREA,
            ],[
                'name' => 'new_client_times',
                'label' => 'Please list the days and times your agency plans to accept new clients',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_TEXT,
                'interface' => AttributeValue::UI_TEXTAREA,
            ],[
                'name' => 'extra_client_documentation',
                'label' => 'Will your agency require any extra documentation for new clients 
                    (beyond HappyBottoms\' requirements)?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_TEXT,
                'interface' => AttributeValue::UI_TEXTAREA,
            ],[
                'name' => 'funding_sources',
                'label' => 'What sources of funding does your agency receive? (Check all that apply)',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_OPTION_LIST,
                'interface' => AttributeValue::UI_CHECKBOX_GROUP,
                'options' => [
                    'foundations' => 'Grants – Foundations',
                    'state' => 'Grants – State',
                    'federal' => 'Grants – Federal',
                    'corporate' => 'Corporate donations',
                    'individual' => 'Individual donations',
                    'other' => 'Other',
                ]
            ],[
                'name' => 'is_harvesters',
                'label' => 'Active Harvesters Agency?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'is_united_way',
                'label' => 'Active United Way Agency?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'current_diaper_source',
                'label' => 'If you currently distribute diapers, what are your sources for diapers? 
                    (check all that apply)',
                'description' => '',
                'required' => false,
                'type' => AttributeDefinition::TYPE_OPTION_LIST,
                'interface' => AttributeValue::UI_CHECKBOX_GROUP,
                'options' => [
                    'retail' => 'Purchase retail',
                    'wholesale' => 'Purchase wholesale',
                    'harvesters' => 'Harvesters',
                    'drives_self' => 'Diaper drives',
                    'drives_others' => 'Diaper drives conducted by others',
                    'other' => 'Other ',
                ]
            ],[
                'name' => 'has_diaper_budget',
                'label' => 'If you currently purchase diapers, do you have a specific line item in your 
                    budget for diapers?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'diaper_funding_source',
                'label' => 'Do you have a specific funding source designated for diapers?',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_BOOLEAN,
                'interface' => AttributeValue::UI_YES_NO_RADIO,
            ],[
                'name' => 'drupal_id',
                'label' => 'Legacy ID',
                'description' => '',
                'required' => false,
                'type' => AttributeDefinition::TYPE_INTEGER,
                'interface' => AttributeValue::UI_NUMBER,
            ],
        ];
    }
}
