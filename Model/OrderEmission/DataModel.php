<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Model\OrderEmission;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\Model\AbstractModel;
use Thuiswinkel\BewustBezorgd\Api\OrderEmission\DataInterface;

/**
 * Order Emission Data Model
 */
class DataModel extends AbstractModel implements ExtensibleDataInterface, DataInterface
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'thuiswinkel_order_emission';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getOrderEmissionId()
    {
        return $this->getData(self::ORDER_EMISSION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderEmissionId($orderEmissionId)
    {
        return $this->setData(self::ORDER_EMISSION_ID, $orderEmissionId);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getServiceType()
    {
        return $this->getData(self::SERVICE_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setServiceType($serviceType)
    {
        return $this->setData(self::SERVICE_TYPE, $serviceType);
    }

    /**
     * @inheritDoc
     */
    public function getEmission()
    {
        return $this->getData(self::EMISSION);
    }

    /**
     * @inheritDoc
     */
    public function setEmission($emission)
    {
        return $this->setData(self::EMISSION, $emission);
    }

    /**
     * @inheritDoc
     */
    public function getMetersDiesel()
    {
        return $this->getData(self::METERS_DIESEL);
    }

    /**
     * @inheritDoc
     */
    public function setMetersDiesel($metersDiesel)
    {
        return $this->setData(self::METERS_DIESEL, $metersDiesel);
    }

    /**
     * Get Meters Gasoline
     *
     * @return string|null
     */
    public function getMetersGasoline()
    {
        return $this->getData(self::METERS_GASOLINE);
    }

    /**
     * @inheritDoc
     */
    public function setMetersGasoline($metersGasoline)
    {
        return $this->setData(self::METERS_GASOLINE, $metersGasoline);
    }
}
