<?php

namespace App\DataFixtures;

use App\Entity\EAV\AttributeValue;
use App\Entity\EAV\ClientAttributeDefinition;
use App\Entity\EAV\AttributeDefinition;
use App\Entity\EAV\AttributeOption;
use App\Entity\EAV\PartnerProfileAttributeDefinition;
use App\Entity\PartnerProfile;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Partner;

class ClientAttributeFixtures extends BaseFixture
{
    public function loadData(ObjectManager $manager)
    {
        foreach ($this->getData() as $key => $field) {
            $definition = new ClientAttributeDefinition();
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
                'type' => AttributeDefinition::TYPE_OPTION_LIST,
                'interface' => AttributeValue::UI_CHECKBOX_GROUP,
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
                'type' => AttributeDefinition::TYPE_FILE,
                'interface' => AttributeValue::UI_FILE_UPLOAD,
            ],[
                'name' => 'mission',
                'label' => 'Describe agency mission/service provided to the community',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_TEXT,
                'interface' => AttributeValue::UI_TEXTAREA,
            ],[
                'name' => 'mailing_address',
                'label' => 'Mailing Address',
                'description' => '',
                'required' => true,
                'type' => AttributeDefinition::TYPE_ADDRESS,
                'interface' => AttributeValue::UI_ADDRESS,
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
            ]
        ];
    }
}
