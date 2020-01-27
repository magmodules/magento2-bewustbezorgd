<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model\Product\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Source Model for BewustbezorgdLegs attribute
 */
class BewustbezorgdLegs extends AbstractSource
{
    /**#@+
     * Constants
     */
    const TWO_LEGS_OPTION_ID = 1;
    const THREE_LEGS_OPTION_ID = 2;
    /**#@-*/

    /**
     * Map of option id and api request path
     *
     * @var array
     */
    private $optionValues = [
        self::TWO_LEGS_OPTION_ID => 'two-legs',
        self::THREE_LEGS_OPTION_ID => 'three-legs'
    ];

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['value' => '', 'label' => __('-- Please select --')],
            ['value' => self::TWO_LEGS_OPTION_ID, 'label' => __('2 man delivery | big package')],
            ['value' => self::THREE_LEGS_OPTION_ID, 'label' => __('Regular Package')]
        ];

        return $this->_options;
    }

    /**
     * Retrieves Option Value Mapping
     *
     * @return array
     */
    public function getOptionValues()
    {
        return $this->optionValues;
    }
}
