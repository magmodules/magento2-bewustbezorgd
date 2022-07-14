<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Service;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Checkout\Model\Session;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Filter\TruncateFilter\Result;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ShippingMethodExtensionFactory;
use Magento\Quote\Api\Data\ShippingMethodExtensionInterface;
use Magento\Quote\Model\Cart\ShippingMethod;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use RuntimeException;
use Throwable;
use Thuiswinkel\BewustBezorgd\Api\Log\RepositoryInterface as LogRepository;
use Thuiswinkel\BewustBezorgd\Service\ApiConnection;
use Thuiswinkel\BewustBezorgd\Api\Config\RepositoryInterface as ConfigModel;
use Thuiswinkel\BewustBezorgd\Model\Exception\WrongApiConfigurationException;
use Thuiswinkel\BewustBezorgd\Model\Exception\WrongApiCredentialsException;
use Thuiswinkel\BewustBezorgd\Model\Product\Attribute\Source\BewustbezorgdLegs as BewustbezorgdLegsAttributeSource;

/**
 * Emission collector service class
 */
class CollectEmission
{

    /**#@+
     * Constants
     */
    private const CALCULATED_FIELD_KEYS = [
        'two-legs'      => 'Weight',
        'three-legs'    => 'Mass'
    ];
    private const ATTRIBUTE_CODE_BEWUSTBEZORGD_LEGS = 'bewustbezorgd_legs';
    /**#@-*/

    /**
     * Map for weight converting
     *
     * @var array
     */
    private const WEIGHT_CONVERT_MAP = [
        'lbs'   => 453.59237,
        'kgs'   => 1000
    ];

    /**
     * Map for dimension converting
     *
     * @var array
     */
    private const VOLUME_CONVERT_MAP = [
        'in'   => 0.0163871,
        'cm'   => 0.001
    ];

    /**
     * Headers for CSV files
     *
     * @var array
     */
    private const CSV_HEADER = [
        'two-legs'      => [
            'postcode_from' => 'From Postal Code',
            'country_from'  => 'From Country',
            'postcode_to'   => 'To Postal Code',
            'country_to'    => 'To Country',
            'weight_mass'   => 'Weight',
            'service_type'  => 'Service Type'
        ],
        'three-legs'    => [
            'postcode_from' => 'From Postal Code',
            'country_from'  => 'From Country',
            'postcode_to'   => 'To Postal Code',
            'country_to'    => 'To Country',
            'weight_mass'   => 'Mass',
            'service_type'  => 'Service Type'
        ]
    ];

    /**
     * @var Result
     */
    private $truncateResult = null;

    /** @var ConfigModel */
    private $configModel;

    /** @var DirectoryHelper */
    private $directoryHelper;

    /** @var ApiConnection */
    private $apiConnection;

    /** @var ShippingMethodExtensionFactory */
    private $extensionFactory;

    /** @var Session */
    private $session;

    /** @var SerializerInterface */
    private $serializer;

    /** @var EavConfig */
    protected $eavConfig;

    /**
     * Store Information Country ID
     *
     * @var string
     */
    private $storeCountryId;

    /**
     * Store Information Postcode
     *
     * @var string
     */
    private $storePostcode;

    /**
     * Filter manager
     *
     * @var FilterManager
     */
    private $filterManager;

    /**
     * Option map for attribute "bewustbezorgdLegs"
     *
     * @var array
     */
    private $bewustbezorgdLegsMap;

    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * @var array
     */
    private $shippingMethodKeys = [];

