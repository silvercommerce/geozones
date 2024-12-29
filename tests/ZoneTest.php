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
        $this->assertIsArray($countries);
        $this->assertCount(1, $countries);
        $this->assertEquals('GB', $countries[0]);

        /** @var Zone */
        $zone = $this->objFromFixture(Zone::class, 'uk_fr');
        $countries = $zone->getCountriesArray();
        $this->assertIsArray($countries);
        $this->assertCount(2, $countries);
        $this->assertEquals('GB', $countries[0]);
        $this->assertEquals('FR', $countries[1]);

        /** @var Zone */
        $zone = $this->objFromFixture(Zone::class, 'eu');
        $countries = $zone->getCountriesArray();
        $this->assertIsArray($countries);
        $this->assertCount(4, $countries);
        $this->assertEquals('FR', $countries[0]);
        $this->assertEquals('IT', $countries[3]);
    }

    public function testGetCountriesList()
    {
        /** @var Zone */
        $zone = $this->objFromFixture(Zone::class, 'uk_fr');
        $countries = $zone->getCountriesList();
        $this->assertEquals('GB,FR', $countries);

        /** @var Zone */
        $zone = $this->objFromFixture(Zone::class, 'eu');
        $countries = $zone->getCountriesList();
        $this->assertEquals('FR,DE,ES,IT', $countries);

        /** @var Zone */
        $zone = $this->objFromFixture(Zone::class, 'us');
        $countries = $zone->getCountriesList();
        $this->assertEquals('US', $countries);
    }

    public function testGetRegionCodesArray()
    {
        /** @var Zone */
        $zone = $this->objFromFixture(Zone::class, 'uk');
        $regions = $zone->getRegionCodesArray();
        $this->assertIsArray($regions);
        $this->assertCount(13, $regions);
        $this->assertEquals('EDH', $regions[0]);
        $this->assertEquals('MAN', $regions[12]);

        /** @var Zone */
        $zone = $this->objFromFixture(Zone::class, 'fr');
        $regions = $zone->getRegionCodesArray();
        $this->assertIsArray($regions);
        $this->assertCount(14, $regions);
        $this->assertEquals('01', $regions[0]);
        $this->assertEquals('LRE', $regions[13]);

        /** @var Zone */
        $zone = $this->objFromFixture(Zone::class, 'eu');
        $regions = $zone->getRegionCodesArray();
        $this->assertIsArray($regions);
        $this->assertCount(340, $regions);
        $this->assertEquals('BB', $regions[0]);
        $this->assertEquals('VV', $regions[339]);
    }

    public function testGetRegionsCount()
    {
        /** @var Zone */
        $zone = $this->objFromFixture(Zone::class, 'uk');
        $regions = $zone->getRegionsCount();
        $this->assertEquals(13, $regions);

        /** @var Zone */
        $zone = $this->objFromFixture(Zone::class, 'fr');
        $regions = $zone->getRegionsCount();
        $this->assertEquals(14, $regions);

        /** @var Zone */
        $zone = $this->objFromFixture(Zone::class, 'uk_fr');
        $regions = $zone->getRegionsCount();
        $this->assertEquals(349, $regions);

        /** @var Zone */
        $zone = $this->objFromFixture(Zone::class, 'eu');
        $regions = $zone->getRegionsCount();
        $this->assertEquals(340, $regions);
    }
}
