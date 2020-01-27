<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model\Emission;

use RuntimeException;
use Throwable;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\ShippingMethodExtensionFactory;
use Magento\Quote\Model\Cart\ShippingMethod;
use Magento\Quote\Api\Data\ShippingMethodExtensionInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Filter\TruncateFilter\Result;
use Thuiswinkel\BewustBezorgd\Helper\Data as DataHelper;
use Thuiswinkel\BewustBezorgd\Model\Config as ConfigModel;
use Thuiswinkel\BewustBezorgd\Model\Exception\ApiAuthenticationFailedException;
use Thuiswinkel\BewustBezorgd\Model\Exception\WrongApiConfigurationException;
use Thuiswinkel\BewustBezorgd\Model\Exception\WrongApiCredentialsException;
use Thuiswinkel\BewustBezorgd\Model\Product\Attribute\Source\BewustbezorgdLegs as BewustbezorgdLegsAttributeSource;
use Thuiswinkel\BewustBezorgd\Model\ApiConnection;
use Thuiswinkel\BewustBezorgd\Model\Converter;
use Thuiswinkel\BewustBezorgd\Model\ArrayCalculatorInterface;

/**
 * Class Collector
 */
class Collector implements CollectorInterface
{
    /**#@+
     * Constants
     */
    const CALCULATED_FIELD_KEYS = [
        'two-legs'      => 'Weight',
        'three-legs'    => 'Mass'
    ];
    /**#@-*/

    /**
     * Headers for CSV files
     *
     * @var array
     */
    protected $csvHeader = [
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

    /** @var DataHelper */
    private $dataHelper;

    /** @var ConfigModel */
    private $configModel;

    /** @var DirectoryHelper */
    protected $directoryHelper;

    /** @var Filesystem */
    private $filesystem;

    /** @var ApiConnection */
    private $apiConnection;

    /** @var Converter */
    protected $converter;

    /** @var ShippingMethodExtensionFactory */
    protected $extensionFactory;

    /** @var Session */
    protected $session;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var ArrayCalculatorInterface */
    protected $arrayCalculator;

    /**
     * Store Information Country ID
     *
     * @var string
     */
    protected $storeCountryId;

    /**
     * Store Information Postcode
     *
     * @var string
     */
    protected $storePostcode;

    /**
     * Filter manager
     *
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * Option map for attribute "bewustbezorgdLegs"
     *
     * @var array
     */
    protected $bewustbezorgdLegsMap;

    protected $shippingMethodKeys = [];

    /**
     * Constructor.
     *
     * @param DataHelper $dataHelper
     * @param ConfigModel $configModel
     * @param DirectoryHelper $directoryHelper
     * @param Filesystem $filesystem
     * @param ApiConnection $apiConnection
     * @param Converter $converter
     * @param ShippingMethodExtensionFactory $extensionFactory
     * @param Session $session
     * @param SerializerInterface $serializer
     * @param ArrayCalculatorInterface $arrayCalculator
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param FilterManager $filterManager
     * @param BewustbezorgdLegsAttributeSource $bewustbezorgdLegsSource
     * @throws NoSuchEntityException
     */
    public function __construct(
        DataHelper $dataHelper,
        ConfigModel $configModel,
        DirectoryHelper $directoryHelper,
        Filesystem $filesystem,
        ApiConnection $apiConnection,
        Converter $converter,
        ShippingMethodExtensionFactory $extensionFactory,
        Session $session,
        SerializerInterface $serializer,
        ArrayCalculatorInterface $arrayCalculator,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        FilterManager $filterManager,
        BewustbezorgdLegsAttributeSource $bewustbezorgdLegsSource
    ) {
        $this->dataHelper = $dataHelper;
        $this->configModel = $configModel;
        $this->directoryHelper = $directoryHelper;
        $this->filesystem = $filesystem;
        $this->apiConnection = $apiConnection;
        $this->converter = $converter;
        $this->extensionFactory = $extensionFactory;
        $this->session = $session;
        $this->serializer = $serializer;
        $this->arrayCalculator = $arrayCalculator;
        $this->filterManager = $filterManager;
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
     * {@inheritdoc}
     */
    public function collect(CartInterface $quote, $address, $shippingMethods)
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
            foreach ($requestData as $filename => $requestDatum) {
                $file = $this->prepareCsv($filename, $suffix, $requestDatum);
                $data = $this->parseCsv($this->apiConnection->getBulkEmission($file, $filename));

                foreach ($shippingMethods as $key => $shippingMethod) {
                    $collectedMethodCode = $shippingMethod->getCarrierCode() . '_' . $shippingMethod->getMethodCode();
                    $this->getExtensionAttributes($shippingMethod)
                        ->setMostEfficient(0);
                    ;

                    if (!isset($this->shippingMethodKeys[$key])) {
                        continue;
                    }
                    $emissionData = array_shift($data);

                    if (null === $emissionData['Service Type']) {
                        continue;
                    }

                    if (!isset($emission[$collectedMethodCode])) {
                        $emission[$collectedMethodCode] = [
                            'emission'          => 0,
                            'meters_diesel'     => 0,
                            'meters_gasoline'   => 0,
                            'service_type'      => $emissionData['Service Type']
                        ];
                    }
                    $emission[$collectedMethodCode]['emission'] += $emissionData['Emission'];
                    $emission[$collectedMethodCode]['meters_diesel'] += $emissionData['Meters Diesel'];
                    $emission[$collectedMethodCode]['meters_gasoline'] += $emissionData['Meters Gasoline'];
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
        } catch (WrongApiCredentialsException $exception) {
        } catch (ApiAuthenticationFailedException $exception) {
        } catch (Throwable $exception) {
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
    protected function prepareEmissionData(CartInterface $quote, $address, $serviceType)
    {
        $data = [];
        $result = [];

        foreach ($quote->getItems() as $quoteItem) {
            $data = array_merge_recursive($data, $this->prepareItemData($quoteItem, $address, $serviceType));
        }

        foreach ($data as $endpoint => $items) {
            $result[$endpoint][] = $this->arrayCalculator->calculateFieldSumByKey(
                $data[$endpoint],
                self::CALCULATED_FIELD_KEYS[$endpoint]
            );
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
    protected function prepareItemData(CartItemInterface $quoteItem, $address, $serviceType)
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
                $this->csvHeader[$itemEndpoint]['postcode_from']   => $this->storePostcode,
                $this->csvHeader[$itemEndpoint]['country_from']    => $this->storeCountryId,
                $this->csvHeader[$itemEndpoint]['postcode_to']     => $this->truncateString($address->getPostcode()),
                $this->csvHeader[$itemEndpoint]['country_to']      => $address->getCountryId(),
                $this->csvHeader[$itemEndpoint]['weight_mass']     => $weightOrVolume,
                $this->csvHeader[$itemEndpoint]['service_type']    => $serviceType
            ]]
        ];
    }

    /**
     * Prepares CSV file to send to Api
     *
     * @param $filename
     * @param $suffix
     * @param $requestData
     * @return string
     * @throws FileSystemException
     */
    protected function prepareCsv($filename, $suffix, $requestData)
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $tmpDir = $directory->getAbsolutePath('order-emission');

        if (!$directory->create($tmpDir)) {
            throw new RuntimeException('Failed to create temporary directory');
        }
        $file = $tmpDir . '/' . $filename . '-' . $suffix . '.csv';
        $stream = $directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->csvHeader[$filename], ';');

        foreach ($requestData as $requestDatum) {
            $stream->writeCsv($requestDatum, ';');
        }
        $stream->unlock();
        $stream->close();

        return $file;
    }

