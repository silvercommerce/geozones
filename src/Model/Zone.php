<?php

namespace SilverCommerce\GeoZones\Model;

use Locale;
use SilverCommerce\GeoZones\Tasks\ZoneMigrationTask;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\ListboxField;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * A container of multiple regions
 * @property string Name
 * @property string Country
 * @property bool AllRegions
 * @property bool Enabled
 * @method SiteConfig Site()
 */
class Zone extends DataObject
{
    private static $table_name = 'GeoZoneZone';

    private static $db = [
        "Name" => "Varchar",
        "Country" => "Varchar",
        "AllRegions" => "Boolean",
        "Enabled" => "Boolean"
    ];

    private static $has_one = [
        "Site" => SiteConfig::class
    ];

    private static $many_many = [
        "Regions" => Region::class
    ];

    private static $casting = [
        "CountriesList" => "Varchar"
    ];

    private static $summary_fields = [
        "Name",
        "CountriesList",
        "Regions.Count",
        "Enabled"
    ];

    private static $searchable_fields = [
        "Name",
        "Country",
        "Regions.Name",
        "Regions.Code",
        "Enabled"
    ];

    /**
     * {@inheritdoc}
     */
    public function populateDefaults()
    {
        parent::populateDefaults();

        $current_region = Locale::getRegion(i18n::get_locale());
        $this->Country = i18n::get_locale($current_region);
    }

    /**
     * Return an array of all associated countries
     *
     * @return array
     */
    public function getCountriesArray()
    {
        $return = json_decode($this->Country);

        if (empty($return) && isset($this->Country)) {
            $return = [$this->Country];
        }

        return $return;
    }

    /**
     * Return a simple, comma seperated list of associated countries
     *
     * @return string
     */
    public function getCountriesList()
    {
        return implode(",", $this->getCountriesArray());
    }

    /**
     * {@inheritdoc}
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $fields->replaceField(
                "Country",
                ListboxField::create(
                    'Country',
                    $this->fieldLabel("Country"),
                    array_change_key_case(
                        i18n::getData()->getCountries(),
                        CASE_UPPER
                    )
                )
            );
        });

        return parent::getCMSFields();
    }

    /**
     * {@inheritdoc}
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        if (ZoneMigrationTask::config()->run_during_dev_build) {
            $task = new ZoneMigrationTask();
            $task->up();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();

        // If this applies to all regions in the country,
        // then add them all on save
        if ($this->AllRegions && isset($this->Country)) {
            foreach ($this->getCountriesArray() as $country) {
                $regions = Region::get()
                    ->filter("CountryCode", $country);

                foreach ($regions as $region) {
                    $this
                        ->Regions()
                        ->add($region);
                }
            }
        }
    }
}
