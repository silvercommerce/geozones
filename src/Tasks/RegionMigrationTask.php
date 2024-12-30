<?php

use SilverStripe\ORM\DB;
use Symfony\Component\Yaml\Yaml;
use SilverStripe\Control\Director;
use SilverStripe\Dev\MigrationTask;
use SilverStripe\ORM\DatabaseAdmin;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverCommerce\GeoZones\Model\Zone;
use SilverCommerce\GeoZones\Model\Region;
use SilverStripe\Core\Manifest\ModuleManifest;
use SilverCommerce\GeoZones\Helpers\GeoZonesHelper;
use SilverStripe\i18n\Data\Intl\IntlLocales;

class RegionMigrationTask extends MigrationTask
{	
    private static $run_during_dev_build = true;

    private static $segment = 'RegionMigrationTask';

    protected $description = "Migrate additional regions from the DB to an additional YML config file";

    /**
     * Run this task
     * 
     * @param HTTPRequest $request The current request
     * 
     * @return void
     */
    public function run($request) {
        if ($request->getVar('direction') == 'down') {
            $this->down();
        } else {
            $this->up();
        }
    }

	/**
	 * {@inheritdoc}
	 */
	public function up()
    {
        $project = Config::inst()->get(ModuleManifest::class, 'project');
        $config_path = Controller::join_links(
            BASE_PATH,
            $project,
            "_config",
            "regions.yml"
        );

        $legacy = Region::get();
        $helper = GeoZonesHelper::create();
        $data = [
            IntlLocales::class => [
                'countries' => []
            ],
            GeoZonesHelper::class => [
                'iso_3166_regions' => []
            ]
        ];

        $this->log('Migrating Regions to YML');
        $this->log('(This may take some time)');

        $i = 0;
        $j = 0;
        $k = 0;

		foreach ($legacy as $region) {
            $name = $region->Name;
            $country = $region->CountryCode;
            $code = $region->Code;
            $type = $region->Type;

            // If country is not in system, add to countries
            try {
                $helper->setCountriesList([$region->CountryCode]);
                $helper->setLimitRegionCodes([$region->Code]);
                $region->delete();
                $j++;
            } catch (LogicException $e) {
                // If either country or region throw an exception,
                // add them to the new map
                if (!in_array(strtolower($country), $data[IntlLocales::class]['countries'])) {
                    $data[IntlLocales::class]['countries'][] = strtolower($country);
                    $k++;
                }

                $data[GeoZonesHelper::class]['iso_3166_regions'][] = [
                    'code' => strtoupper($country) . "-" . $code,
                    'name' => $name,
                    'type' => $type
                ];

                $region->delete();
                $i++;
            }
        }

        $yml = <<<YAML
---
Name: customregions
---

YAML;

        $yml .= Yaml::dump($data, 4);

        $this->log("Migrated {$k} Countries");
        $this->log("Migrated {$i} Regions");
        $this->log("Deleted {$j} Regions");

        if ($i > 0) {
            file_put_contents($config_path, $yml);
            $this->log("Created regions.yml");
        }
	}

	/**
	 * {@inheritdoc}
	 */
	public function down() {
        $regions = Config::inst()->get(
            GeoZonesHelper::class,
            'iso_3166_regions'
        );

        $this->log('Downgrading Regions');
        $this->log('(This might take some time)');
        $i = 0;

		foreach ($regions as $region) {
            $code = explode('-', $region['code']);
            
            $new_region = Region::create();
            $new_region->Name = $region['name'];
            $new_region->Type = $region['type'];
            $new_region->CountryCode = $code[0];
            $new_region->Code = $code[1];
            $new_region->write();

            $i++;
        }

        $this->log("Downgraded {$i} Regions");
	}

	/**
	 * @param string $text
	 */
	protected function log($text) {
		if(Controller::curr() instanceof DatabaseAdmin) {
			DB::alteration_message($text, 'obsolete');
		} elseif (Director::is_cli()) {
            echo $text . "\n";
        } else {
            echo $text . "<br/>";
		}
	}
}
