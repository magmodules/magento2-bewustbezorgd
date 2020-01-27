<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Shipping\Model\Carrier\CarrierInterface;
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

    /**
     * @param DataHelper $helper
     */
    public function __construct(DataHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Return options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $carriers = $this->helper->getCarriers();
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
