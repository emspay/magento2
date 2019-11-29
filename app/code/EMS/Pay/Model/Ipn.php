<?php

namespace EMS\Pay\Model;

use EMS\Pay\Gateway\Config\Config;
use EMS\Pay\Gateway\Config\ConfigFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Invoice\Sender\EmailSender;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Ipn
 * @package EMS\Pay\Model
 */
class Ipn
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var Config
     */
    protected $_config;

    /**
     * Collected debug information
     *
     * @var array
     */
    protected $_debugData = [];

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var \Magento\Payment\Model\Method\Logger
     */
    private $logger;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var CurrencyFormat
     */
    private $currencyFormat;

    /**
     * Ipn constructor.
     * @param Config $config
     * @param ResponseFactory $responseFactory
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param ConfigFactory $configFactory
     * @param EmailSender $emailSender
     * @param OrderSender $orderSender
     * @param OrderFactory $orderFactory
     * @param CurrencyFormat $currencyFormat
     */
    public function __construct(
        Config $config,
        ResponseFactory $responseFactory,
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        ConfigFactory $configFactory,
        EmailSender $emailSender,
        OrderSender $orderSender,
        OrderFactory $orderFactory,
        CurrencyFormat $currencyFormat
    ) {
        $this->_config = $config;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->configFactory = $configFactory;
        $this->emailSender = $emailSender;
        $this->orderSender = $orderSender;
        $this->orderFactory = $orderFactory;
        $this->currencyFormat = $currencyFormat;
    }

    /**
     * Get ipn notification data, verify request, process order
     *
     * @param array $requestParams
     * @throws \Exception
     */
    public function processIpnRequest(array $requestParams)
    {
        $this->_debugData[] = __('Processing IPN request');
        $this->_debugData['ipn_params'] = $requestParams;
        $this->response = $this->responseFactory->create(['response' => $requestParams]);
        try {
            $this->_order = null;
            $this->_initOrder();
            $this->response->validate($this->_order->getPayment()->getMethodInstance());
            $this->_processOrder();
        } catch (\Exception $ex) {
            $this->_debugData['exception'] = $this->_formatExceptionForBeingLogged($ex);
            $this->_debug();
            throw $ex;
        }
        $this->_debugData['success'] = __('IPN request processed');
        $this->_debug();
    }

    /**
     * IPN workflow implementation. Runs corresponding response handler depending on status
     *
     * @throws \Exception
     */
    protected function _processOrder()
    {
        try {
            switch ($this->response->getTransactionStatus()) {
                case Response::STATUS_SUCCESS:
                    $this->_registerSuccess(true);
                    break;
                case Response::STATUS_WAITING:
                    $this->_registerPaymentReview();
                    break;
                case Response::STATUS_FAILURE:
                    $this->_registerFailure();
                    break;
            }
        } catch (\Exception $ex) {
            $comment = $this->_createIpnComment(__('Note: %s', $ex->getMessage()));
            $this->_order->addStatusHistoryComment($comment);
            $this->orderRepository->save($this->_order);
            throw $ex;
        }
    }

    /**
     * Processes successful payment
     *
     * @param bool $skipFraudDetection
     * @throws \Exception
     */
    protected function _registerSuccess($skipFraudDetection = false)
    {
        $this->response = $this->getResponseByCurrencyFormat();
        $this->_importPaymentInformation();
        $payment = $this->_order->getPayment();
        $payment->setTransactionId($response->getTransactionId());
        $payment->setCurrencyCode($response->getTextCurrencyCode());
        $payment->setPreparedMessage($this->_createIpnComment(''));
        $payment->setIsTransactionClosed(0);
        $payment->getMethodInstance()->addTransactionData($this->response);
        $payment->registerCaptureNotification(
            $response->getChargeTotal(),
            $skipFraudDetection
        );

        $this->sendInvoice();

        if ($this->_order->getState() === Order::STATE_NEW && $this->_order->hasInvoices()) {
            $this->_order->setIsInProcess(true);
        }
        $this->orderRepository->save($this->_order);
    }

    /**
     * Invoice Sending
     *
     * @throws \Exception
     */
    protected function sendInvoice()
    {
        if (!$this->_config->isInvoiceConfirmationEmailSending() || !$this->_order->hasInvoices()) {
            return;
        }
        $ids = [];
        $invoices = $this->_order->getInvoiceCollection();
        foreach ($invoices as $invoice) {
            $ids[] = $invoice->getIncrementId();
            $this->emailSender->send($this->_order, $invoice, null, $this->_config->isForceSyncModeEmailSending());
        }
        $multi = count($ids) > 1 ? 's' : '';
        $message = __('Notified customer about invoice' . $multi . ': #%s.', implode(', ', $ids));
        $history = $this->_order->addCommentToStatusHistory($message)
            ->setIsCustomerNotified(true);
        $history->save();
    }

    /**
     * Processes failed payment
     */
    protected function _registerFailure()
    {
        $this->_importPaymentInformation();
        $this->_order->cancel();
        $this->orderRepository->save($this->_order);
    }

    /**
     * Processes pending payment notification
     */
    protected function _registerPaymentReview()
    {
        $response = $this->response;
        $this->_importPaymentInformation();
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $this->_order->getPayment();
        $payment->setTransactionId($response->getTransactionId())
            ->setCurrencyCode($response->getTextCurrencyCode())
            ->setPreparedMessage($this->_createIpnComment(''))
            ->setIsTransactionClosed(0);
        $payment->getMethodInstance()->addTransactionData($this->response);
        $message = '';
        if ($payment->getMethod() == Config::METHOD_KLARNA) {
            $message = __('Please visit the EMS virtual terminal to approve the payment for Klarna.');
        }
        $this->_order->setState(Order::STATE_PAYMENT_REVIEW, true, $this->_createIpnComment($message));
        $this->orderRepository->save($this->_order);
    }

    /**
     * Initializes order object based on data from transaction response
     *
     * @return Order
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    protected function _initOrder()
    {
        $this->_order = $this->orderFactory->create()->loadByIncrementId($this->response->getOrderId());
        if (!$this->_order->getId()) {
            $message = __("Order for id %s not found", $this->response->getOrderId());
            $this->_debugData['exception'] = $message;
            $this->_debug();
            throw new \Exception($message);
        }
        //reinitialize config with method code and store id taken from order
        $methodCode = $this->_order->getPayment()->getMethod();
        $this->_config = $this->configFactory->create()->setMethod($methodCode)->setStoreId($this->_order->getStoreId());
        return $this->_order;
    }

    /**
     * @param string $comment
     * @return string
     */
    protected function _createIpnComment($comment = '')
    {
        $status = $this->response->getTransactionStatus();
        $message = __('IPN ' . $status . ', approval code ' . $this->response->getApprovalCode());
        if ($this->response->getFailReason()) {
            $message .= ' ' . $this->response->getFailReason();
        }
        if ($comment) {
            $message .= ' ' . $comment;
        }
        return $message;
    }

    /**
     * Map payment information from transaction response to payment object
     * Returns true if there were changes in information
     *
     * @return bool
     */
    protected function _importPaymentInformation()
    {
        $payment = $this->_order->getPayment();
        $currentInfo = $payment->getAdditionalInformation();
        $data = [
            Info::TRANSACTION_ID => $this->response->getTransactionId(),
            Info::APPROVAL_CODE => $this->response->getApprovalCode(),
            Info::REFNUMBER => $this->response->getRefNumber(),
            Info::IPG_TRANSACTION_ID => $this->response->getIpgTransactionId(),
            Info::ENDPOINT_TRANSACTION_ID => $this->response->getEndpointTransactionId(),
            Info::PROCESSOR_RESPONSE_CODE => $this->response->getProcessorResponseCode(),
        ];
        foreach ($data as $key => $value) {
            $payment->setAdditionalInformation($key, $value);
        }
        return $currentInfo != $data;
    }

    /**
     * Log debug data to file
     */
    protected function _debug()
    {
        if ($this->_config && $this->_config->isDebuggingEnabled()) {
            Debugger::debug($this->_debugData, $this->_config->getLogFile());
        }
    }

    /**
     * Formats exception into text message that can be logged
     *
     * @param \Exception $ex
     * @return string
     */
    protected function _formatExceptionForBeingLogged(\Exception $ex)
    {
        return $ex->getMessage() . ' in ' . $ex->getFile() . ':' . $ex->getLine();
    }

    /**
     * Check response by currency format - change comma to dot
     *
     * @return Response
     */
    protected function getResponseByCurrencyFormat()
    {
        $responseArray = $this->currencyFormat->convertCurrencyStringToFloat($this->response->getResponse());
        $this->response->setResponse($responseArray);
        return $this->response;
    }
}
