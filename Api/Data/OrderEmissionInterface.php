<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Order Emission Interface
 */
interface OrderEmissionInterface extends ExtensibleDataInterface
{

    const ORDER_EMISSION_ID = 'order_emission_id';
    const SERVICE_TYPE = 'service_type';
    const ORDER_ID = 'order_id';
    const METERS_DIESEL = 'meters_diesel';
    const METERS_GASOLINE = 'meters_gasoline';
    const EMISSION = 'emission';

    /**
     * Get Order Emission ID
     *
     * @return string|null
     */
    public function getOrderEmissionId();

    /**
     * Set Order Emission ID
     *
     * @param string $orderEmissionId
     * @return OrderEmissionInterface
     */
    public function setOrderEmissionId($orderEmissionId);

    /**
     * Get Order ID
     *
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set Order ID
     *
     * @param string $orderId
     * @return OrderEmissionInterface
     */
    public function setOrderId($orderId);

    /**
     * Retrieves existing extension attributes object or create a new one.
     *
     * @return \Thuiswinkel\BewustBezorgd\Api\Data\OrderEmissionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Thuiswinkel\BewustBezorgd\Api\Data\OrderEmissionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Thuiswinkel\BewustBezorgd\Api\Data\OrderEmissionExtensionInterface $extensionAttributes
    );

    /**
     * Get service_type
     * @return string|null
     */
    public function getServiceType();

    /**
     * Set Service Type
     *
     * @param string $serviceType
     * @return OrderEmissionInterface
     */
    public function setServiceType($serviceType);

    /**
     * Get Emission
     *
     * @return string|null
     */
    public function getEmission();

    /**
     * Set Emission
     *
     * @param string $emission
     * @return OrderEmissionInterface
     */
    public function setEmission($emission);

    /**
     * Get Meters Diesel
     *
     * @return string|null
     */
    public function getMetersDiesel();

    /**
     * Set Meters Diesel
     *
     * @param string $metersDiesel
     * @return OrderEmissionInterface
     */
    public function setMetersDiesel($metersDiesel);

    /**
     * Get Meters Gasoline
     *
     * @return string|null
     */
    public function getMetersGasoline();

    /**
     * Set Meters Gasoline
     *
     * @param string $metersGasoline
     * @return OrderEmissionInterface
     */
    public function setMetersGasoline($metersGasoline);
}
