<?php

namespace App\DataFixtures;

use App\Entity\EAV\Attribute;
use App\Entity\EAV\ClientDefinition;
use App\Entity\EAV\Definition;
use App\Entity\EAV\Option;
use App\Entity\EAV\PartnerProfileDefinition;
use App\Entity\PartnerProfile;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Partner;

class ClientAttributeFixtures extends BaseFixture
{
    public function loadData(ObjectManager $manager)
    {
        foreach ($this->getData() as $key => $field) {
            $definition = new ClientDefinition();
            $definition->setName($field['name']);
            $definition->setLabel($field['label']);
            $definition->setDescription($field['description']);
            $definition->setRequired($field['required']);
            $definition->setType($field['type']);
            $definition->setDisplayInterface($field['interface']);
            $definition->setOrderIndex($key);
            if (isset($field['options'])) {
                foreach ($field['options'] as $value => $name) {
                    $option = new Option();
                    $option->setName($name);
                    $option->setValue($value);
                    $definition->addOption($option);
                }
            }

            $manager->persist($definition);
        }

        $manager->flush();
    }

    public function getData(): array
    {
        return [
            // TODO: UI_CHECKBOX_GROUP
            [
                'name' => 'designation',
                'label' => 'Your Agency is a',
                'description' => '',
                'required' => true,
                'type' => Definition::TYPE_OPTION_LIST,
                'interface' => Attribute::UI_SELECT_SINGLE,
                'options' => [
                    '501c3' => '501(c)3',
                    'religious' => 'Religious Organization',
                    'government' =>  'Government Organization',
                ],
            ],[ //TODO: TYPE_FIELD and UI_FILE_UPLOAD
                'name' => 'designation_upload',
                'label' => 'Proof of agency status',
                'description' => 'Please attach one of the following:

    * 501(c)3 Letter
    * Letter of Good Standing from Denominational Headquarters
    * Government Letterhead',
                'required' => true,
                'type' => Definition::TYPE_FILE,
                'interface' => Attribute::UI_FILE_UPLOAD,
            ],[
                'name' => 'mission',
                'label' => 'Describe agency mission/service provided to the community',
                'description' => '',
                'required' => true,
                'type' => Definition::TYPE_TEXT,
                'interface' => Attribute::UI_TEXTAREA,
            ],[ //TODO: TYPE_ADDRESS and UI_ADDRESS
                'name' => 'mailing_address',
                'label' => 'Mailing Address',
                'description' => '',
                'required' => true,
                'type' => Definition::TYPE_ADDRESS,
                'interface' => Attribute::UI_ADDRESS,
            ],[ //TODO: TYPE_URL and UI_URL
                'name' => 'website',
                'label' => 'Website',
                'description' => '',
                'required' => false,
                'type' => Definition::TYPE_URL,
                'interface' => Attribute::UI_URL,
            ],[ //TODO: TYPE_URL and UI_URL
                'name' => 'facebook',
                'label' => 'Facebook Page',
                'description' => 'ex: https://www.facebook.com/happybottoms/',
                'required' => false,
                'type' => Definition::TYPE_URL,
                'interface' => Attribute::UI_URL,
            ],[
                'name' => 'twitter',
                'label' => 'Twitter Account',
                'description' => 'ex. @happybottomsorg',
                'required' => false,
                'type' => Definition::TYPE_STRING,
                'interface' => Attribute::UI_TEXT,
            ],[
                'name' => 'founded_year',
                'label' => 'Year Agency Founded',
                'description' => '',
                'required' => true,
                'type' => Definition::TYPE_INTEGER,
                'interface' => Attribute::UI_NUMBER,
            ]
        ];
    }
}
