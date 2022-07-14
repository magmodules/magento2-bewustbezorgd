<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Plugin\Magento\Catalog\Ui\DataProvider;

use Magento\Catalog\Ui\DataProvider\CatalogEavValidationRules as OriginClass;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Config as ShippingConfig;
use Thuiswinkel\BewustBezorgd\Api\Config\RepositoryInterface as ConfigModel;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;

/**
 * Plugin for Magento\Catalog\Ui\DataProvider\CatalogEavValidationRules
 */
class CatalogEavValidationRules
{
    public const DIMENSIONS_ATTRIBUTE_ADDITIONAL_VALIDATION_RULE = 'required-entry-if-bewust-bezorgen-three-legs';

    /** @var ShippingConfig */
    private $shippingConfig;
    /**
     * @var ConfigModel
     */
    private $configModel;
    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * Constructor.
     *
     * @param ConfigModel $configModel
     * @param ShippingConfig $shippingConfig
     * @param AttributeCollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        ConfigModel $configModel,
        ShippingConfig $shippingConfig,
        AttributeCollectionFactory $attributeCollectionFactory
    ) {
        $this->configModel = $configModel;
        $this->shippingConfig = $shippingConfig;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
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
        $dimensionsAttributeIds = $this->getDimensionsAttributeIds();

        if (in_array($attribute->getAttributeId(), $dimensionsAttributeIds)) {
            $carriers = $this->shippingConfig->getAllCarriers();
            $methods = [];

            /** @var CarrierInterface $carrierModel */
            foreach ($carriers as $carrierCode => $carrierModel) {
                $methods[$carrierCode] = $carrierModel->getAllowedMethods();
            }
            $result[self::DIMENSIONS_ATTRIBUTE_ADDITIONAL_VALIDATION_RULE] = true;
        }

        return $result;
    }

    /**
     * Retrieves attribute IDs from configuration group "Attributes"
     *
     * @return array
     */
    private function getDimensionsAttributeIds()
    {
        $attributeCodes = $this->configModel->getConfigDimensionsAttributes();
        return $this->attributeCollectionFactory->create()
            ->addFieldToFilter(AttributeSet::KEY_ENTITY_TYPE_ID, 4)
            ->addFieldToFilter('attribute_code', ['in' => $attributeCodes])
            ->getAllIds();
    }
}
