# Adding Custom Regions

A large number of regions are added to this module by default, but there are probably omissions (or you may want to define custom regions in your project).

Adding custom regions is managed via YML config, to add more, simply add a `regions.yml`
to your site config:

```YML
---
Name: customregions
---
SilverStripe\i18n\Data\Intl\IntlLocales:
    countries:
        - ss

SilverCommerce\GeoZones\Helpers\GeoZonesHelper:
    iso_3166_regions:
        -
            code: SS-999
            name: 'A Place'
            type: State
        -
            code: SS-878
            name: 'Another Place'
            type: State
```

**NOTE** If you are adding regions for a country not included in SilverStripe, you will also need to add this country to SilverStripe's `IntlLocales` (as shown above).