<?php

namespace SilverCommerce\GeoZones\Forms;

use Locale;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\DropdownField;
use SilverCommerce\GeoZones\Model\Region;

/**
 * Custom field that makes use of Ajax to change the list of possible regions you can select.
 * 
 * This field needs to be linked with another field on the same form that will provide the
 * selected country code. EG:
 * 
 *  $field = RegionSelectField::create("FieldName", "FieldTitle", "CountryFieldName");
 */
class RegionSelectionField extends DropdownField
{    
    private static $allowed_actions = [
        "regionslist"
    ];

    private static $url_handlers = array(
        '$Action!/$ID' => '$Action'
    );

    /**
     * The name of the associated country field
     * 
     * @var string
     */
    private $country_field;

    protected $create_empty_default = true;

    /**
     * Get the associated country field
     */ 
    public function getCountryField()
    {
        return $this->country_field;
    }

    /**
     * Set the associated country field
     *
     * @return  self
     */ 
    public function setCountryField($country_field)
    {
        $this->country_field = $country_field;

        return $this;
    }

    /**
     * Overwrite default get source to return
     * custom list of regions
     * 
     * @return array|ArrayAccess
     */
    public function getSource()
    {
        $field = $this
            ->getForm()
            ->Fields()
            ->dataFieldByName($this->country_field);

        if (empty($field) || empty($field->Value())) {
            $locale = strtoupper(Locale::getRegion(i18n::get_locale()));
        } else {
            $locale = $field->Value();
        }

        return $this
            ->getList($locale)
            ->map("Code", "Name")
            ->toArray();
    }

    /**
     * Custom constructor to allow us to define the associated country field
     * 
     * @param string $name the name of this field
     * @param string $title the title (label) of this field
     * @param string $country_field The name of the country select field in this form
     * @param string $value pass the value of this field
     */
    public function __construct($name, $title = null, $country_field = "Country", $value = null)
    {
        // Force construction of parent
        parent::__construct($name, $title, [], $value);

        $this->country_field = $country_field;
    }

    /**
     * Render the final field
     */
    public function Field($properties = [])
    {
        Requirements::javascript("silvercommerce/geozones: client/dist/js/RegionSelectionField.min.js");

        $country_field = $this->country_field;
        
        // Get source based on selected country (or current/default locale)
        $field = $this
            ->getForm()
            ->Fields()
            ->dataFieldByName($country_field);
        
        // Add reference to base field
        $this
            ->setAttribute("data-region-field", true)
            ->setAttribute("data-country-field", $field->ID())
            ->setAttribute("data-link", $this->Link("regionslist"));
        
        if ($this->getHasEmptyDefault()) {
            $this->setAttribute("data-empty-string", $this->getEmptyString());
        }

        return parent::Field($properties);
    }

    /**
     * Get a list of regions, filtered by the provided country code
     * 
     * @return SSList
     */
    public function getList($country)
    {
        $list = Region::get()
            ->filter("CountryCode", strtoupper($country));

        if (!$list->exists() && $this->getCreateEmptyDefault()) {
            $countries = i18n::getData()->getCountries();
            if (isset($countries[strtolower($country)])) {
                $name = $countries[strtolower($country)];
            } else {
                $name = $country;
            }
            $list = ArrayList::create();
            $list->push(Region::create([
                "Name" => $name,
                "Type" => "Nation",
                "Code" => strtoupper($country),
                "CountryCode" => strtoupper($country)
            ]));
        }

        return $list;
    }

    /**
     * Return a list of regions based on the supplied country ID 
     * 
     * @return string
     */
    public function regionslist()
    {
        $id = $this->getRequest()->param("ID");
        $data = $this->getList($id)->map("Code", "Name")->toArray();

        return json_encode($data);
    }

    /**
     * Get the value of create_empty_default
     */ 
    public function getCreateEmptyDefault()
    {
        return $this->create_empty_default;
    }

    /**
     * Set the value of create_empty_default
     *
     * @return  self
     */ 
    public function setCreateEmptyDefault($create_empty_default)
    {
        $this->create_empty_default = $create_empty_default;

        return $this;
    }
}