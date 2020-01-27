<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model;

/**
 * Class Converter needs for convert weight or dimension
 */
class Converter
{
    /**
     * Map for weight converting
     *
     * @var array
     */
    protected $weightConvertMap = [
        'lbs'   => 453.59237,
        'kgs'   => 1000
    ];

    /**
     * Map for dimension converting
     *
     * @var array
     */
    protected $volumeConvertMap = [
        'in'   => 0.0163871,
        'cm'   => 0.001
    ];

    /**
     * Retrieves converted value from the requested unit to kilograms
     *
     * @param $value
     * @param $unit
     * @return float|int
     */
    public function convertWeightToGrams($value, $unit)
    {
        return $value * $this->weightConvertMap[$unit];
    }

    /**
     * Retrieves converted value from the requested unit to liters
     *
     * @param $value
     * @param $unit
     * @return float|int
     */
    public function convertVolumeToLiters($value, $unit)
    {
        return $value * $this->volumeConvertMap[$unit];
    }
}
