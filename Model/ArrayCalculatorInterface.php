<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model;

/**
 * Interface ArrayCalculatorInterface
 */
interface ArrayCalculatorInterface
{
    /**
     * Calculates sum array field by key
     *
     * Input array should be like
     * [
     *     ['key1' => 'value1', 'key2' => 'value2', ... 'keyN' => 'valueN'],
     *     ['key1' => 'value1', 'key2' => 'value2', ... 'keyN' => 'valueN'],
     *      ...
     *     ['key1' => 'value1', 'key2' => 'value2', ... 'keyN' => 'valueN']
     * ]
     *
     * Example output array
     * ['key1' => 'value1', 'calculatedKey' => SUM('value'), ... 'keyN' => 'valueN']
     *
     * @param array $array
     * @param $calculatedKey
     * @return array
     */
    public function calculateFieldSumByKey(array $array, $calculatedKey);
}
