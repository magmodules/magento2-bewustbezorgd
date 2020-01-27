<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model;

/**
 * Class ArrayCalculator
 */
class ArrayCalculator implements ArrayCalculatorInterface
{
    /**
     * {@inheritDoc}
     */
    public function calculateFieldSumByKey(array $array, $calculatedKey)
    {
        if (!count($array)) {
            return $array;
        }
        $result = array_shift($array);

        while (count($array)) {
            $item = array_shift($array);
            $result[$calculatedKey] += $item[$calculatedKey];
        }

        return $result;
    }
}
