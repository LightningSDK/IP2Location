<?php

namespace Modules\IP2Location\Jobs;

use Lightning\Jobs\Job;
use Lightning\Tools\Communicator\RestClient;
use Lightning\Tools\Configuration;
use Lightning\Tools\CSVIterator;
use Lightning\Tools\Database;
use ZipArchive;

class Update extends Job {
    public function execute($job) {
        $config = Configuration::get('modules.ip2location');
        $db = Database::getInstance();

        foreach ([$config['download_database'], $config['download_database'] . 'IPV6'] as $file) {
            $url = 'http://www.ip2location.com/download/?token=' . $config['download_token'] . '&file=' . $file;
            $tmp_location = HOME_PATH . '/cache/';
            $outputfile = $tmp_location . $file . '.tmp.zip';

            $this->out('Downloading file ' . $file);
            $client = new RestClient($url);
            $client->callGet();
            file_put_contents($outputfile, $client->getRaw());
            $zip = new ZipArchive();
            $zip->open($outputfile);

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex( $i );
                if (preg_match('/\.CSV$/', $stat['name'])) {
                    $this->out('Unzipping file ' . $file);
                    $zip->extractTo($tmp_location, $stat['name']);

                    $csv = new CSVIterator($tmp_location . $stat['name']);

                    $counter = 0;
                    $this->out('Inserting to database from file ' . $file);
                    foreach ($csv as $row) {
                        $db->query('INSERT INTO ip2location SET `start` = ?, `end` = ?, country_code = ?, country = ?, region = ?, city = ?', [
                            $row[0], $row[1], $row[2], $row[3], $row[4], $row[5]
                        ]);
                        $counter++;
                        if ($this->debug && $counter % 10000 == 0) {
                            echo '.';
                        }
                    }
                    if ($this->debug) {
                        echo PHP_EOL;
                    }
                    $this->out('Completed file ' . $file . ' with ' . $counter . 'entries');
                }
            }
        }
    }
}
