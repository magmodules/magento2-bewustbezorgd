<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Logger;

use Monolog\Logger;

/**
 * DataLogger
 */
class Data extends Logger
{
    /**
     * @inheritDoc
     */
    public function add($type, $data)
    {
        if (is_array($data) || is_object($data)) {
            $this->info($type . ':' . PHP_EOL . json_encode($data) . PHP_EOL . '--------------------' . PHP_EOL);
        } else {
            $this->info($type . ':' . PHP_EOL . $data . PHP_EOL . '--------------------' . PHP_EOL);
        }
    }
}
