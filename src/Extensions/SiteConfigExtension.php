<?php

namespace SilverCommerce\GeoZones\Extensions;

use SilverCommerce\GeoZones\Helpers\GeoZonesHelper;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverCommerce\GeoZones\Model\Zone;
use SilverCommerce\GeoZones\Model\Region;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_Base;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldDataColumns;

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
        $helper = GeoZonesHelper::create();

        $region_config = GridFieldConfig_Base::create();
        $region_config
            ->getComponentByType(GridFieldDataColumns::class)
            ->setDisplayFields([
                "Name" => _t("SilverCommerce\GeoZones.RegionName", "Name"),
                "Type" => _t("SilverCommerce\GeoZones.RegionType", "Type"),
                "RegionCode" =>  _t("SilverCommerce\GeoZones.RegionCode", "Region Code"),
                "CountryCode" =>  _t("SilverCommerce\GeoZones.CountryCode", "Country Code")
            ]);

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
                    _t("SilverCommerce\GeoZones.RegionList", "All regions available (more can be added via YML config)"),
                    $helper->getRegionsAsObjects()
                )->setConfig($region_config)
            ]
        );
    }
}
