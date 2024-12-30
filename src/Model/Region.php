<?php

namespace SilverCommerce\GeoZones\Model;

use RegionMigrationTask;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Controller;

/**
 * Subdivisions of a country, based on ISO-3166-2 standards.
 * 
 * Thanks to debian (https://salsa.debian.org/iso-codes-team/iso-codes/blob/master/data/iso_3166-2.json)
 * for the base data set
 */
class Region extends DataObject
{
    /**
     * Syncronise codes on dev/build
     */
    private static $create_on_build = true;

    private static $table_name = "GeoZoneRegion";

    private static $db = [
        "Name" => "Varchar",
        "Type" => "Varchar",
        "Code" => "Varchar(3)",
        "CountryCode" => "Varchar(2)"
    ];

    private static $belongs_many_many = [
        'Zones' => Zone::class
    ];

    private static $summary_fields = [
        "CountryCode",
        "Name",
        "Type",
        "Code"
    ];

    private static $default_sort = [
        "CountryCode" => "ASC",
        "Name" => "ASC"
    ];

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

		if (RegionMigrationTask::config()->run_during_dev_build) {
			$task = new RegionMigrationTask();
			$task->up();
		}
    }
}