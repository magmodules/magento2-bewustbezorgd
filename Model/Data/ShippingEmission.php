<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Thuiswinkel\BewustBezorgd\Api\Data\ShippingEmissionInterface;
use Thuiswinkel\BewustBezorgd\Api\Data\ShippingEmissionExtensionInterface;

/**
 * Shipping Emission Data Model
 */
class ShippingEmission extends AbstractExtensibleObject implements ShippingEmissionInterface
{
    /**
     * {@inheritDoc}
     */
    public function getEmission()
    {
        return $this->_get(self::EMISSION);
    }

    /**
     * {@inheritDoc}
     */
    public function setEmission($emission)
    {
        return $this->setData(self::EMISSION, $emission);
    }

    /**
     * {@inheritDoc}
     */
    public function getMetersDiesel()
    {
        return $this->_get(self::METERS_DIESEL);
    }

    /**
     * {@inheritDoc}
     */
    public function setMetersDiesel($metersDiesel)
    {
        return $this->setData(self::METERS_DIESEL, $metersDiesel);
    }

    /**
     * {@inheritDoc}
     */
    public function getMetersGasoline()
    {
        return $this->_get(self::METERS_GASOLINE);
    }

    /**
     * {@inheritDoc}
     */
    public function setMetersGasoline($metersGasoline)
    {
        return $this->setData(self::METERS_GASOLINE, $metersGasoline);
    }

    /**
     * {@inheritDoc}
     */
    public function getMostEfficient()
    {
        return $this->_get(self::MOST_EFFICIENT);
    }

    /**
     * {@inheritDoc}
     */
    public function setMostEfficient($mostEfficient)
    {
        return $this->setData(self::MOST_EFFICIENT, $mostEfficient);
    }

    /**
     * {@inheritDoc}
     */
    public function getEmissionLogo()
    {
        return $this->_get(self::EMISSION_LOGO);
    }

    /**
     * {@inheritDoc}
     */
    public function setEmissionLogo($emissionLogo)
    {
        return $this->setData(self::EMISSION_LOGO, $emissionLogo);
    }

    /**
     * {@inheritDoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritDoc}
     */
    public function setExtensionAttributes(ShippingEmissionExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
