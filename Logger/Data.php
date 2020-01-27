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
class Data extends Logger implements DataLoggerInterface
{
    /**
     * {@inheritDoc}
     */
    public function add($type, $data)
    {
        if (is_array($data) || is_object($data)) {
            $this->addInfo($type . ':' . PHP_EOL . json_encode($data) . PHP_EOL . '--------------------' . PHP_EOL);
        } else {
            $this->addInfo($type . ':' . PHP_EOL . $data . PHP_EOL . '--------------------' . PHP_EOL);
        }
    }
}
