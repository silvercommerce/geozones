# SilverCommerce GeoZones

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