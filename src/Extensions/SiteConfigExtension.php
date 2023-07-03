<?php

namespace SilverCommerce\GeoZones\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverCommerce\GeoZones\Model\Zone;
use SilverCommerce\GeoZones\Model\Region;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;

/**
 * Add postage areas to config
 */
class SiteConfigExtension extends DataExtension
{
    private static $has_many = [
        'GeoZones' => Zone::class
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab(
            "Root.GeoZones",
            [
                // Generate a list of zones for the current site
                GridField::create(
                    'GeoZones',
                    _t("SilverCommerce\GeoZones.Zones", "Zones"),
                    $this->owner->GeoZones()
                )->setConfig(GridFieldConfig_RelationEditor::create()),

                // Show all current regions in the system
                GridField::create(
                    'GeoZoneRegions',
                    _t("SilverCommerce\GeoZones.Regions", "Regions"),
                    Region::get()
                )->setConfig(GridFieldConfig_RecordEditor::create())
            ]
        );
    }
}
