<?php

namespace Thuiswinkel\BewustBezorgd\Controller\Adminhtml\System\Config\ApiPermissions;

use Throwable;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Thuiswinkel\BewustBezorgd\Model\ApiConnection;
use Thuiswinkel\BewustBezorgd\Model\Exception\ApiAuthenticationFailedException;
use Thuiswinkel\BewustBezorgd\Model\Exception\WrongApiConfigurationException;
use Thuiswinkel\BewustBezorgd\Model\Exception\WrongApiCredentialsException;

class Validate extends Action
{
    /** @var ApiConnection */
    protected $apiConnection;

    /** @var JsonFactory */
    protected $resultJsonFactory;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ApiConnection $apiConnection
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        ApiConnection $apiConnection
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->apiConnection = $apiConnection;
    }

    /**
     * Check whether permissions is valid
     *
     * @return ResultJson
     */
    public function execute()
    {
        $result = $this->_validate();

        /** @var ResultJson $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }

    /**
     * Perform permissions validation
     *
     * @return array
     */
    protected function _validate()
    {
        $result = [
            'valid'     => false,
            'message'   => __('Error during credentials verification.')
        ];

        try {
            $this->apiConnection->setApiShopId($this->getRequest()->getParam('apiShopId'))
                ->setApiPassword($this->getRequest()->getParam('apiPassword'))
                ->auth();
            $result = [
                'valid'     => true,
                'message'   => __('Credentials look like correct.')
            ];
        } catch (WrongApiConfigurationException $exception) {
            $result['message'] = $exception->getMessage();
        } catch (WrongApiCredentialsException $exception) {
            $result['message'] = $exception->getMessage();
        } catch (ApiAuthenticationFailedException $exception) {
            $result['message'] = $exception->getMessage();
        } catch (Throwable $exception) {
            $result['message'] = $exception->getMessage();
        }

        return $result;
    }
}
