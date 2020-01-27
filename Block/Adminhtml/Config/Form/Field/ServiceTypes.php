<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Block\Adminhtml\Config\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Thuiswinkel\BewustBezorgd\Model\Config\Source\ServiceType;

/**
 * HTML Select for Service Types
 */
class ServiceTypes extends Select
{
    /** @var ServiceType */
    private $serviceType;

    /**
     * @param Context $context
     * @param ServiceType $serviceType
     * @param array $data
     */
    public function __construct(Context $context, ServiceType $serviceType, array $data = [])
    {
        parent::__construct($context, $data);
        $this->serviceType = $serviceType;
    }

    /**
     * Get country options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->serviceType->toOptionArray();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setData('name', $value);
    }

    /**
     * Sets id for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setData('id', $value);
    }
}
