<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Plugin\Magento\Catalog\Ui\DataProvider;

use Magento\Catalog\Ui\DataProvider\CatalogEavValidationRules as OriginClass;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Thuiswinkel\BewustBezorgd\Helper\Data as DataHelper;

/**
 * Plugin for Magento\Catalog\Ui\DataProvider\CatalogEavValidationRules
 */
class CatalogEavValidationRules
{
    const DIMENSIONS_ATTRIBUTE_ADDITIONAL_VALIDATION_RULE = 'required-entry-if-bewust-bezorgen-three-legs';

    /** @var DataHelper */
    protected $helper;

    /**
     * Constructor.
     *
     * @param DataHelper $helper
     */
    public function __construct(DataHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Add additional validation to dimensions attributes
     *
     * @param OriginClass $subject
     * @param $result
     * @param ProductAttributeInterface $attribute
     * @param $data
     * @return mixed
     */
    public function afterBuild(OriginClass $subject, $result, ProductAttributeInterface $attribute, $data)
    {
        $dimensionsAttributeIds = $this->helper->getDimensionsAttributeIds();

        if (in_array($attribute->getAttributeId(), $dimensionsAttributeIds)) {
            $carriers = $this->helper->getCarriers();
            $methods = [];

            /** @var CarrierInterface $carrierModel */
            foreach ($carriers as $carrierCode => $carrierModel) {
                $methods[$carrierCode] = $carrierModel->getAllowedMethods();
            }
            $result[self::DIMENSIONS_ATTRIBUTE_ADDITIONAL_VALIDATION_RULE] = true;
        }

        return $result;
    }
}
