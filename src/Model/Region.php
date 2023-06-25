<?php

namespace SilverCommerce\GeoZones\Model;

use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Controller;

/**
 * Subdivisions of a country, based on ISO-3166-2 standards.
 *
 * Thanks to debian (https://salsa.debian.org/iso-codes-team/iso-codes/blob/master/data/iso_3166-2.json)
 * for the base data set
 * @property string Name
 * @property string Type
 * @property string Code
 * @property string CountryCode
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
        $existing = self::get();
        $create = $this->config()->create_on_build;

        if (!$existing->exists() && $create) {
            DB::alteration_message(
                "Setting up regions (this could take some time)",
                "created"
            );

            $data_loc = Controller::join_links(
                dirname(dirname(dirname(__FILE__))),
                "data",
                "iso_3166-2.json"
            );

            $data = file_get_contents($data_loc);
            $data_json = json_decode($data, true);
            $data_json = $data_json["3166-2"];
            $i = 0;

            foreach ($data_json as $item) {
                if (array_key_exists("code", $item) && array_key_exists("name", $item)) {
                    $codes = explode("-", $item["code"]);
                    $type = array_key_exists("type", $item) ? $item["type"] : "";

                    $region = Region::create([
                        "Name" => $item["name"],
                        "Type" => $type,
                        "Code" => $codes[1],
                        "CountryCode" => $codes[0],
                    ]);
                    $region->write();
                    $i++;
                }
            }

            DB::alteration_message(
                "Added {$i} regions",
                "created"
            );
        }
    }
}
