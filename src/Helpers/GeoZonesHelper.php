<?php

namespace SilverCommerce\GeoZones\Helpers;

use LogicException;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;

/**
 * Some simple helper methods to interact with country and regions
 */
class GeoZonesHelper
{
    use Configurable, Injectable;

    /**
     * loaded via yml config
     * 
     * @var array
     */
    private static $iso_3166_regions = [];

    /**
     * List of countries that this helper will filter by
     *
     * @var array
     */
    private $countries_list = [];

    /**
     * List of ISO 3166 3 character region codes to limit
     * this list to.
     *
     * @var array
     */
    private $limit_region_codes = [];

    /**
     * Cache of regions from previous calls (to save some memory)
     *
     * @var array
     */
    private $region_cache = [];

    /**
     * Instantiate this object and setup countries (if provided)
     *
     * @param array $countries
     */
    public function __construct(array $countries = [])
    {
        $this->setCountriesList($countries);
    }

    /**
     * Get a list of region codes, possibly filtered by list of
     * country codes.
     *
     * Each region returned is of the format:
     *
     *  - name - region name
     *  - type - region type
     *  - code - full region code (2 character country code and 3
     *           character region code)
     *  - region_code - 3 character region code
     *  - country_code - 2 character region code
     *
     * NOTE: As we are dealing with over 4,000 regions, we cache the
     * results to improve performace. If you need this helper to regenerate
     * the regions, make sure you call @link clearRegionCache method
     * first
     * 
     * @return array
     */
    public function getRegionArray()
    {
        if (count($this->region_cache) > 0) {
            return $this->region_cache;
        }

        $countries = $this->getCountriesList();
        $limit_regions = $this->getLimitRegionCodes();
        $regions = $this->config()->iso_3166_regions;
        $results = [];

        // Filter generate a new list of regions with some more useful
        // data and filter by country if relevent
        foreach ($regions as $item) {
            if (array_key_exists("code", $item) && array_key_exists("name", $item)) {
                $codes = explode("-", $item["code"]);
                $region_code = $codes[1];
                $country_code = $codes[0];
                $type = array_key_exists("type", $item) ? $item["type"] : "";

                if (count($countries) > 0 && !in_array($country_code, $countries)) {
                    continue;
                }

                if (count($limit_regions) > 0 && !in_array($region_code, $limit_regions)) {
                    continue;
                }

                $results[] = [
                    "name" => $item["name"],
                    "type" => $type,
                    "code" => $item["code"],
                    "region_code" => $region_code,
                    "country_code" => $country_code
                ];
            }
        }

        $this->region_cache = $results;

        return $results;
    }

    /**
     * Return a list of objects representing relevent regions
     * This list can be filtered by a list of country codes to
     * output only relevent regions.
     *
     * @return \SilverStripe\ORM\ArrayList
     */
    public function getRegionsAsObjects()
    {
        $regions = $this->getRegionArray();
        $results = ArrayList::create();

        foreach ($regions as $item) {
            $results->add(ArrayData::create([
                "Name" => $item["name"],
                "Type" => $item["type"],
                "Code" => $item["code"],
                "RegionCode" => $item['region_code'],
                "CountryCode" => $item['country_code']
            ]));
        }

        return $results;
    }

    /**
     * Generate an array of country codes and names that can be used in
     * country dropdowns, or for comparison.
     *
     * @param bool $codes_only Rturn only an array of 2 character codes (no names)
     *
     * @return array
     */
    public function getISOCountries(bool $codes_only = false)
    {
        $countries = array_change_key_case(
            i18n::getData()->getCountries(),
            CASE_UPPER
        );

        if ($codes_only === true) {
            return array_keys($countries);
        }

        return $countries;
    }

    /**
     * Check if the provided country code is valid
     *
     * @return bool
     */
    protected function validCountryCode(string $code)
    {
        $countries = array_keys(array_change_key_case(
            i18n::getData()->getCountries(),
            CASE_UPPER
        ));

        if (strlen($code) !== 2) {
            return false;
        }

        if (!in_array($code, $countries)) {
            return false;
        }

        return true;
    }

    /**
     * Get list of countries that this helper will filter by
     *
     * @return array
     */ 
    public function getCountriesList()
    {
        return $this->countries_list;
    }

    /**
     * Add a country code to the of countries
     *
     * @param array $code Single country code
     *
     * @throws LogicException
     * 
     * @return self
     */ 
    public function addCountryToList(string $code)
    {
        if (!$this->validCountryCode($code)) {
            throw new LogicException("You must use ISO 3166 2 character country codes");
        }
        $this->countries_list[] = $code;
        return $this;
    }

    /**
     * remove a country code to from the country list
     *
     * @param array $code Single country code
     *
     * @throws LogicException
     * 
     * @return self
     */ 
    public function removeCountryFromList(string $code)
    {
        if (!$this->validCountryCode($code)) {
            throw new LogicException("You must use ISO 3166 2 character country codes");
        }

        $list = $this->countries_list;

        if(($key = array_search($code, $list)) !== false) {
            unset($list[$key]);
        }

        $this->setCountriesList($list);

        return $this;
    }

    /**
     * Set list of countries (and also perform some basic validation)
     *
     * @param array $countries_list  List of countrie that this helper will filter by
     *
     * @throws LogicException
     *
     * @return self
     */ 
    public function setCountriesList(array $countries)
    {
        $this->countries_list = [];

        foreach ($countries as $code) {
            if (!$this->validCountryCode($code)) {
                throw new LogicException("You must use ISO 3166 2 character country codes");
            }
            $this->countries_list[] = $code;
        }

        return $this;
    }

    /**
     * Get list of regions that the final list needs to be filtered by
     *
     * @return array
     */
    public function getLimitRegionCodes()
    {
        return $this->limit_region_codes;
    }

    /**
     * Add a region code to the list of regions to limit the final list by
     *
     * @param array $code Single region code
     *
     * @throws LogicException
     *
     * @return self
     */
    public function addLimitRegionCodeToList(string $code)
    {
        if (!$this->validCountryCode($code)) {
            throw new LogicException("You must use ISO 3166 3 character region codes");
        }
        $this->limit_region_codes[] = $code;
        return $this;
    }

    /**
     * Remove a region code to from the region code limit list
     *
     * @param array $code Single region code
     *
     * @throws LogicException
     * 
     * @return self
     */ 
    public function removeLimitRegionCodeFromList(string $code)
    {
        if (!$this->validCountryCode($code)) {
            throw new LogicException("You must use ISO 3166 3 character region codes");
        }

        $list = $this->limit_region_codes;

        if(($key = array_search($code, $list)) !== false) {
            unset($list[$key]);
        }

        $this->setLimitRegionCodes($list);

        return $this;
    }

    /**
     * Set list of regions (and also perform some basic validation)
     *
     * @param array $regions List of countries that this helper will filter by
     *
     * @throws LogicException
     *
     * @return self
     */ 
    public function setLimitRegionCodes(array $regions)
    {
        $this->limit_region_codes = [];

        foreach ($regions as $region) {
            if (!$this->validCountryCode($region)) {
                throw new LogicException("You must use ISO 3166 3 character region codes");
            }
            $this->limit_region_codes[] = $region;
        }

        return $this;
    }

    /**
     * Clear any existing region cache (if the region list needs re-building)
     *
     * @return self
     */
    public function clearRegionCache()
    {
        $this->region_cache = [];
        return $this;
    }
}