    /**
     * Constructor.
     *
     * @param ConfigModel $configModel
     * @param DirectoryHelper $directoryHelper
     * @param ApiConnection $apiConnection
     * @param ShippingMethodExtensionFactory $extensionFactory
     * @param Session $session
     * @param SerializerInterface $serializer
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param FilterManager $filterManager
     * @param EavConfig $eavConfig
     * @param LogRepository $logRepository
     * @param BewustbezorgdLegsAttributeSource $bewustbezorgdLegsSource
     * @throws NoSuchEntityException
     */
    public function __construct(
        ConfigModel $configModel,
        DirectoryHelper $directoryHelper,
        ApiConnection $apiConnection,
        ShippingMethodExtensionFactory $extensionFactory,
        Session $session,
        SerializerInterface $serializer,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        FilterManager $filterManager,
        EavConfig $eavConfig,
        LogRepository $logRepository,
        BewustbezorgdLegsAttributeSource $bewustbezorgdLegsSource
    ) {
        $this->configModel = $configModel;
        $this->directoryHelper = $directoryHelper;
        $this->apiConnection = $apiConnection;
        $this->extensionFactory = $extensionFactory;
        $this->session = $session;
        $this->serializer = $serializer;
        $this->filterManager = $filterManager;
        $this->eavConfig = $eavConfig;
        $this->logRepository = $logRepository;
        $this->storeCountryId = $scopeConfig->getValue(
            ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID,
            ScopeInterface::SCOPE_STORE,
            $storeManager->getStore()
        );
        $this->storePostcode = $this->truncateString(
            $scopeConfig->getValue(
                ShippingConfig::XML_PATH_ORIGIN_POSTCODE,
                ScopeInterface::SCOPE_STORE,
                $storeManager->getStore()
            )
        );
        $this->bewustbezorgdLegsMap = $bewustbezorgdLegsSource->getOptionValues();
    }

    /**
     * Emission Collect Method
     *
     * @param CartInterface $quote
     * @param $address
     * @param array $shippingMethods
     * @return ShippingMethod[]|array
     * @throws NoSuchEntityException
     * @throws FileSystemException
     * @throws WrongApiConfigurationException
     */
    public function execute(CartInterface $quote, $address, $shippingMethods)
    {
        // Check if country in store information is allowed
        if (!in_array($this->storeCountryId, explode(',', $this->configModel->getAllowedCountries()))) {
            return $shippingMethods;
        }

        // And check if country in address is allowed
        if (!in_array($address->getCountryId(), explode(',', $this->configModel->getAllowedCountries()))) {
            return $shippingMethods;
        }
        $emission = [];
        $requestData = [];
        $suffix = hash('md5', microtime());
        $collectedMethodCodes = array_keys(
            $collectedMethods = $this->configModel->getServiceTypes()
        );

        /** @var ShippingMethod $shippingMethod */
        foreach ($shippingMethods as $key => $shippingMethod) {
            $collectedMethodCode = $shippingMethod->getCarrierCode() . '_' . $shippingMethod->getMethodCode();

            //FIXME: sloppy code!
            // Fixes that provide compatibility with PostNL
            if ($shippingMethod->getCarrierCode() == 'tig_postnl' && $shippingMethod->getMethodCode() == 'regular') {
                $collectedMethodCode = $shippingMethod->getCarrierCode() . '_' . 'tig_postnl';
            }

            if (!in_array($collectedMethodCode, $collectedMethodCodes)) {
                continue;
            }
            $requestData = array_merge_recursive(
                $requestData,
                $this->prepareEmissionData($quote, $address, $collectedMethods[$collectedMethodCode])
            );
            $this->shippingMethodKeys[$key] = $collectedMethodCode;
        }
        $efficiency = [];
        try {
            foreach ($requestData as $endpoint => $requestDatum) {
                $data = $this->apiConnection->getEmission($requestDatum, $endpoint);
                foreach ($shippingMethods as $key => $shippingMethod) {
                    $collectedMethodCode = $shippingMethod->getCarrierCode() . '_' . $shippingMethod->getMethodCode();
                    $this->getExtensionAttributes($shippingMethod)
                        ->setMostEfficient(0);

                    if (!isset($this->shippingMethodKeys[$key])) {
                        continue;
                    }
                    if (!$data) {
                        continue;
                    }
                    $emissionData = $data;

                    if (!isset($emission[$collectedMethodCode])) {
                        $emission[$collectedMethodCode] = [
                            'emission'          => 0,
                            'meters_diesel'     => 0,
                            'meters_gasoline'   => 0,
                            'service_type'      => $endpoint
                        ];
                    }
                    $emission[$collectedMethodCode]['emission'] += $emissionData['emission'];
                    $emission[$collectedMethodCode]['meters_diesel'] += $emissionData['metersDiesel'];
                    $emission[$collectedMethodCode]['meters_gasoline'] += $emissionData['metersGasoline'];
                    $efficiency[$key] = [
                        'emission'          => $emission[$collectedMethodCode]['emission'],
                        'shipping_method'   => $shippingMethod
                    ];
                    unset($this->shippingMethodKeys[$key]);
                }
            }
            $this->session->setData(
                'thuiswinkel_bewustbezorgd_order_emission',
                $this->serializer->serialize($emission)
            );
            if ($mostEfficient = $this->getMostEfficient($efficiency)) {
                $this->getExtensionAttributes($mostEfficient['shipping_method'])
                    ->setMostEfficient(1);
            }
            // @codingStandardsIgnoreStart
        } catch (WrongApiConfigurationException $exception) {
            $this->logRepository->addApiLog($exception->getMessage());
        } catch (WrongApiCredentialsException $exception) {
            $this->logRepository->addApiLog($exception->getMessage());
        } catch (Throwable $exception) {
            $this->logRepository->addApiLog($exception->getMessage());
        }
        // @codingStandardsIgnoreEnd
        return $shippingMethods;
    }

