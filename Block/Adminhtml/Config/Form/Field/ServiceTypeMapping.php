<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Block\Adminhtml\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObject;

/**
 * System config field renderer for Service Type Mapping
 */
class ServiceTypeMapping extends AbstractFieldArray
{

    /** @var AllowedMethods */
    private $allowedMethodRenderer;

    /** @var ServiceTypes */
    private $serviceTypeRenderer;

    /**
     * Prepare to render method
     *
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'allowed_method',
            [
                'label' => __('Allowed Method'),
                'renderer' => $this->getAllowedMethodRenderer(),
            ]
        );
        $this->addColumn(
            'service_type',
            [
                'label' => __('Service Type'),
                'renderer' => $this->getServiceTypeRenderer(),
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare array row method
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $allowedMethod = $row->getData('allowed_method');
        $serviceType = $row->getData('service_type');
        $options = [];
        if ($allowedMethod) {
            $name = 'option_' . $this->getAllowedMethodRenderer()->calcOptionHash($allowedMethod);
            $options[$name] = 'selected="selected"';
        }
        if ($serviceType) {
            $name = 'option_' . $this->getServiceTypeRenderer()->calcOptionHash($serviceType);
            $options[$name] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Retrieve "Allowed Method" renderer
     *
     * @return AllowedMethods
     * @throws LocalizedException
     */
    private function getAllowedMethodRenderer()
    {
        if (!$this->allowedMethodRenderer) {
            $this->allowedMethodRenderer = $this->getLayout()->createBlock(
                AllowedMethods::class,
                '',
                [
                    'data' => [
                        'is_render_to_js_template' => true,
                        'class' => 'input-text required-entry validate-no-empty',
                    ],
                ]
            );
        }

        return $this->allowedMethodRenderer;
    }

    /**
     * Retrieve "Service Type" Renderer
     *
     * @return ServiceTypes
     * @throws LocalizedException
     */
    private function getServiceTypeRenderer()
    {
        if (!$this->serviceTypeRenderer) {
            $this->serviceTypeRenderer = $this->getLayout()->createBlock(
                ServiceTypes::class,
                '',
                [
                    'data' => [
                        'is_render_to_js_template' => true,
                        'class' => 'input-text required-entry validate-no-empty',
                    ],
                ]
            );
        }

        return $this->serviceTypeRenderer;
    }
}
