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
interface ShippingEmissionInterface extends ExtensibleDataInterface
{
    const EMISSION = 'emission';
    const METERS_DIESEL = 'meters_diesel';
    const METERS_GASOLINE = 'meters_gasoline';
    const MOST_EFFICIENT = 'most_efficient';
    const EMISSION_LOGO = 'emission_logo';

    /**
     * Get Emission
     *
     * @return int|float|null
     */
    public function getEmission();

    /**
     * Set Emission
     *
     * @param int|float $emission
     * @return self
     */
    public function setEmission($emission);

    /**
     * Get Meters Diesel
     *
     * @return int|float|null
     */
    public function getMetersDiesel();

    /**
     * Set Meters Diesel
     *
     * @param int|float $metersDiesel
     * @return self
     */
    public function setMetersDiesel($metersDiesel);

    /**
     * Get Meters Gasoline
     *
     * @return int|float|null
     */
    public function getMetersGasoline();

    /**
     * Set Meters Gasoline
     *
     * @param int|float $metersGasoline
     * @return self
     */
    public function setMetersGasoline($metersGasoline);

    /**
     * Get Most Efficient
     *
     * @return int|null
     */
    public function getMostEfficient();

    /**
     * Set Most Efficient
     *
     * @param int $mostEfficient
     * @return self
     */
    public function setMostEfficient($mostEfficient);

    /**
     * Get emission logo image
     *
     * @return string|null
     */
    public function getEmissionLogo();

    /**
     * Set emission logo image
     *
     * @param string $emissionLogo
     * @return self
     */
    public function setEmissionLogo($emissionLogo);

    /**
     * Retrieves existing extension attributes object or create a new one.
     *
     * @return \Thuiswinkel\BewustBezorgd\Api\Data\ShippingEmissionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Thuiswinkel\BewustBezorgd\Api\Data\OrderEmissionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Thuiswinkel\BewustBezorgd\Api\Data\ShippingEmissionExtensionInterface $extensionAttributes
    );
}