    /**
     * Prepares request emission data from quote
     *
     * @param CartInterface $quote
     * @param $address
     * @param $serviceType
     * @return array
     * @throws LocalizedException
     */
    private function prepareEmissionData(CartInterface $quote, $address, $serviceType)
    {
        $data = [];
        $result = [];

        foreach ($quote->getItems() as $quoteItem) {
            $data = array_merge_recursive($data, $this->prepareItemData($quoteItem, $address, $serviceType));
        }

        foreach ($data as $endpoint => $items) {
            $result[$endpoint][] = $this->calculateFieldSumByKey(
                $data[$endpoint],
                self::CALCULATED_FIELD_KEYS[$endpoint]
            );
        }

        return $result;
    }

    /**
     * Calculates sum array field by key
     *
     * Input array should be like
     * [
     *     ['key1' => 'value1', 'key2' => 'value2', ... 'keyN' => 'valueN'],
     *     ['key1' => 'value1', 'key2' => 'value2', ... 'keyN' => 'valueN'],
     *      ...
     *     ['key1' => 'value1', 'key2' => 'value2', ... 'keyN' => 'valueN']
     * ]
     *
     * Example output array
     * ['key1' => 'value1', 'calculatedKey' => SUM('value'), ... 'keyN' => 'valueN']
     *
     * @param array $array
     * @param $calculatedKey
     * @return array
     */
    private function calculateFieldSumByKey(array $array, $calculatedKey)
    {
        if (!count($array)) {
            return $array;
        }
        $result = array_shift($array);

        while (count($array)) {
            $item = array_shift($array);
            $result[$calculatedKey] += $item[$calculatedKey];
        }

        return $result;
    }

    /**
     * Prepares request emission data from quote item
     *
     * @param CartItemInterface $quoteItem
     * @param $address
     * @param $serviceType
     * @return array
     * @throws LocalizedException
     */
    private function prepareItemData(CartItemInterface $quoteItem, $address, $serviceType)
    {
        $bewustbezorgdLegs = $this->getBewustbezorgdLegs($quoteItem);

        switch ($bewustbezorgdLegs) {
            case (BewustbezorgdLegsAttributeSource::TWO_LEGS_OPTION_ID):
                $weightOrVolume = $this->getQuoteItemWeight($quoteItem);
                break;
            default:
                $weightOrVolume = $this->getQuoteItemVolume($quoteItem);
                break;
        }
        $itemEndpoint = $this->bewustbezorgdLegsMap[$bewustbezorgdLegs];

        return [
            $itemEndpoint => [[
                self::CSV_HEADER[$itemEndpoint]['postcode_from']   => $this->storePostcode,
                self::CSV_HEADER[$itemEndpoint]['country_from']    => $this->storeCountryId,
                self::CSV_HEADER[$itemEndpoint]['postcode_to']     => $this->truncateString($address->getPostcode()),
                self::CSV_HEADER[$itemEndpoint]['country_to']      => $address->getCountryId(),
                self::CSV_HEADER[$itemEndpoint]['weight_mass']     => $weightOrVolume,
                self::CSV_HEADER[$itemEndpoint]['service_type']    => $serviceType
            ]]
        ];
    }

    /**
     * Retrieves "extension_attributes" from shipping method or creates it
     *
     * @param ShippingMethod $shippingMethod
     * @return ShippingMethodExtensionInterface
     */
    private function getExtensionAttributes(ShippingMethod $shippingMethod)
    {
        $extension = $shippingMethod->getExtensionAttributes();
        if (!$extension) {
            $extension = $this->extensionFactory->create();
            $shippingMethod->setExtensionAttributes($extension);
        }

        return $extension;
    }

