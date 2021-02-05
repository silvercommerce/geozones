<?php

namespace SilverCommerce\GeoZones\Tests;

use LogicException;
use SilverCommerce\GeoZones\Helpers\GeoZonesHelper;
use SilverStripe\Dev\SapphireTest;

class GeoZonesHelperTest extends SapphireTest
{
    public function testAddCountryToList()
    {
        $gb_country = "GB";
        $de_country = "DE";
        $us_country = "US";
        $nz_country = "NZ";
        $invalid_country_one = "XY";
        $invalid_country_two = "GBC";

        $helper = GeoZonesHelper::create();
        $this->assertCount(0, $helper->getCountriesList());

        $helper->addCountryToList($gb_country);
        $this->assertCount(1, $helper->getCountriesList());

        $helper->addCountryToList($de_country);
        $this->assertCount(2, $helper->getCountriesList());

        $helper->addCountryToList($us_country);
        $this->assertCount(3, $helper->getCountriesList());

        $helper->addCountryToList($nz_country);
        $this->assertCount(4, $helper->getCountriesList());

        $helper->setCountriesList([]);
        $this->assertCount(0, $helper->getCountriesList());

        $helper->setCountriesList([$gb_country, $us_country, $nz_country]);
        $this->assertCount(3, $helper->getCountriesList());

        $this->expectException(LogicException::class);
        $helper->addCountryToList($invalid_country_one);

        $this->expectException(LogicException::class);
        $helper->addCountryToList($invalid_country_two);

        $this->expectException(LogicException::class);
        $helper->setCountriesList([$gb_country, $invalid_country_one]);

        $this->expectException(LogicException::class);
        $helper->setCountriesList([$gb_country, $invalid_country_two]);
    }

    public function testRemoveCountryFromList()
    {
        $gb_country = "GB";
        $us_country = "US";
        $nz_country = "NZ";
        $invalid_country_one = "XY";
        $invalid_country_two = "GBC";

        $helper = GeoZonesHelper::create();
        $this->assertCount(0, $helper->getCountriesList());
    
        $helper->setCountriesList([$gb_country, $us_country, $nz_country]);
        $this->assertCount(3, $helper->getCountriesList());

        $helper->removeCountryFromList($gb_country);
        $this->assertCount(2, $helper->getCountriesList());

        $this->expectException(LogicException::class);
        $helper->removeCountryFromList($invalid_country_one);

        $this->expectException(LogicException::class);
        $helper->removeCountryFromList($invalid_country_two);
    }

    public function testSetCountriesList()
    {
        $gb_country = "GB";
        $us_country = "US";
        $nz_country = "NZ";
        $invalid_country_one = "XY";

        $helper = GeoZonesHelper::create();
        $this->assertCount(0, $helper->getCountriesList());
    
        $helper->setCountriesList([$gb_country]);
        $this->assertCount(1, $helper->getCountriesList());
        
        $helper->setCountriesList([$gb_country, $nz_country]);
        $this->assertCount(2, $helper->getCountriesList());
    
        $helper->setCountriesList([$gb_country, $us_country, $nz_country]);
        $this->assertCount(3, $helper->getCountriesList());

        $this->expectException(LogicException::class);
        $helper->setCountriesList([$gb_country, $invalid_country_one, $nz_country]);

        $this->expectException(LogicException::class);
        $helper->setCountriesList([$gb_country, $nz_country, $invalid_country_one]);
    }

    public function testGetRegionArray()
    {
        $gb_country = "GB";
        $us_country = "US";
        $nz_country = "NZ";

        $helper = GeoZonesHelper::create();
        $this->assertCount(4837, $helper->getRegionArray());

        $helper->setCountriesList([$gb_country]);
        // Ensure regions cache is working
        $this->assertCount(4837, $helper->getRegionArray());
        $this->assertCount(224, $helper->clearRegionCache()->getRegionArray());
        $this->assertEquals('Armagh, Banbridge and Craigavon', $helper->getRegionArray()[0]['name']);

        $helper->setCountriesList([$us_country]);
        $this->assertCount(57, $helper->clearRegionCache()->getRegionArray());
        $this->assertEquals('Alaska', $helper->getRegionArray()[0]['name']);

        // Test region limit
        $helper->setLimitRegionCodes(['AL', 'AR', 'AS']);
        $this->assertCount(3, $helper->clearRegionCache()->getRegionArray());
        $this->assertEquals('Alabama', $helper->getRegionArray()[0]['name']);

        $helper->setCountriesList([$nz_country]);
        $helper->setLimitRegionCodes([]);
        $this->assertCount(19, $helper->clearRegionCache()->getRegionArray());
        $this->assertEquals('Auckland', $helper->getRegionArray()[0]['name']);

        $helper->setCountriesList([$gb_country, $us_country, $nz_country]);
        $this->assertCount(300, $helper->clearRegionCache()->getRegionArray());
    }

    public function testGetRegionsAsObjects()
    {
        $gb_country = "GB";
        $us_country = "US";
        $nz_country = "NZ";

        $helper = GeoZonesHelper::create();
        $this->assertCount(4837, $helper->getRegionsAsObjects());

        $helper->setCountriesList([$gb_country]);
        $this->assertCount(224, $helper->clearRegionCache()->getRegionsAsObjects());
        $this->assertEquals('Armagh, Banbridge and Craigavon', $helper->getRegionsAsObjects()->first()->Name);

        $helper->setCountriesList([$us_country]);
        // Ensure regions cache is working
        $this->assertEquals('Armagh, Banbridge and Craigavon', $helper->getRegionsAsObjects()->first()->Name);
        $this->assertCount(57, $helper->clearRegionCache()->getRegionsAsObjects());
        $this->assertEquals('Alaska', $helper->getRegionsAsObjects()->first()->Name);

        // Test region limit
        $helper->setLimitRegionCodes(['AL', 'AR', 'AS']);
        $this->assertCount(3, $helper->clearRegionCache()->getRegionsAsObjects());
        $this->assertEquals('Alabama', $helper->getRegionsAsObjects()[0]->Name);

        $helper->setCountriesList([$nz_country]);
        $helper->setLimitRegionCodes([]);
        $this->assertCount(19, $helper->clearRegionCache()->getRegionsAsObjects());
        $this->assertEquals('Auckland', $helper->getRegionsAsObjects()->first()->Name);

        $helper->setCountriesList([$gb_country, $us_country, $nz_country]);
        $this->assertCount(300, $helper->clearRegionCache()->getRegionsAsObjects());
    }
}
