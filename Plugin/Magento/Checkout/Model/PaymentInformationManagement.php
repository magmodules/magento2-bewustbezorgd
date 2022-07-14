<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Plugin\Magento\Checkout\Model;

use Magento\Checkout\Model\PaymentInformationManagement as OriginClass;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Thuiswinkel\BewustBezorgd\Api\Config\RepositoryInterface as ConfigModel;
use Thuiswinkel\BewustBezorgd\Api\Log\RepositoryInterface as LogRepository;
use Thuiswinkel\BewustBezorgd\Api\OrderEmission\RepositoryInterface as OrderEmissionRepository;

/**
 * Plugin for Magento\Quote\Model\ShippingMethodManagement
 */
class PaymentInformationManagement
{
    /** @var ConfigModel  */
    protected $configModel;

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var OrderEmissionRepository */
    protected $orderEmissionRepository;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var Session */
    protected $session;

    /** @var LogRepository */
    private $logRepository;

    /**
     * Constructor
     *
     * PaymentInformationManagement constructor.
     * @param ConfigModel $configModel
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderEmissionRepository $orderEmissionRepository
     * @param SerializerInterface $serializer
     * @param Session $session
     * @param LogRepository $logRepository
     */
    public function __construct(
        ConfigModel $configModel,
        OrderRepositoryInterface $orderRepository,
        OrderEmissionRepository $orderEmissionRepository,
        SerializerInterface $serializer,
        Session $session,
        LogRepository $logRepository
    ) {
        $this->configModel = $configModel;
        $this->orderRepository = $orderRepository;
        $this->orderEmissionRepository = $orderEmissionRepository;
        $this->serializer = $serializer;
        $this->session = $session;
        $this->logRepository = $logRepository;
    }

    /**
     * After plugin for method Magento\Checkout\Model\PaymentInformationManagement::savePaymentInformationAndPlaceOrder
     *
     * @param OriginClass $subject
     * @param $result
     * @param $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return mixed
     * @throws LocalizedException
     */
    public function afterSavePaymentInformationAndPlaceOrder(
        OriginClass $subject,
        $result,
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        if (!$this->configModel->saveToOrder()) {
            return $result;
        }

        if (!($emissionSessionData = $this->session->getData('thuiswinkel_bewustbezorgd_order_emission', true))) {
            return $result;
        }
        $order = $this->orderRepository->get($result);
        $shippingMethod = $order->getShippingMethod();
        $this->logRepository->addDataLog($emissionSessionData, 'SessionData for registered customer');
        $emissionSessionData = $this->serializer->unserialize($emissionSessionData);

        if (!isset($emissionSessionData[$shippingMethod])) {
            return $result;
        }
        $orderEmission = $this->orderEmissionRepository->create();
        $orderEmission->addData([
            'order_id'          => $result,
            'service_type'      => $emissionSessionData[$shippingMethod]['service_type'],
            'emission'          => $emissionSessionData[$shippingMethod]['emission'],
            'meters_diesel'     => $emissionSessionData[$shippingMethod]['meters_diesel'],
            'meters_gasoline'   => $emissionSessionData[$shippingMethod]['meters_gasoline']
        ]);
        $this->orderEmissionRepository->save($orderEmission);

        return $result;
    }
}
