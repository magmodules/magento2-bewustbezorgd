<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Logger\Monolog;

/**
 * Class Logger Api
 */
class Api extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/bewustbezorgd/api.log';

    /**
     * @var int
     */
    protected $loggerType = Monolog::ERROR;
}
