<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Helper;

use Throwable;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Thuiswinkel\BewustBezorgd\Model\Config as ConfigModel;
use Thuiswinkel\BewustBezorgd\Logger\ApiLoggerInterface as ApiLoggerInterface;
use Thuiswinkel\BewustBezorgd\Logger\DataLoggerInterface as DataLoggerInterface;

/**
 * Data Helper
 */
class Data extends AbstractHelper
{
    const ATTRIBUTE_CODE_BEWUSTBEZORGD_LEGS = 'bewustbezorgd_legs';

    /** @var ConfigModel */
    protected $configModel;

    /** @var AttributeCollectionFactory */
    protected $attributeCollectionFactory;

    /** @var EavConfig */
    protected $eavConfig;

    /** @var ShippingConfig */
    protected $shippingConfig;

    /** @var ApiLoggerInterface */
    protected $apiLogger;

    /** @var DataLoggerInterface */
    protected $dataLogger;

    /** @var null|CarrierInterface[] */
    protected $carriers = null;

    /**
     * Constructor.
     *
     * @param ConfigModel $configModel
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param EavConfig $eavConfig
     * @param ShippingConfig $shippingConfig
     * @param ApiLoggerInterface $apiLogger
     * @param DataLoggerInterface $dataLogger
     * @param Context $context
     */
    public function __construct(
        ConfigModel $configModel,
        AttributeCollectionFactory $attributeCollectionFactory,
        EavConfig $eavConfig,
        ShippingConfig $shippingConfig,
        ApiLoggerInterface $apiLogger,
        DataLoggerInterface $dataLogger,
        Context $context
    ) {
        $this->configModel = $configModel;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->shippingConfig = $shippingConfig;
        $this->apiLogger = $apiLogger;
        $this->dataLogger = $dataLogger;
        parent::__construct($context);
    }

    /**
     * Retrieves attribute IDs from configuration group "Attributes"
     *
     * @return array
     */
    public function getDimensionsAttributeIds()
    {
        $attributeCodes = $this->configModel->getConfigDimensionsAttributes();
        return $this->attributeCollectionFactory->create()
            ->addFieldToFilter(AttributeSet::KEY_ENTITY_TYPE_ID, 4)
            ->addFieldToFilter('attribute_code', ['in' => $attributeCodes])
            ->getAllIds();
    }

    /**
     * Retrieves default value of attribute "bewustbezorgd_legs"
     *
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefaultBewustbezorgdLegs()
    {
        /** @var Attribute $attribute */
        $attribute = $this->eavConfig->getAttribute(
            Product::ENTITY,
            self::ATTRIBUTE_CODE_BEWUSTBEZORGD_LEGS
        );

        return $attribute->getDefaultValue();
    }

    /**
     * Retrieves all carriers
     *
     * @return CarrierInterface[]
     */
    public function getCarriers()
    {
        if (!$this->carriers) {
            $carriers = $this->shippingConfig->getAllCarriers();

            $this->carriers = $carriers;
        }

        return $this->carriers;
    }

    /**
     * Logs data
     *
     * @param Throwable|array|string $data
     * @param string $entity
     * @return void
     */
    public function log($data, $entity = 'api')
    {
        if ($entity === 'api') {
            $this->apiLogger->add($data->getMessage());
        } elseif ($this->configModel->isDebugMode()) {
            $this->dataLogger->add($entity, $data);
        }
    }
}
