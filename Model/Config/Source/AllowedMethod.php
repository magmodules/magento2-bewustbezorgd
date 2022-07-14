<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Config as ShippingConfig;
use Thuiswinkel\BewustBezorgd\Helper\Data as DataHelper;

/**
 * Source model for allowed method select drop down
 */
class AllowedMethod implements OptionSourceInterface
{
    /** @var DataHelper */
    private $helper;

    /** @var array */
    private $options = [];

    /** @var ShippingConfig */
    private $shippingConfig;

    /**
     * @param ShippingConfig $shippingConfig
     */
    public function __construct(ShippingConfig $shippingConfig)
    {
        $this->shippingConfig = $shippingConfig;
    }

    /**
     * Return options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $carriers = $this->shippingConfig->getAllCarriers();
            $options = [];

            /** @var CarrierInterface $carrierModel */
            foreach ($carriers as $carrierCode => $carrierModel) {
                $options[$carrierCode] = [
                    'value' => $this->getAllowedMethods($carrierCode, $carrierModel),
                    'label' => $carrierModel->getConfigData('title')
                ];
            }
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);

            $this->options = $options;
        }

        return $this->options;
    }

    /**
     * Retrieves allowed methods from carrier
     *
     * @param $carrierCode
     * @param $carrierModel
     * @return array
     */
    protected function getAllowedMethods($carrierCode, $carrierModel)
    {
        $allowedMethods = [];
        foreach ($carrierModel->getAllowedMethods() as $methodCode => $methodTitle) {
            $allowedMethods[$carrierCode . '_' . $methodCode] = $methodTitle;
        }

        return $allowedMethods;
    }
}
