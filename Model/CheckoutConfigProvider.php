<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;

class CheckoutConfigProvider implements ConfigProviderInterface
{
    /** @var AssetRepository */
    protected $assetRepo;

    /** @var Config */
    protected $modelConfig;

    /**
     * Constructor
     *
     * @param AssetRepository $assetRepo
     * @param Config $modelConfig
     */
    public function __construct(AssetRepository $assetRepo, Config $modelConfig)
    {
        $this->assetRepo = $assetRepo;
        $this->modelConfig = $modelConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [
            'thuiswinkelBewustBezorgd' => [
                'shippingMethods' => [
                    'emission_logo_url_svg' => $this->assetRepo
                        ->getUrl('Thuiswinkel_BewustBezorgd::images/logo.svg'),
                    'emission_logo_url_png' => $this->assetRepo
                        ->getUrl('Thuiswinkel_BewustBezorgd::images/logo.png'),
                    'can_show_logo' => $this->modelConfig->canShowLogo()
                ]
            ]
        ];

        return $config;
    }
}
