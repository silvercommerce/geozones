<?php

namespace SilverCommerce\GeoZones\Model;

use Locale;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\DropdownField;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * A container of multiple regions 
 * 
 */
class Zone extends DataObject
{
    private static $table_name = 'GeoZoneZone';

    private static $db = [
        "Name" => "Varchar",
        "Country" => "Varchar(2)",
        "AllRegions" => "Boolean",
        "Enabled" => "Boolean"
    ];

    private static $has_one = [
        "Site" => SiteConfig::class
    ];

    private static $many_many = [
        "Regions" => Region::class
    ];

    private static $summary_fields = [
        "Name",
        "Country",
        "Regions.Count",
        "Enabled"
    ];

    public function populateDefaults()
    {
        parent::populateDefaults();

        $current_region = Locale::getRegion(i18n::get_locale());
        $this->Country = i18n::get_locale($current_region);
    }

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $fields->replaceField(
                "Country",
                DropdownField::create(
                    'Country',
                    $this->fieldLabel("Country"),
                    array_change_key_case(
                        i18n::getData()->getCountries(),
                        CASE_UPPER
                    )
                )->setEmptyString("")
            );
        });

        return parent::getCMSFields();
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        // If this applies tyo all regions in the country,
        // then add them all on save
        if ($this->AllRegions && isset($this->Country)) {
            $regions = Region::get()
                ->filter("CountryCode", $this->Country);
            
            foreach ($regions as $region) {
                $this
                    ->Regions()
                    ->add($region);
            }
        }
    }
}