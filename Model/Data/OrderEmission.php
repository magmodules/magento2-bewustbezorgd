<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Thuiswinkel\BewustBezorgd\Api\Data\OrderEmissionInterface;
use Thuiswinkel\BewustBezorgd\Api\Data\OrderEmissionExtensionInterface;

/**
 * Order Emission Data Model
 */
class OrderEmission extends AbstractExtensibleObject implements OrderEmissionInterface
{

    /**
     * Get Order Emission ID
     *
     * @return string|null
     */
    public function getOrderEmissionId()
    {
        return $this->_get(self::ORDER_EMISSION_ID);
    }

    /**
     * Set Order Emission ID
     *
     * @param string $orderEmissionId
     * @return OrderEmissionInterface
     */
    public function setOrderEmissionId($orderEmissionId)
    {
        return $this->setData(self::ORDER_EMISSION_ID, $orderEmissionId);
    }

    /**
     * Get Order ID
     *
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * Set Order ID
     *
     * @param string $orderId
     * @return OrderEmissionInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Retrieves existing extension attributes object or create a new one.
     *
     * @return ExtensionAttributesInterface|OrderEmissionExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param OrderEmissionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(OrderEmissionExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get Service Type
     *
     * @return string|null
     */
    public function getServiceType()
    {
        return $this->_get(self::SERVICE_TYPE);
    }

    /**
     * Set Service Type
     *
     * @param string $serviceType
     * @return OrderEmissionInterface
     */
    public function setServiceType($serviceType)
    {
        return $this->setData(self::SERVICE_TYPE, $serviceType);
    }

    /**
     * Get Emission
     *
     * @return string|null
     */
    public function getEmission()
    {
        return $this->_get(self::EMISSION);
    }

    /**
     * Set Emission
     *
     * @param string $emission
     * @return OrderEmissionInterface
     */
    public function setEmission($emission)
    {
        return $this->setData(self::EMISSION, $emission);
    }

    /**
     * Get Meters Diesel
     *
     * @return string|null
     */
    public function getMetersDiesel()
    {
        return $this->_get(self::METERS_DIESEL);
    }

    /**
     * Set Meters Diesel
     *
     * @param string $metersDiesel
     * @return OrderEmissionInterface
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
        return $this->_get(self::METERS_GASOLINE);
    }

    /**
     * Set Meters Gasoline
     *
     * @param string $metersGasoline
     * @return OrderEmissionInterface
     */
    public function setMetersGasoline($metersGasoline)
    {
        return $this->setData(self::METERS_GASOLINE, $metersGasoline);
    }
}
