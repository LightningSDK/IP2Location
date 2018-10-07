<?php

namespace Modules\IP2Location\Model;

use Lightning\Model\Object;
use Lightning\Tools\Database;

class Range extends Object {
    public static function loadByIP($ip_address) {
        $ip_bin = inet_pton($ip_address);
        $ip_string = unpack('N*', $ip_bin);
        if (!empty($ip_string[1])) {
            $result = Database::getInstance()->query('SELECT * FROM ip2location WHERE `end` >= ? AND `start` <= ? ORDER BY `end` LIMIT 1', [$ip_string[1], $ip_string[1]]);
            return $result->fetch();
        } else {
            return null;
        }
    }
}
