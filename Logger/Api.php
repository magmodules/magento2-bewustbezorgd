<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Logger;

use Monolog\Logger;

/**
 * ApiLogger
 */
class Api extends Logger
{
    /**
     * @inheritDoc
     */
    public function add($message)
    {
        $this->error($message . PHP_EOL . '--------------------' . PHP_EOL);
    }
}
