<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Logger;

/**
 * DataLogger Interface
 */
interface DataLoggerInterface
{
    /**
     * @param string $type
     * @param $data
     * @return void
     */
    public function add($type, $data);
}
