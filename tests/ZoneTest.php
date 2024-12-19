<?php

namespace SilverCommerce\GeoZones\Tests;

use LogicException;
use SilverStripe\Dev\SapphireTest;
use SilverCommerce\GeoZones\Model\Zone;
use SilverCommerce\GeoZones\Helpers\GeoZonesHelper;

class ZoneTest extends SapphireTest
{
    protected static $fixture_file = 'GeoZoneData.yml';

    public function testGetCountriesArray()
    {
        /** @var Zone */
        $zone = $this->objFromFixture(Zone::class, 'uk');
        $countries = $zone->getCountriesArray();
        $this->assertIsArray($zone->getCountriesArray());
        $this->assertCount(1, $countries);
    }

    
}
