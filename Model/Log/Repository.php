<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Model\Log;

use Thuiswinkel\BewustBezorgd\Api\Log\RepositoryInterface;
use Thuiswinkel\BewustBezorgd\Logger\Api;
use Thuiswinkel\BewustBezorgd\Logger\Data;

/**
 * Log Repository class
 */
class Repository implements RepositoryInterface
{

    /**
     * @var Api
     */
    private $api;
    /**
     * @var Data
     */
    private $data;

    /**
     * Repository constructor.
     * @param Data $data
     * @param Api $api
     */
    public function __construct(
        Data $data,
        Api $api
    ) {
        $this->data = $data;
        $this->api = $api;
    }

    /**
     * @inheritDoc
     */
    public function addApiLog(string $message)
    {
        $this->api->add($message);
    }

    /**
     * @inheritDoc
     */
    public function addDataLog(string $type, $data)
    {
        $this->data->add($type, $data);
    }
}
