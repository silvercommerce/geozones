# Getting Started

All Zone based settings are linked to `SiteConfig`, to configure Zones:

1. Navigate to your SilverStripe admin area.
2. Select the "Settings" menu (from the left hand menu).
3. Select the "Geo Zones" tab (in the top right).
4. Select "Add Zone" or edit an existing Zone.

The bottom of the Geo Zones screen also lists **all** regions found in YML config, if you need to add new regions, see [Custom Regions](./custom_regions.md).

## Zones

A `Zone` is broken down into the current properties:

**Name:** Human readable name for your `Zone`.

**Country:** Relevent country code for this `Zone` (taken from `i18n`).

**Region Codes:** Relevent regions for this `Zone`

**All Regions:** When selected, all regions for this country will be automatically added when this `Zone` is saved.

**Enabled:** The `GeoZonesHelper` will automatically filter out any `Zones` which are not `Enabled`  