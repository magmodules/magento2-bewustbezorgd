<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Plugin\Magento\Quote\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Cart\ShippingMethod;
use Magento\Quote\Model\ShippingMethodManagement as OriginClass;
use Thuiswinkel\BewustBezorgd\Api\Config\RepositoryInterface as ConfigModel;
use Thuiswinkel\BewustBezorgd\Model\Exception\WrongApiConfigurationException;
use Thuiswinkel\BewustBezorgd\Service\CollectEmission;

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

    /** @var CollectEmission */
    protected $emissionCollector;

    /**
     * Constructor
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param ConfigModel $configModel
     * @param AddressRepositoryInterface $addressRepository
     * @param CollectEmission $emissionCollector
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ConfigModel $configModel,
        AddressRepositoryInterface $addressRepository,
        CollectEmission $emissionCollector
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
    public function afterEstimateByExtendedAddress(OriginClass $subject, $result, $cartId, $address): array
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
    public function afterEstimateByAddressId(OriginClass $subject, $result, $cartId, $addressId): array
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
    public function afterEstimateProcess(array $result, CartInterface $quote, $address): array
    {
        // Check if country in address is allowed

        $this->emissionCollector->execute($quote, $address, $result);

        return $result;
    }
}
