<?php

namespace SilverCommerce\GeoZones\Model;

use Locale;
use SilverCommerce\GeoZones\Helpers\GeoZonesHelper;
use SilverCommerce\GeoZones\Tasks\ZoneMigrationTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\ListboxField;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * A container of multiple regions
 * @property string Name
 * @property string Country
 * @property string RegionsCode
 * @property bool AllRegions
 * @property bool Enabled
 */
class Zone extends DataObject
{
    private static $table_name = 'GeoZoneZone';

    private static $db = [
        "Name" => "Varchar",
        "Country" => "Varchar",
        "RegionCodes" => "Text",
        "AllRegions" => "Boolean",
        "Enabled" => "Boolean"
    ];

    private static $has_one = [
        "Site" => SiteConfig::class
    ];

    private static $many_many = [
        "Regions" => Region::class // Remaining for legacy support
    ];

    private static $casting = [
        "CountriesList" => "Varchar"
    ];

    private static $field_labels = [
        "RegionsCount" => "No. of regions"
    ];

    private static $summary_fields = [
        "Name",
        "CountriesList",
        "RegionsCount",
        "Enabled"
    ];

    private static $searchable_fields = [
        "Name",
        "Country",
        "RegionCodes",
        "Enabled"
    ];

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
     * Get an array of region codes saved against this object
     *
     * @return array
     */
    public function getRegionCodesArray()
    {
        $return = json_decode($this->RegionCodes);

        if (empty($return) && isset($this->RegionCodes)) {
            $return = [$this->RegionCodes];
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
     * Get an array of regions for the current zone, or an empty
     * array if no countries selected
     *
     * @return array
     */
    public function getRegionsArray()
    {
        $region_codes = $this->getRegionCodesArray();
        $helper = GeoZonesHelper::create();

        if (count($region_codes) > 0) {
            $helper->setLimitRegionCodes($region_codes);
        }

        return $helper->getRegionArray();
    }

    /**
     * Get an array of regions for the current country, or an empty
     * array if no countries selected
     *
     * @return array
     */
    public function getRegionsCount()
    {
        return count($this->getRegionsArray());
    }

    /**
     * {@inheritdoc}
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $fields->removeByName("Regions");

            $helper = GeoZonesHelper::create($this->getCountriesArray());

            $fields->replaceField(
                "Country",
                ListboxField::create(
                    'Country',
                    $this->fieldLabel("Country"),
                    $helper->getISOCountries()
                )
            );

            $fields->replaceField(
                "RegionCodes",
                ListboxField::create(
                    'RegionCodes',
                    $this->fieldLabel("RegionCodes"),
                    $helper->getRegionsAsObjects()->map('RegionCode', 'Name')
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
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // If this applies to all regions in the country,
        // then add them all on save
        if ($this->AllRegions && isset($this->Country)) {
            $helper = GeoZonesHelper::create($this->getCountriesArray());
            $codes = [];
            foreach ($helper->getRegionArray() as $region) {
                $codes[] = $region['region_code'];
            }

            if (count($codes) > 0) {
                $this->RegionCodes = json_encode($codes);
            }
        }
    }
}
