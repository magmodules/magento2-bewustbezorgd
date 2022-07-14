<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Api\Config;

/**
 * Config Repository Interface
 */
interface RepositoryInterface
{

    /**#@+
     * System configuration path constants
     */
    public const CONFIG_XML_PATH_ACTIVE = 'bewust_bezorgd/general/active';
    public const CONFIG_XML_PATH_GATEWAY_URL = 'bewust_bezorgd/api_settings/gateway_url';
    public const CONFIG_XML_PATH_SHOP_ID = 'bewust_bezorgd/api_settings/api_shopid';
    public const CONFIG_XML_PATH_PASSWORD = 'bewust_bezorgd/api_settings/api_password';
    public const CONFIG_XML_PATH_DEBUG_ENABLED = 'bewust_bezorgd/api_settings/debug';
    public const CONFIG_XML_PATH_ALLOWED_COUNTRIES = 'bewust_bezorgd/api_settings/allowed_countries';
    public const CONFIG_XML_PATH_CAN_SHOW_LOGO = 'bewust_bezorgd/display_settings/can_show_logo';
    public const CONFIG_XML_PATH_SAVE_TO_ORDER = 'bewust_bezorgd/order_settings/save_to_order';
    public const CONFIG_XML_PATH_DEFAULT_WEIGHT = 'bewust_bezorgd/default_values/default_weight';
    public const CONFIG_XML_PATH_DEFAULT_VOLUME = 'bewust_bezorgd/default_values/default_volume';
    public const CONFIG_XML_PATH_SERVICE_TYPES = 'bewust_bezorgd/data_mapping/service_type_mapping';
    public const CONFIG_XML_PATH_GROUP_ATTRIBUTES = 'bewust_bezorgd/attributes';
    /**#@-*/

    /**
     * Set a specified store ID value
     *
     * @param int $store
     * @return $this
     */
    public function setStoreId($store);

    /**
     * Validate parameters for correct mass calculation
     *
     * @return bool
     */
    public function validateMass();

    /**
     * Check if module is enabled
     *
     * @return bool
     * @api
     */
    public function isEnabled();

    /**
     * Retrieves configuration of group "Attributes"
     *
     * @return array
     */
    public function getConfigDimensionsAttributes();

    /**
     * Retrieves API gateway URL
     *
     * @return string
     */
    public function getConfigGatewayUrl();

    /**
     * Retrieves API Shop ID
     *
     * @return string
     */
    public function getConfigApiShopId();

    /**
     * Retrieves API Password
     *
     * @return string
     */
    public function getConfigApiPassword();

    /**
     * Retrieves default weight from configuration
     *
     * @return string
     */
    public function getDefaultWeight();

    /**
     * Retrieves default volume from configuration
     *
     * @return string
     */
    public function getDefaultVolume();

    /**
     * Retrieves allowed countries from configuration
     *
     * @return string
     */
    public function getAllowedCountries();

    /**
     * Retrieves service types from configuration
     *
     * @return array
     */
    public function getServiceTypes();

    /**
     * Retrieves setting "Show BewustBezorgd Icon on Shippingmethods" from configuration
     *
     * @return bool
     */
    public function canShowLogo();

    /**
     * Retrieves save to order configuration setting
     *
     * @return bool
     */
    public function saveToOrder();

    /**
     * Retrieves dimensions unit according to selected weight unit
     *
     * @return string
     */
    public function getDimensionsUnit();

    /**
     * Check if debug mode is enabled
     *
     * @return bool
     */
    public function isDebugMode();
}
