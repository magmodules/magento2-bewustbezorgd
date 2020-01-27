<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Plugin\Magento\Checkout\Model;

use Magento\Checkout\Model\GuestPaymentInformationManagement as OriginClass;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Checkout\Model\Session;
use Thuiswinkel\BewustBezorgd\Model\OrderEmissionFactory;
use Thuiswinkel\BewustBezorgd\Model\OrderEmissionRepository;
use Thuiswinkel\BewustBezorgd\Model\Config as ConfigModel;
use Thuiswinkel\BewustBezorgd\Helper\Data as DataHelper;

/**
 * Plugin for Magento\Quote\Model\ShippingMethodManagement
 */
class GuestPaymentInformationManagement
{
    /** @var ConfigModel  */
    protected $configModel;

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var OrderEmissionFactory */
    protected $orderEmissionFactory;

    /** @var OrderEmissionRepository */
    protected $orderEmissionRepository;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var Session */
    protected $session;

    /** @var DataHelper */
    private $dataHelper;

    /**
     * Constructor
     *
     * PaymentInformationManagement constructor.
     * @param ConfigModel $configModel
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderEmissionFactory $orderEmissionFactory
     * @param OrderEmissionRepository $orderEmissionRepository
     * @param SerializerInterface $serializer
     * @param Session $session
     * @param DataHelper $dataHelper
     */
    public function __construct(
        ConfigModel $configModel,
        OrderRepositoryInterface $orderRepository,
        OrderEmissionFactory $orderEmissionFactory,
        OrderEmissionRepository $orderEmissionRepository,
        SerializerInterface $serializer,
        Session $session,
        DataHelper $dataHelper
    ) {
        $this->configModel = $configModel;
        $this->orderRepository = $orderRepository;
        $this->orderEmissionFactory = $orderEmissionFactory;
        $this->orderEmissionRepository = $orderEmissionRepository;
        $this->serializer = $serializer;
        $this->session = $session;
        $this->dataHelper = $dataHelper;
    }

    /**
     * After plugin for method
     * Magento\Checkout\Model\GuestPaymentInformationManagement::savePaymentInformationAndPlaceOrder
     *
     * @param OriginClass $subject
     * @param $result
     * @param string $cartId
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return mixed
     * @throws LocalizedException
     */
    public function afterSavePaymentInformationAndPlaceOrder(
        OriginClass $subject,
        $result,
        $cartId,
        $email,
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
        $emissionSessionData = $this->serializer->unserialize($emissionSessionData);
        $this->dataHelper->log($emissionSessionData, 'SessionData for Guest');

        if (!isset($emissionSessionData[$shippingMethod])) {
            return $result;
        }
        $orderEmission = $this->orderEmissionFactory->create();
        $orderEmission->addData([
            'order_id'          => $result,
            'service_type'      => $emissionSessionData[$shippingMethod]['service_type'],
            'emission'          => $emissionSessionData[$shippingMethod]['emission'],
            'meters_diesel'     => $emissionSessionData[$shippingMethod]['meters_diesel'],
            'meters_gasoline'   => $emissionSessionData[$shippingMethod]['meters_gasoline']
        ]);
        $this->orderEmissionRepository->save($orderEmission->getDataModel());

        return $result;
    }
}
