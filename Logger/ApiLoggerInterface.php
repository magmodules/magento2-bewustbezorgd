<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Logger;

/**
 * ApiLogger Interface
 */
interface ApiLoggerInterface
{
    /**
     * @param $message
     * @return void
     */
    public function add($message);
}
