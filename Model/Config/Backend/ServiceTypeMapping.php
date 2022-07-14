<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Exception\LocalizedException;
use Thuiswinkel\BewustBezorgd\Model\Config\ServiceType;

/**
 * Config model for Service Type Mapping
 */
class ServiceTypeMapping extends Value
{
    /** @var ServiceType */
    private $serviceType;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ServiceType $serviceType
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ServiceType $serviceType,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->serviceType = $serviceType;
    }

    /**
     * Unserialize the value loaded from the database
     *
     * @return $this|Value
     * @throws LocalizedException
     */
    protected function _afterLoad()
    {
        $value = $this->serviceType->makeArrayFieldValue($this->getValue());
        $this->setValue($value);
        return $this;
    }

    /**
     * Serialize the value before it is saved to the database
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->serviceType->makeStorableArrayFieldValue($this->getValue());
        $this->setValue($value);
        return $this;
    }
}
