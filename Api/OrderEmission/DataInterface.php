<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Api\OrderEmission;

use Magento\Framework\Api\ExtensionAttributesInterface;

/**
 * Order Emission Data Interface
 */
interface DataInterface
{

    public const ORDER_EMISSION_ID = 'order_emission_id';
    public const SERVICE_TYPE = 'service_type';
    public const ORDER_ID = 'order_id';
    public const METERS_DIESEL = 'meters_diesel';
    public const METERS_GASOLINE = 'meters_gasoline';
    public const EMISSION = 'emission';

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
     * @return self
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
     * @return self
     */
    public function setOrderId($orderId);

    /**
     * Get service_type
     * @return string|null
     */
    public function getServiceType();

    /**
     * Set Service Type
     *
     * @param string $serviceType
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
     */
    public function setMetersGasoline($metersGasoline);
}
