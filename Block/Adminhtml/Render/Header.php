<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Block\Adminhtml\Render;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Header
 *
 * @package Thuiswinkel\BewustBezorgd\Block\Adminhtml\Render
 */
class Header extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Thuiswinkel_BewustBezorgd::system/config/header.phtml';

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->addClass('bewustbezorgd');
        return $this->toHtml();
    }
}
