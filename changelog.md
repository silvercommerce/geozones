# Log of changes

This file logs changes to the SilverCommerce Geo Zones Module

## 1.0.0

Initial stable release

## 1.0.1

Add `RegionSelectionField`

## 1.0.2

Allow support for multiple `RegionSelectionField` objects in one form

## 1.1.0

Add ability to assign multiple countries to a `Zone` (ensureing auto
linking of regions is simplified).

## 1.1.1

Enabled RegionSelectionField to always return a region list, even when one shouldn't be present.

## 2.0.0

* Add SS5 support
* Code and performance improvements

## 2.1.0

* Fix errors in 2.0.0 releases
* Add task to migrate and revert Region objects to YML

## 2.1.1

* Fix errors in Zone::getRegionsArray()

## 2.1.2

* Fix errors in Zone::getCountriesArray()