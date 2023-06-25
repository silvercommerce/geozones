<?php

namespace SilverCommerce\GeoZones\Tasks;

use SilverStripe\ORM\DB;
use SilverStripe\Dev\MigrationTask;
use SilverStripe\ORM\DatabaseAdmin;
use SilverStripe\Control\Controller;
use SilverCommerce\GeoZones\Model\Zone;
use SilverStripe\Control\Director;

class ZoneMigrationTask extends MigrationTask
{
    /**
     * Should this task be invoked automatically via dev/build?
     *
     * @config
     *
     * @var bool
     */
    private static $run_during_dev_build = true;

    private static $segment = 'ZoneMigrationTask';

    protected $description = "Upgrade zones to allowing multiple countries";


    /**
     * Run this task
     *
     * @param HTTPRequest $request The current request
     *
     * @return void
     */
    public function run($request)
    {
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
        $zones = Zone::get();

        $this->log('Migrating Zones');
        $i = 0;

        foreach ($zones as $zone) {
            $countries = json_decode($zone->Country);

            if (empty($countries) && isset($zone->Country)) {
                $countries = [$zone->Country];
                $zone->Country = json_encode($countries);
                $zone->write();
                $i++;
            }
        }
        
        $this->log("Migrated {$i} Zones");
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $zones = Zone::get();

        $this->log('Downgrading Zones');
        $i = 0;

        foreach ($zones as $zone) {
            $countries = json_decode($zone->Country);

            if (is_array($countries)) {
                $zone->Country = $countries[0];
                $zone->write();
                $i++;
            }
        }
        
        $this->log("Downgraded {$i} Zones");
    }

    /**
     * @param string $text
     */
    protected function log($text)
    {
        if (Controller::curr() instanceof DatabaseAdmin) {
            DB::alteration_message($text, 'obsolete');
        } elseif (Director::is_cli()) {
            echo $text . "\n";
        } else {
            echo $text . "<br/>";
        }
    }
}
