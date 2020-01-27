<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Plugin\Magento\Quote\Model\Quote;

use Magento\Quote\Model\Quote\Config as OriginClass;
use Thuiswinkel\BewustBezorgd\Model\Config as ConfigModel;

/**
 * Plugin for Magento\Quote\Model\Quote\Config
 */
class Config
{
    /** @var ConfigModel */
    protected $configModel;

    /**
     * Constructor.
     *
     * @param ConfigModel $configModel
     */
    public function __construct(ConfigModel $configModel)
    {
        $this->configModel = $configModel;
    }

    /**
     * Add additional configurable dimensions attributes to Quote
     *
     * @param OriginClass $subject
     * @param $result
     * @return array
     */
    public function afterGetProductAttributes(OriginClass $subject, $result)
    {
        return array_unique(array_merge($result, array_values($this->configModel->getConfigDimensionsAttributes())));
    }
}
