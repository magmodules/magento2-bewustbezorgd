<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Math\Random;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class encode/decode Service Type configuration
 */
class ServiceType
{
    /** @var Random */
    private $mathRandom;

    /**
     * @param Random $mathRandom
     */
    public function __construct(Random $mathRandom)
    {
        $this->mathRandom = $mathRandom;
    }

    /**
     * Make value readable by @see AbstractFieldArray
     *
     * @param string|array $value
     * @return array
     * @throws LocalizedException
     */
    public function makeArrayFieldValue($value)
    {
        $value = $this->unserializeValue($value);
        if (!$this->isEncodedArrayFieldValue($value)) {
            return $this->encodeArrayFieldValue($value);
        }

        return $this->unserializeValue($value);
    }

    /**
     * Make value ready for store
     *
     * @param string|array $value
     * @return string
     */
    public function makeStorableArrayFieldValue($value)
    {
        if ($this->isEncodedArrayFieldValue($value)) {
            $value = $this->decodeArrayFieldValue($value);
        }

        return $this->serializeValue($value);
    }

    /**
     * Decode value from used in @see AbstractFieldArray
     *
     * @param array $value
     * @return array
     */
    private function decodeArrayFieldValue(array $value)
    {
        $result = [];
        unset($value['__empty']);
        foreach ($value as $row) {
            if (!is_array($row)
                || !array_key_exists('allowed_method', $row)
                || !array_key_exists('service_type', $row)
            ) {
                continue;
            }
            $allowedMethod = $row['allowed_method'];
            $serviceType = $row['service_type'];
            $result[$allowedMethod] = $serviceType;
        }

        return $result;
    }

    /**
     * Encode value to be used in @see AbstractFieldArray
     *
     * @param array $value
     * @return array
     * @throws LocalizedException
     */
    private function encodeArrayFieldValue(array $value)
    {
        $result = [];
        foreach ($value as $allowedMethod => $serviceType) {
            $resultId = $this->mathRandom->getUniqueHash('_');
            $result[$resultId] = ['allowed_method' => $allowedMethod, 'service_type' => $serviceType];
        }

        return $result;
    }

    /**
     * Check whether value is in form retrieved by @see encodeArrayFieldValue
     *
     * @param string|array $value
     * @return bool
     */
    private function isEncodedArrayFieldValue($value)
    {
        if (!is_array($value)) {
            return false;
        }
        unset($value['__empty']);
        foreach ($value as $row) {
            if (!is_array($row)
                || !array_key_exists('allowed_method', $row)
                || !array_key_exists('service_type', $row)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate a storable representation of a value
     *
     * @param array $value
     * @return string
     */
    private function serializeValue($value)
    {
        if (is_array($value)) {
            $data = [];
            foreach ($value as $allowedMethod => $serviceType) {
                if (!array_key_exists($allowedMethod, $data)) {
                    $data[$allowedMethod] = $serviceType;
                }
            }

            return json_encode($data, true);
        }

        return '';
    }

    /**
     * Create a value from a storable representation
     *
     * @param string|null $value
     * @return array
     */
    public function unserializeValue($value)
    {
        if (is_string($value) && !empty($value)) {
            return json_decode($value, true);
        }

        return [];
    }
}
