<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model\Emission;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Cart\ShippingMethod;
use Magento\Framework\Exception\FileSystemException;
use Thuiswinkel\BewustBezorgd\Model\Exception\WrongApiConfigurationException;

/**
 * Interface CollectorInterface
 */
interface CollectorInterface
{
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
    public function collect(CartInterface $quote, $address, $shippingMethods);
}
