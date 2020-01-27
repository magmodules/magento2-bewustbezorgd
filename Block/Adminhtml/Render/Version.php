<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Block\Adminhtml\Render;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\ModuleListInterface;

/**
 * Class Version
 */
class Version extends Field
{
    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * Version constructor.
     *
     * @param Context             $context
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        Context $context,
        ModuleListInterface $moduleList
    ) {
        $this->moduleList = $moduleList;
        parent::__construct($context);
    }

    /**
     * Render block: extension version
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = '<tr id="row_' . $element->getHtmlId() . '">';
        $html .= ' <td class="label">' . $element->getData('label') . '</td>';
        $html .= ' <td class="value">' . $this->getExtensionVersion() . '</td>';
        $html .= ' <td></td>';
        $html .= '</tr>';

        return $html;
    }

    /**
     * Returns current version of the extension for admin display
     *
     * @return string
     */
    private function getExtensionVersion()
    {
        $moduleInfo = $this->moduleList->getOne('Thuiswinkel_BewustBezorgd');
        return $moduleInfo['setup_version'];
    }
}
