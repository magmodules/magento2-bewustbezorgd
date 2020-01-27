<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\HTTP\Client;

use Magento\Framework\HTTP\Client\Curl as MagentoClientCurl;
use CURLFile;

/**
 * Class Curl
 */
class Curl extends MagentoClientCurl
{
    /**
     * Max supported protocol by curl CURL_SSLVERSION_TLSv1_2
     * @var int
     */
    private $sslVersion;

    /**
     * Constructor.
     *
     * @param int|null $sslVersion
     */
    public function __construct($sslVersion = null)
    {
        $this->sslVersion = $sslVersion;

        parent::__construct($sslVersion);
    }

    /**
     * @inheritDoc
     */
    protected function makeRequest($method, $uri, $params = [])
    {
        if (!(is_array($params) && isset($params['uploadedFile']))) {
            parent::makeRequest($method, $uri, $params);

            return;
        }
        // @codingStandardsIgnoreStart
        $this->_ch = curl_init();
        $this->curlOption(CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS | CURLPROTO_FTP | CURLPROTO_FTPS);
        $this->curlOption(CURLOPT_URL, $uri);
        $this->curlOption(CURLOPT_POST, 1);
        $params['uploadedFile'] = new CURLFile($params['uploadedFile'], 'text/csv');
        $this->curlOption(CURLOPT_POSTFIELDS, $params);
        if (count($this->_headers)) {
            $heads = [];
            foreach ($this->_headers as $k => $v) {
                $heads[] = $k . ': ' . $v;
            }
            $this->curlOption(CURLOPT_HTTPHEADER, $heads);
        }

        if (count($this->_cookies)) {
            $cookies = [];
            foreach ($this->_cookies as $k => $v) {
                $cookies[] = "{$k}={$v}";
            }
            $this->curlOption(CURLOPT_COOKIE, implode(";", $cookies));
        }
        if ($this->_timeout) {
            $this->curlOption(CURLOPT_TIMEOUT, $this->_timeout);
        }
        if ($this->_port != 80) {
            $this->curlOption(CURLOPT_PORT, $this->_port);
        }
        $this->curlOption(CURLOPT_RETURNTRANSFER, 1);
        $this->curlOption(CURLOPT_HEADERFUNCTION, [$this, 'parseHeaders']);

        if ($this->sslVersion !== null) {
            $this->curlOption(CURLOPT_SSLVERSION, $this->sslVersion);
        }
        if (count($this->_curlUserOptions)) {
            foreach ($this->_curlUserOptions as $k => $v) {
                $this->curlOption($k, $v);
            }
        }
        $this->_headerCount = 0;
        $this->_responseHeaders = [];
        $this->_responseBody = curl_exec($this->_ch);
        $err = curl_errno($this->_ch);

        if ($err) {
            $this->doError(curl_error($this->_ch));
        }
        curl_close($this->_ch);
        // @codingStandardsIgnoreEnd
    }
}
