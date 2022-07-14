<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Model\Config;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Thuiswinkel\BewustBezorgd\Api\Log\RepositoryInterface as LogRepositoryInterface;
use Thuiswinkel\BewustBezorgd\Api\Config\RepositoryInterface as ConfigRepositoryInterface;

/**
 * Config repo class
 */
class Repository implements ConfigRepositoryInterface
{

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var DirectoryHelper */
    private $directoryHelper;

    /** @var SerializerInterface */
    private $serializer;

    /** @var int */
    private $storeId;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var LogRepositoryInterface
     */
    private $logRepository;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param DirectoryHelper $directoryHelper
     * @param SerializerInterface $serializer
     * @param AppState $appState
     * @param Quote $quote
     * @param LogRepositoryInterface $logRepository
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DirectoryHelper $directoryHelper,
        SerializerInterface $serializer,
        AppState $appState,
        Quote $quote,
        LogRepositoryInterface $logRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->directoryHelper = $directoryHelper;
        $this->serializer = $serializer;
        $this->appState = $appState;
        $this->quote = $quote;
        $this->logRepository = $logRepository;
    }

    /**
     * @inheritDoc
     */
    public function setStoreId($store)
    {
        $this->storeId = $store;
        return $this;
    }

    /**
     * @inheritDoc
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
            ) {
                return false;
            } elseif ($length == 0
                || $width == 0
                || $height == 0
            ) {
                $this->logRepository->addDataLog('data', "Product volume is null");
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
