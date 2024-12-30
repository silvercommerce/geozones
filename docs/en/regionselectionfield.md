# Region Selection Field

This module provides a `RegionSelectionField` which is a simple ajax powered form
field that can be used to allow a user to select a Region/SubDivision by the value
selected on a predefined `Country` field.

Before adding your `RegionSelectionField` you must add a Country selection field
(usually a dropdown, but most field types should work).

You can then add a `RegionSelectionField` to your code and target the Country field
on construction.

**NOTE** You must add a field to the same form that is responsible for setting a valid
country code (ISO 3166 2 character) for `RegionSelectionField` to work.

An example of this would be as follows:

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