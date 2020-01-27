<?php

/**
 * Adminhtml VAT ID validation block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Thuiswinkel\BewustBezorgd\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ValidatePermissions extends Field
{
    /**
     * API-ShopID Field Name
     *
     * @var string
     */
    protected $_apiShopId = 'bewust_bezorgd_api_settings_api_shopid';

    /**
     * API-Password Field Name
     *
     * @var string
     */
    protected $_apiPassword = 'bewust_bezorgd_api_settings_api_password';

    /**
     * Validate VAT Button Label
     *
     * @var string
     */
    protected $_buttonLabel = 'Check Permissions';

    /**
     * Set API-ShopID Field Name
     *
     * @param string $apiShopIdField
     * @return $this
     */
    public function setApiShopIdField($apiShopIdField)
    {
        $this->_apiShopId = $apiShopIdField;
        return $this;
    }

    /**
     * Get API-ShopID Field Name
     *
     * @return string
     */
    public function getApiShopIdField()
    {
        return $this->_apiShopId;
    }

    /**
     * Set API-Password Field
     *
     * @param string $apiPasswordField
     * @return $this
     */
    public function setApiPasswordField($apiPasswordField)
    {
        $this->_apiPassword = $apiPasswordField;
        return $this;
    }

    /**
     * Get API-Password Field
     *
     * @return string
     */
    public function getApiPasswordField()
    {
        return $this->_apiPassword;
    }

    /**
     * Set Button Label
     *
     * @param string $buttonLabel
     * @return $this
     */
    public function setButtonLabel($buttonLabel)
    {
        $this->_buttonLabel = $buttonLabel;
        return $this;
    }

    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('Thuiswinkel_BewustBezorgd::system/config/validate-permissions.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->_buttonLabel;
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl(
                    'bewustbezorgd/system_config_apipermissions/validate'
                ),
            ]
        );

        return $this->_toHtml();
    }
}
