<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\State as AppState;
use Magento\Quote\Model\Quote;
use Thuiswinkel\BewustBezorgd\Logger\DataLoggerInterface
    as DataLoggerInterface;

/**
 * Class Config
 */
class Config
{
    /**#@+
     * System configuration path constants
     */
    const CONFIG_XML_PATH_ACTIVE = 'bewust_bezorgd/general/active';
    const CONFIG_XML_PATH_GATEWAY_URL = 'bewust_bezorgd/api_settings/gateway_url';
    const CONFIG_XML_PATH_SHOP_ID = 'bewust_bezorgd/api_settings/api_shopid';
    const CONFIG_XML_PATH_PASSWORD = 'bewust_bezorgd/api_settings/api_password';
    const CONFIG_XML_PATH_DEBUG_ENABLED = 'bewust_bezorgd/api_settings/debug';
    const CONFIG_XML_PATH_ALLOWED_COUNTRIES = 'bewust_bezorgd/api_settings/allowed_countries';
    const CONFIG_XML_PATH_CAN_SHOW_LOGO = 'bewust_bezorgd/display_settings/can_show_logo';
    const CONFIG_XML_PATH_SAVE_TO_ORDER = 'bewust_bezorgd/order_settings/save_to_order';
    const CONFIG_XML_PATH_DEFAULT_WEIGHT = 'bewust_bezorgd/default_values/default_weight';
    const CONFIG_XML_PATH_DEFAULT_VOLUME = 'bewust_bezorgd/default_values/default_volume';
    const CONFIG_XML_PATH_SERVICE_TYPES = 'bewust_bezorgd/data_mapping/service_type_mapping';
    const CONFIG_XML_PATH_GROUP_ATTRIBUTES = 'bewust_bezorgd/attributes';
    /**#@-*/

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var DirectoryHelper */
    protected $directoryHelper;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var int */
    protected $storeId;

    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var DataLoggerInterface
     */
    private $dataLogger;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param DirectoryHelper $directoryHelper
     * @param SerializerInterface $serializer
     * @param AppState $appState
     * @param Quote $quote
     * @param DataLoggerInterface $dataLogger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DirectoryHelper $directoryHelper,
        SerializerInterface $serializer,
        AppState $appState,
        Quote $quote,
        DataLoggerInterface $dataLogger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->directoryHelper = $directoryHelper;
        $this->serializer = $serializer;
        $this->appState = $appState;
        $this->quote = $quote;
        $this->dataLogger = $dataLogger;
    }

    /**
     * Set a specified store ID value
     *
     * @param int $store
     * @return $this
     */
    public function setStoreId($store)
    {
        $this->storeId = $store;
        return $this;
    }

    /**
     * Validate parameters for correct mass calculation
     *
     * @return bool
     */
    public function validateMass()
    {
        foreach ($this->quote->getAllItems() as $quoteItem) {
            if ($quoteItem->getProductType() != 'simple') {
                continue;
            }
            $dimensionAttributes = $this->getConfigDimensionsAttributes();
            $length = $quoteItem->getProduct()
                ->getData($dimensionAttributes['attribute_length']);
            $width = $quoteItem->getProduct()
                ->getData($dimensionAttributes['attribute_width']);
            $height = $quoteItem->getProduct()
                ->getData($dimensionAttributes['attribute_height']);
            if ($length === null
                || $width === null
                || $height === null
            ){
                return false;
            } elseif ($length == 0
                || $width == 0
                || $height == 0
            ) {
                $this->dataLogger->add('data', "Product volume is null");
                return false;
            }
        }
        return true;
    }

    /**
     * Check if module is enabled
     *
     * @return bool
     * @api
     */
    public function isEnabled()
    {
        if ($this->appState->getAreaCode() == 'frontend') {
            if (!$this->validateMass()) {
                return false;
            }
        }
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Retrieves configuration of group "Attributes"
     *
     * @return array
     */
    public function getConfigDimensionsAttributes()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_GROUP_ATTRIBUTES,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Retrieves API gateway URL
     *
     * @return string
     */
    public function getConfigGatewayUrl()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_GATEWAY_URL,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Retrieves API Shop ID
     *
     * @return string
     */
    public function getConfigApiShopId()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_SHOP_ID,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Retrieves API Password
     *
     * @return string
     */
    public function getConfigApiPassword()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_PASSWORD,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Retrieves default weight from configuration
     *
     * @return string
     */
    public function getDefaultWeight()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_DEFAULT_WEIGHT,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Retrieves default volume from configuration
     *
     * @return string
     */
    public function getDefaultVolume()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_DEFAULT_VOLUME,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Retrieves allowed countries from configuration
     *
     * @return string
     */
    public function getAllowedCountries()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_ALLOWED_COUNTRIES,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Retrieves service types from configuration
     *
     * @return array
     */
    public function getServiceTypes()
    {
        return $this->serializer->unserialize(
            $this->scopeConfig->getValue(
                self::CONFIG_XML_PATH_SERVICE_TYPES,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            )
        );
    }

    /**
     * Retrieves setting "Show BewustBezorgd Icon on Shippingmethods" from configuration
     *
     * @return bool
     */
    public function canShowLogo()
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_CAN_SHOW_LOGO,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Retrieves save to order configuration setting
     *
     * @return bool
     */
    public function saveToOrder()
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_SAVE_TO_ORDER,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Retrieves dimensions unit according to selected weight unit
     *
     * @return string
     */
    public function getDimensionsUnit()
    {
        $weightUnit = $this->directoryHelper->getWeightUnit();
        if ($weightUnit === 'lbs') {
            return 'in';
        }

        return 'cm';
    }

    /**
     * Check if debug mode is enabled
     *
     * @return bool
     */
    public function isDebugMode()
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_DEBUG_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }
}
