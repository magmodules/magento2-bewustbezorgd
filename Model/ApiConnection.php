<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model;

use Throwable;
use Zend_Date;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Filesystem;
use Thuiswinkel\BewustBezorgd\Model\Config as ConfigModel;
use Thuiswinkel\BewustBezorgd\Model\Exception\WrongApiConfigurationException;
use Thuiswinkel\BewustBezorgd\Model\Exception\WrongApiCredentialsException;
use Thuiswinkel\BewustBezorgd\Model\Exception\ApiAuthenticationFailedException;
use Thuiswinkel\BewustBezorgd\HTTP\Client\Curl;
use Thuiswinkel\BewustBezorgd\Helper\Data as DataHelper;

/**
 * Class ApiConnection
 */
class ApiConnection
{
    /**
     * Api headers.
     *
     * @var array
     */
    private $headers = [
        'Accept' => 'application/json'
    ];

    /**
     * Api available endpoints
     *
     * @var array
     */
    private $availablePostEndpoints = [
        '/api/Account/Token',
        '/api/Account/Refresh',
        '/api/bulk-emission-calculation/two-legs',
        '/api/bulk-emission-calculation/three-legs'
    ];

    /** @var ClientInterface|Curl */
    protected $curlClient;

    /** @var SessionManagerInterface */
    protected $session;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var ConfigModel */
    protected $configModel;

    /** @var TimezoneInterface */
    private $timezone;

    /** @var string|null */
    private $bearerToken = null;

    /** @var string|null */
    private $bearerTokenExpiry = null;

    /** @var Filesystem */
    protected $filesystem;

    protected $helper;

    /** @var string|null */
    protected $apiShopId = null;

    /** @var string|null */
    protected $apiPassword = null;

    /**
     * Constructor.
     *
     * @param Curl $curlClient
     * @param SessionManagerInterface $session
     * @param SerializerInterface $serializer
     * @param ConfigModel $configModel
     * @param TimezoneInterface $timezone
     * @param Filesystem $filesystem
     * @param DataHelper $helper
     */
    public function __construct(
        Curl $curlClient,
        SessionManagerInterface $session,
        SerializerInterface $serializer,
        ConfigModel $configModel,
        TimezoneInterface $timezone,
        Filesystem $filesystem,
        DataHelper $helper
    ) {
        $this->curlClient = $curlClient;
        $this->session = $session;
        $this->serializer = $serializer;
        $this->configModel = $configModel;
        $this->timezone = $timezone;
        $this->filesystem = $filesystem;
        $this->helper = $helper;
    }

    /**
     * Retrieves Api endpoint
     *
     * @param string $path
     * @return string
     * @throws WrongApiConfigurationException
     */
    protected function getApiEndpoint(string $path)
    {
        if (!in_array($path, $this->availablePostEndpoints)) {
            throw new WrongApiConfigurationException();
        }

        return $this->configModel->getConfigGatewayUrl() . $path;
    }

    /**
     * Authenticates to the service by API
     *
     * @return bool|string
     * @throws Throwable
     * @throws WrongApiConfigurationException
     * @throws WrongApiCredentialsException
     */
    public function auth()
    {
        try {
            $authEndpoint = $this->getApiEndpoint('/api/Account/Token');
            $this->curlClient->setHeaders($this->headers);
            $this->curlClient->addHeader('Content-Type', 'application/json');
            $this->curlClient->post(
                $authEndpoint,
                $this->serializer->serialize([
                    "id"        => $this->getApiShopId(),
                    "password"  => $this->getApiPassword()
                ])
            );
            $response = $this->serializer->unserialize($this->curlClient->getBody());

            if (count($response['errors'])) {
                if ($response['errors'][0]['code'] == 'C0006') {
                    throw new WrongApiCredentialsException();
                }
                throw new ApiAuthenticationFailedException();
            }
            $accessToken = $response['accessToken'];
            $accessTokenExpiry = $response['expireDateTimeAccesToken'];
            $this->session->setData('thuiswinkel_bewustbezorgd_bearer_token', $accessToken);
            $this->bearerToken = $accessToken;
            $this->session->setData('thuiswinkel_bewustbezorgd_bearer_token_expiry', $accessTokenExpiry);
            $this->bearerTokenExpiry = $accessTokenExpiry;

            return $this->getBearerToken();
        // @codingStandardsIgnoreStart
        } catch (WrongApiConfigurationException $exception) {
            $this->helper->log($exception);
            throw $exception;
        } catch (WrongApiCredentialsException $exception) {
            $this->helper->log($exception);
            throw $exception;
        } catch (ApiAuthenticationFailedException $exception) {
            $this->helper->log($exception);
        } catch (Throwable $exception) {
            $this->helper->log($exception);
            throw $exception;
        }
        // @codingStandardsIgnoreEnd

        return false;
    }

