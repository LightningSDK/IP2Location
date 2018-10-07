<?php

namespace Modules\IP2Location;

use Lightning\Tools\Configuration;
use Lightning\Tools\Messages\SpamFilterInterface;
use Modules\IP2Location\Model\Range;

class SpamFilter implements SpamFilterInterface {

    /**
     * @param array $message
     *
     * @return int
     *   5 if it was found in the blacklist or 0 if not
     */
    public static function getScore(&$message) {
        if (!empty($message['IP'])) {
            if ($location = Range::loadByIP($message['IP'])) {
                $config = Configuration::get('modules.ip2location');

                $message['country_code'] = $location['country_code'];
                $message['country'] = $location['country'];
                $message['region'] = $location['region'];
                $message['city'] = $location['city'];

                // Check white and blacklists.
                foreach (['whitelist', 'blacklist'] as $list) {

                    // If the IP's country is in this list
                    if (in_array($location['country_code'], $config[$list . '_countries'])) {
                        // Add information about what was found.
                        // Then return this lists value.
                        $message['ip2location_filter'] = 'Country found in ' . $list;
                        return $config[$list . '_score'];
                    }
                }
            } else {
                $message['country_code'] = '-';
                $message['country'] = '-';
                $message['region'] = '-';
                $message['city'] = '-';
            }
        }

        $message['ip2location_filter'] = 'Country not found in any list';

        // The IP address was not found, do not rate it as spam.
        return 0;
    }

    public static function flagAsSpam($message) {
        // At this time, this RBL is read only.
    }

}
