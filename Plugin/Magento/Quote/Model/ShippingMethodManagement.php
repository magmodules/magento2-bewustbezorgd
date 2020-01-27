<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Plugin\Magento\Quote\Model;

use Magento\Quote\Model\Cart\ShippingMethod;
use Magento\Quote\Model\ShippingMethodManagement as OriginClass;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\FileSystemException;
use Thuiswinkel\BewustBezorgd\Model\Emission\CollectorInterface;
use Thuiswinkel\BewustBezorgd\Model\Config as ConfigModel;
use Thuiswinkel\BewustBezorgd\Model\Exception\WrongApiConfigurationException;

/**
 * Plugin for Magento\Quote\Model\ShippingMethodManagement
 */
class ShippingMethodManagement
{
    /** @var CartRepositoryInterface */
    protected $quoteRepository;

    /** @var ConfigModel  */
    protected $configModel;

    /**
     * Customer Address repository
     *
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /** @var CollectorInterface */
    protected $emissionCollector;

    /**
     * Constructor
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param ConfigModel $configModel
     * @param AddressRepositoryInterface $addressRepository
     * @param CollectorInterface $emissionCollector
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ConfigModel $configModel,
        AddressRepositoryInterface $addressRepository,
        CollectorInterface $emissionCollector
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->configModel = $configModel;
        $this->addressRepository = $addressRepository;
        $this->emissionCollector = $emissionCollector;
    }

    /**
     * After plugin for method Magento\Quote\Model\ShippingMethodManagement::estimateByExtendedAddress
     *
     * @param OriginClass $subject
     * @param array $result
     * @param $cartId
     * @param $address
     * @return ShippingMethod[]
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function afterEstimateByExtendedAddress(OriginClass $subject, $result, $cartId, $address)
    {
        // no change result if module is disabled
        if (!$this->configModel->isEnabled()) {
            return $result;
        }
        $quote = $this->quoteRepository->getActive($cartId);

        // no change result for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return $result;
        }

        return $this->afterEstimateProcess($result, $quote, $address);
    }

    /**
     * After plugin for method Magento\Quote\Model\ShippingMethodManagement::estimateByAddressId
     *
     * @param OriginClass $subject
     * @param array $result
     * @param $cartId
     * @param $addressId
     * @return ShippingMethod[]|array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function afterEstimateByAddressId(OriginClass $subject, $result, $cartId, $addressId)
    {
        $quote = $this->quoteRepository->getActive($cartId);

        // no change result for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return $result;
        }
        $address = $this->addressRepository->getById($addressId);

        return $this->afterEstimateProcess($result, $quote, $address);
    }

    /**
     * Process method for plugin methods
     *
     * @param array $result
     * @param CartInterface $quote
     * @param $address
     * @return ShippingMethod[]|array
     * @throws NoSuchEntityException
     * @throws FileSystemException
     * @throws WrongApiConfigurationException
     */
    public function afterEstimateProcess(array $result, CartInterface $quote, $address)
    {
        // Check if country in address is allowed
        if (!in_array($address->getCountryId(), explode(',', $this->configModel->getAllowedCountries()))) {
            return $result;
        }
        $this->emissionCollector->collect($quote, $address, $result);

        return $result;
    }
}