    /**
     * Sends request to Api and retrieves responded data
     *
     * @param $filePath
     * @param string $endpoint
     * @return string
     * @throws Throwable
     * @throws WrongApiConfigurationException
     * @throws WrongApiCredentialsException
     */
    public function getBulkEmission($filePath, $endpoint = 'three-legs')
    {
        if (!($bearerToken = $this->getBearerToken()) || $this->isBearerTokenExpired()) {
            $bearerToken = $this->auth();
        }
        $bulkEndpoint = $this->getApiEndpoint('/api/bulk-emission-calculation/' . $endpoint);
        $uploadedFile = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)
            ->getAbsolutePath($filePath);
        $postData = [
            'uploadedFile'  => $uploadedFile
        ];
        $this->curlClient->setHeaders($this->headers);
        $this->curlClient->addHeader('Authorization', 'Bearer ' . $bearerToken);
        $this->curlClient->addHeader('Content-Type', 'multipart/form-data');
        $this->curlClient->post($bulkEndpoint, $postData);
        $this->helper->log($this->curlClient->getBody(), 'ResponseData');

        return $this->curlClient->getBody();
    }

    /**
     * Retrieves Bearer token from session
     *
     * @return string|null
     */
    private function getBearerToken()
    {
        if (null === $this->bearerToken) {
            $this->bearerToken = $this->session->getData('thuiswinkel_bewustbezorgd_bearer_token');
        }

        return $this->bearerToken;
    }

    /**
     * Retrieves Bearer token expiry from session
     *
     * @return string|null
     */
    private function getBearerTokenExpiry()
    {
        if (null === $this->bearerTokenExpiry) {
            $this->bearerTokenExpiry = $this->session->getData('thuiswinkel_bewustbezorgd_bearer_token_expiry');
        }

        return $this->bearerTokenExpiry;
    }

    /**
     * Retrieves "true" if access token is expired
     *
     * @return bool
     */
    protected function isBearerTokenExpired()
    {
        $bearerTokenExpiry = $this->getBearerTokenExpiry();
        $now = $this->timezone->date();

        return (bool) $now->diff($this->timezone->date($bearerTokenExpiry, Zend_Date::ISO_8601))
            ->invert;
    }

    /**
     * Gets API-ShopID
     *
     * @return string|null
     */
    public function getApiShopId()
    {
        if ($this->apiShopId === null) {
            $this->apiShopId = (string)$this->configModel->getConfigApiShopId();
        }

        return $this->apiShopId;
    }

    /**
     * Sets API-ShopID
     *
     * @param string $apiShopId
     * @return $this
     */
    public function setApiShopId($apiShopId)
    {
        $this->apiShopId = $apiShopId;

        return $this;
    }

    /**
     * Gets API-Password
     *
     * @return string|null
     */
    public function getApiPassword()
    {
        if ($this->apiPassword === null) {
            $this->apiPassword = (string)$this->configModel->getConfigApiPassword();
        }

        return $this->apiPassword;
    }

    /**
     * Sets API-Password
     *
     * @param string $apiPassword
     * @return $this
     */
    public function setApiPassword($apiPassword)
    {
        $this->apiPassword = $apiPassword;

        return $this;
    }
}
