<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Api\Log;

/**
 * Log Repository Interface
 */
interface RepositoryInterface
{

    /**
     * @param string $message
     * @return void
     */
    public function addApiLog(string $message);

    /**
     * @param string $type
     * @param mixed $data
     * @return void
     */
    public function addDataLog(string $type, $data);
}
