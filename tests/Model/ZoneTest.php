<?php

namespace SilverCommerce\GeoZones\Tests;

use Generator;
use Locale;
use SilverCommerce\GeoZones\Model\Zone;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\i18n\i18n;

class ZoneTest extends SapphireTest
{
    private const LOCALE = "en_GB";

    protected function setUp(): void
    {
        parent::setUp();
        i18n::set_locale(self::LOCALE);
    }

    public function testDefaultsToCurrentLocale()
    {
        
        $zone = Zone::create();
        $this->assertEquals(self::LOCALE, $zone->Country);
    }

    /**
     * @return Generator 
     */
    public function countriesArrayProvider()
    {
        yield 'Single String' => [self::LOCALE, [self::LOCALE]];
        yield 'Multiple Strings' => [json_encode([self::LOCALE, 'en_US']), [self::LOCALE, 'en_US']];
    }

    /**
     * @dataProvider countriesArrayProvider
     */
    public function testGetCountriesArray($string, $expected)
    {
        $zone = Zone::create();
        $zone->Country = $string;
        $actual = $zone->getCountriesArray();
        $this->assertEquals(count($expected), count($actual));
        $this->assertSame($expected, $zone->getCountriesArray());
    }

    /**
     * @dataProvider countriesArrayProvider
     */
    public function testGetCountriesList($string, $expected)
    {
        var_dump(Locale::getRegion('en_GB'));
        $zone = Zone::create();
        $zone->Country = $string;
        $actual = $zone->getCountriesList();
        $this->assertSame(implode(',', $expected), $zone->getCountriesList());
    }

    /**
     * @return Generator 
     */
    public function countriesProvider()
    {
        yield 'UK Regions' => [self::LOCALE, 224];
        yield 'UK & US Regions' => [json_encode([self::LOCALE, 'en_US']), 281];
    }

    /**
     * @dataProvider countriesProvider
     */
    public function testOnAfterWrite($string, $expectedCount)
    {
        $zone = Zone::create();
        $zone->Country = $string;
        $zone->AllRegions = true;
        $zone->write();

        $this->assertEquals($expectedCount, $zone->Regions()->count());
    }
}