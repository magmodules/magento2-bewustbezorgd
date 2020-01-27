<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Plugin\Magento\Quote\Model\GuestCart;

use Magento\Quote\Model\Cart\ShippingMethod;
use Magento\Quote\Model\GuestCart\GuestShippingMethodManagement as OriginClass;
use Magento\Quote\Model\GuestCart\GuestCartRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\FileSystemException;
use Thuiswinkel\BewustBezorgd\Model\Exception\WrongApiConfigurationException;
use Thuiswinkel\BewustBezorgd\Model\Config as ConfigModel;
use Thuiswinkel\BewustBezorgd\Plugin\Magento\Quote\Model\ShippingMethodManagement as ShippingMethodManagementPlugin;

/**
 * Plugin for Magento\Quote\Model\GuestCart\GuestShippingMethodManagement
 */
class GuestShippingMethodManagement
{
    /** @var ConfigModel  */
    protected $configModel;

    /** @var ShippingMethodManagementPlugin */
    protected $shippingMethodManagementPlugin;

    /**
     * @var GuestCartRepository
     */
    private $guestCartRepository;

    /**
     * Constructor
     *
     * @param ConfigModel $configModel
     * @param ShippingMethodManagementPlugin $shippingMethodManagementPlugin
     * @param GuestCartRepository $guestCartRepository
     */
    public function __construct(
        ConfigModel $configModel,
        ShippingMethodManagementPlugin $shippingMethodManagementPlugin,
        GuestCartRepository $guestCartRepository
    ) {
        $this->configModel = $configModel;
        $this->shippingMethodManagementPlugin = $shippingMethodManagementPlugin;
        $this->guestCartRepository = $guestCartRepository;
    }

    /**
     * After plugin for method Magento\Quote\Model\GuestCart\GuestShippingMethodManagement::estimateByExtendedAddress
     *
     * @param OriginClass $subject
     * @param array $result
     * @param $cartId
     * @param $address
     * @return ShippingMethod[]
     * @throws NoSuchEntityException
     * @throws FileSystemException
     * @throws WrongApiConfigurationException
     */
    public function afterEstimateByExtendedAddress(OriginClass $subject, $result, $cartId, $address)
    {
        // no change result if module is disabled
        if (!$this->configModel->isEnabled()) {
            return $result;
        }
        $quote = $this->guestCartRepository->get($cartId);

        return $this->shippingMethodManagementPlugin->afterEstimateProcess($result, $quote, $address);
    }
}