    /**
     * Retrieves quote item "bewustbezorgd_legs" value
     *
     * @param CartItemInterface $quoteItem
     * @return string|null
     * @throws LocalizedException
     */
    private function getBewustbezorgdLegs(CartItemInterface $quoteItem)
    {
        return $quoteItem->getProduct()->getBewustbezorgdLegs() ?: $this->getDefaultBewustbezorgdLegs();
    }

    /**
     * Retrieves default value of attribute "bewustbezorgd_legs"
     *
     * @return string|null
     * @throws LocalizedException
     */
    private function getDefaultBewustbezorgdLegs()
    {
        try {
            /** @var Attribute $attribute */
            $attribute = $this->eavConfig->getAttribute(
                Product::ENTITY,
                self::ATTRIBUTE_CODE_BEWUSTBEZORGD_LEGS
            );
        } catch (LocalizedException $exception) {
            $this->logRepository->addDataLog($exception->getMessage(), 'Get Default Bewustbezorgd Legs');
            return null;
        }

        return $attribute->getDefaultValue();
    }

    /**
     * Retrieves weight of quote item for all qty
     *
     * @param CartItemInterface $quoteItem
     * @return float|int
     */
    private function getQuoteItemWeight(CartItemInterface $quoteItem)
    {
        $itemWeight = $quoteItem->getProduct()->getWeight() ?: $this->configModel->getDefaultWeight();

        return $this->convertWeightToGrams(
            $itemWeight * $quoteItem->getQty(),
            $this->directoryHelper->getWeightUnit()
        );
    }

    /**
     * Retrieves quote item volume for all qty
     *
     * @param CartItemInterface $quoteItem
     * @return float|int
     */
    private function getQuoteItemVolume(CartItemInterface $quoteItem)
    {
        $dimensionAttributes = $this->configModel->getConfigDimensionsAttributes();
        $product = $quoteItem->getProduct();
        if (($length = $product->getData($dimensionAttributes['attribute_length']))
            && ($width = $product->getData($dimensionAttributes['attribute_width']))
            && ($height = $product->getData($dimensionAttributes['attribute_height']))
        ) {
            return $this->convertVolumeToLiters(
                $length * $width * $height * $quoteItem->getQty(),
                $this->configModel->getDimensionsUnit()
            );
        }

        return $this->configModel->getDefaultVolume() * $quoteItem->getQty();
    }

    /**
     * Retrieves emission from efficiency item
     *
     * @param array $efficiencyItem
     * @return float|int
     */
    private function getEfficiencyEmission(array $efficiencyItem)
    {
        return $efficiencyItem['emission'];
    }

    /**
     * Retrieves first item from efficiency
     *
     * @param array $efficiency
     * @return array|bool
     */
    private function getMostEfficient(array $efficiency)
    {
        usort($efficiency, function ($a, $b) {
            return $this->getEfficiencyEmission($a) <=> $this->getEfficiencyEmission($b);
        });
        $mostEfficient = reset($efficiency);

        if ($mostEfficient['emission']) {
            return $mostEfficient;
        }

        return false;
    }

    /**
     * Truncate string
     *
     * @param string $value
     * @param int $length
     * @param string $etc
     * @param string &$remainder
     * @param bool $breakWords
     * @return string
     */
    private function truncateString($value, $length = 4, $etc = '', &$remainder = '', $breakWords = true)
    {
        if (!$value) {
            $value = "";
        }
        $value = trim($value);
        $this->truncateResult = $this->filterManager->truncateFilter(
            $value,
            ['length' => $length, 'etc' => $etc, 'breakWords' => $breakWords]
        );

        return $this->truncateResult->getValue();
    }

    /**
     * Retrieves converted value from the requested unit to kilograms
     *
     * @param $value
     * @param $unit
     * @return float|int
     */
    private function convertWeightToGrams($value, $unit)
    {
        return $value * self::WEIGHT_CONVERT_MAP[$unit];
    }

    /**
     * Retrieves converted value from the requested unit to liters
     *
     * @param $value
     * @param $unit
     * @return float|int
     */
    private function convertVolumeToLiters($value, $unit)
    {
        return $value * self::VOLUME_CONVERT_MAP[$unit];
    }
}
