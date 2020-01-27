<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Logger\Monolog;

/**
 * Class Logger Data
 */
class Data extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/bewustbezorgd/data.log';

    /**
     * @var int
     */
    protected $loggerType = Monolog::DEBUG;
}
