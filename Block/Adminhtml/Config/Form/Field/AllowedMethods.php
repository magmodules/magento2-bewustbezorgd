<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Block\Adminhtml\Config\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Thuiswinkel\BewustBezorgd\Model\Config\Source\AllowedMethod;

/**
 * HTML Select for Allowed Shipping Methods
 */
class AllowedMethods extends Select
{
    /** @var AllowedMethod */
    private $allowedMethod;

    /**
     * Constructor
     *
     * @param Context $context
     * @param AllowedMethod $allowedMethod
     * @param array $data
     */
    public function __construct(Context $context, AllowedMethod $allowedMethod, array $data = [])
    {
        parent::__construct($context, $data);
        $this->allowedMethod = $allowedMethod;
    }

    /**
     * Get country options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->allowedMethod->toOptionArray();
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
