<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for service type select drop down
 */
class ServiceType implements OptionSourceInterface
{
    /**
     * Allowed service types
     *
     * @var array
     */
    private $allowedServiceTypes = [
        'NextDay',
        'SmallTimeframe (1,2 hours)',
        'MediumTimeframe (4 hours)',
        'EveningDelivery',
        'TwomanDelivery',
        'SameDay',
        'SundayDelivery'
    ];

    /** @var array */
    private $options = [];

    /**
     * Return options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $options = [];

            foreach ($this->allowedServiceTypes as $allowedServiceType) {
                $options[] = ['value' => $allowedServiceType, 'label' => __($allowedServiceType)];
            }
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);

            $this->options = $options;
        }

        return $this->options;
    }
}
