<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Service;

use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Throwable;
use Thuiswinkel\BewustBezorgd\Api\Log\RepositoryInterface as LogRepository;
use Magento\Framework\HTTP\Client\Curl;
use Thuiswinkel\BewustBezorgd\Api\Config\RepositoryInterface as ConfigModel;
use Thuiswinkel\BewustBezorgd\Model\Exception\ApiAuthenticationFailedException;
use Thuiswinkel\BewustBezorgd\Model\Exception\WrongApiConfigurationException;
use Thuiswinkel\BewustBezorgd\Model\Exception\WrongApiCredentialsException;
use Zend_Date;

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
        '/api/emission-calculation/two-legs',
        '/api/emission-calculation/three-legs'
    ];

    /** @var ClientInterface|Curl */
    private $curlClient;

    /** @var SessionManagerInterface */
    private $session;

    /** @var SerializerInterface */
    private $serializer;

    /** @var ConfigModel */
    private $configModel;

    /** @var TimezoneInterface */
    private $timezone;

    /** @var string|null */
    private $bearerToken = null;

    /** @var string|null */
    private $bearerTokenExpiry = null;

    /**
     * @var LogRepository
     */
    private $logRepository;

    /** @var string|null */
    private $apiShopId = null;

    /** @var string|null */
    private $apiPassword = null;

    /**
     * Constructor.
     *
     * @param Curl $curlClient
     * @param SessionManagerInterface $session
     * @param SerializerInterface $serializer
     * @param ConfigModel $configModel
     * @param TimezoneInterface $timezone
     * @param LogRepository $logRepository
     */
    public function __construct(
        Curl $curlClient,
        SessionManagerInterface $session,
        SerializerInterface $serializer,
        ConfigModel $configModel,
        TimezoneInterface $timezone,
        LogRepository $logRepository
    ) {
        $this->curlClient = $curlClient;
        $this->session = $session;
        $this->serializer = $serializer;
        $this->configModel = $configModel;
        $this->timezone = $timezone;
        $this->logRepository = $logRepository;
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
        } catch (WrongApiConfigurationException $exception) {
            $this->logRepository->addApiLog($exception->getMessage());
            throw $exception;
        }
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
                $exception = new WrongApiCredentialsException();
                $this->logRepository->addApiLog($exception->getMessage());
                throw $exception;
            }
            $exception = new ApiAuthenticationFailedException();
            $this->logRepository->addApiLog($exception->getMessage());
            throw $exception;
        }
        $accessToken = $response['accessToken'];
        $accessTokenExpiry = $response['expireDateTimeAccesToken'];
        $this->session->setData('thuiswinkel_bewustbezorgd_bearer_token', $accessToken);
        $this->bearerToken = $accessToken;
        $this->session->setData('thuiswinkel_bewustbezorgd_bearer_token_expiry', $accessTokenExpiry);
        $this->bearerTokenExpiry = $accessTokenExpiry;

        return $this->getBearerToken();
    }

    /**
     * Sends request to Api and retrieves responded data
     *
     * @param $data
     * @param string $endpoint
     * @return string
     * @throws Throwable
     * @throws WrongApiConfigurationException
     * @throws WrongApiCredentialsException
     */
    public function getEmission($data, $endpoint = 'three-legs')
    {
        if (!($bearerToken = $this->getBearerToken()) || $this->isBearerTokenExpired()) {
            $bearerToken = $this->auth();
        }
        $endpoint = $this->getApiEndpoint('/api/emission-calculation/' . $endpoint);
        $query = '?';
        foreach (reset($data) as $key => $val) {
            $query .= str_replace(' ', '', $key) . "=" . $val . '&';
        }
        $this->curlClient->addHeader('Authorization', 'Bearer ' . $bearerToken);
        $this->curlClient->get($endpoint . $query);
        $this->logRepository->addDataLog($this->curlClient->getBody(), 'ResponseData');
        return $this->serializer->unserialize($this->curlClient->getBody());
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
