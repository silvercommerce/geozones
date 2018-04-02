# SilverCommerce GeoZones

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/silvercommerce/geozones/badges/quality-score.png?b=1.0)](https://scrutinizer-ci.com/g/silvercommerce/geozones/?branch=1.0)

Adds a list of ISO-3166-2 "Subdivisions" and allows for these to be divided up into "Zones" (which can be used for geographical identicifation)

The initial list is generated based on data provided by the Debian project. more at:

https://salsa.debian.org/iso-codes-team/iso-codes/blob/master/data/iso_3166-2.json

**NOTE** This module just provides data that you can then work with on your SilverStripe
project.

## Instalation

Easiest is to install via composer:

    composer require silvercommerce/geozones

## Setup

All GeoZone settings are linked to `SiteConfig`, to setup new Zones, or add new regions, you can
do this by visit site settings and selecting the "GeoZones" tab.

## Region Selection Field

This module also provides a `RegionSelectionField` which is a simple ajax powered form
field that can be used to filter the regions list by a pre-defined country code.

You can add a `RegionSelectionField` to your code via the following:

```php
use SilverCommerce\GeoZones\Forms\RegionSelectionField;

$form = Form::create(
    $this,
    'PostageForm',
    $fields = FieldList::create(
        DropdownField::create(
            'Country',
            'Country',
            array_change_key_case(
                i18n::getData()->getCountries(),
                CASE_UPPER
            )
        ),
        RegionSelectionField::create(
            "Region",
            "County/State",
            "Country" // name of the field in this form responsible for setting a country code
        )
    ),
    $actions = FieldList::create(
        FormAction::create(
            "doSetPostage",
            _t('SilverCommerce\ShoppingCart.Search', "Search")
        )
    ),
    $required = RequiredFields::create(array(
        "Country",
        "Region"
    ))
);
```

**NOTE** You must add a field to the same form that is responsible for setting a valid
country code (ISO 3166 2 character) for `RegionSelectionField` to work.