    /**
     * CSV parser
     *
     * @param $csvData
     * @return array
     */
    protected function parseCsv($csvData)
    {
        $csvStrings = explode(PHP_EOL, str_replace("\r\n", "\n", $csvData));
        $csvStrings = array_filter($csvStrings, function ($csvString) {
            return !empty($csvString);
        });
        $csvKeys = explode(';', array_shift($csvStrings));
        $data = [];

        while (count($csvStrings)) {
            $csvData = [];
            $csvStringAsArray = explode(';', array_shift($csvStrings));

            for ($i = 0, $colsCount = count($csvStringAsArray); $i < $colsCount; $i++) {
                $csvData[$csvKeys[$i]] = $csvStringAsArray[$i];
            }
            $data[] = $csvData;
        }

        return $data;
    }

    /**
     * Retrieves "extension_attributes" from shipping method or creates it
     *
     * @param ShippingMethod $shippingMethod
     * @return ShippingMethodExtensionInterface
     */
    protected function getExtensionAttributes(ShippingMethod $shippingMethod)
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
    protected function getBewustbezorgdLegs(CartItemInterface $quoteItem)
    {
        return $quoteItem->getProduct()->getBewustbezorgdLegs() ?: $this->dataHelper->getDefaultBewustbezorgdLegs();
    }

    /**
     * Retrieves weight of quote item for all qty
     *
     * @param CartItemInterface $quoteItem
     * @return float|int
     */
    protected function getQuoteItemWeight(CartItemInterface $quoteItem)
    {
        $itemWeight = $quoteItem->getProduct()->getWeight() ?: $this->configModel->getDefaultWeight();

        return $this->converter->convertWeightToGrams(
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
    protected function getQuoteItemVolume(CartItemInterface $quoteItem)
    {
        $dimensionAttributes = $this->configModel->getConfigDimensionsAttributes();
        if (($length = $quoteItem->getProduct()->getData($dimensionAttributes['attribute_length']))
            && ($width = $quoteItem->getProduct()->getData($dimensionAttributes['attribute_width']))
            && ($height = $quoteItem->getProduct()->getData($dimensionAttributes['attribute_height']))
        ) {
            return $this->converter->convertVolumeToLiters(
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
    protected function getEfficiencyEmission(array $efficiencyItem)
    {
        return $efficiencyItem['emission'];
    }

    /**
     * Retrieves first item from efficiency
     *
     * @param array $efficiency
     * @return array|bool
     */
    protected function getMostEfficient(array $efficiency)
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
    protected function truncateString($value, $length = 4, $etc = '', &$remainder = '', $breakWords = true)
    {
        $value = trim($value);
        $this->truncateResult = $this->filterManager->truncateFilter(
            $value,
            ['length' => $length, 'etc' => $etc, 'breakWords' => $breakWords]
        );

        return $this->truncateResult->getValue();
    }
}
