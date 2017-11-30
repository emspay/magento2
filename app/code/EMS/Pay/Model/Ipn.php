<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 13.11.17
 * Time: 16:04
 */

namespace EMS\Pay\Model;

use EMS\Pay\Model\Response;
use Magento\Sales\Model\Order;
use EMS\Pay\Gateway\Config\Config;


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
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var InvoiceMailerFactory
     */
    private $invoiceMailerFactory;
    /**
     * @var \EMS\Pay\Gateway\Config\ConfigFactory
     */
    private $configFactory;
    /**
     * @var Order\Invoice\Sender\EmailSender
     */
    private $emailSender;


    /**
     * Ipn constructor.
     * @param Config $config
     * @param ResponseFactory $responseFactory
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param InvoiceMailerFactory $invoiceMailerFactory
     * @param \EMS\Pay\Gateway\Config\ConfigFactory $configFactory
     * @param Order\Invoice\Sender\EmailSender $emailSender
     */
    public function __construct(
        Config $config,
        \EMS\Pay\Model\ResponseFactory $responseFactory,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \EMS\Pay\Model\InvoiceMailerFactory $invoiceMailerFactory,
        \EMS\Pay\Gateway\Config\ConfigFactory $configFactory,
        \Magento\Sales\Model\Order\Invoice\Sender\EmailSender $emailSender
    )
    {
        $this->_config = $config;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->invoiceMailerFactory = $invoiceMailerFactory;
        $this->configFactory = $configFactory;
        $this->emailSender = $emailSender;
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
        $this->response = $this->responseFactory->create($requestParams);
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
     */
    protected function _registerSuccess($skipFraudDetection = false)
    {
        $response = $this->response;
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
        $this->orderRepository->save($this->_order);
        $this->_order->queueNewOrderEmail()
            ->setIsCustomerNotified(true);
        $this->orderRepository->save($this->_order);

        $ids = array();
        $invoices = $this->_order->getInvoiceCollection();
        foreach($invoices as $invoice) {
            if ($invoice) {
                $ids[] = $invoice->getIncrementId();
                $this->emailSender->send($this->_order, $invoice);
            }
        }
        $multi = count($ids)>1 ? 's' : '';
        $message = __('Notified customer about invoice'.$multi.': #%s.', implode(', ', $ids));
        $this->_order->addStatusHistoryComment($message)->save();
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
        $this->_order
            ->setState(Order::STATE_PAYMENT_REVIEW, true, $this->_createIpnComment($message));
        $this->orderRepository->save($this->_order);
    }
    /**
     * Initializes order object based on data from transaction response
     *
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    protected function _initOrder()
    {
        $orderId =
        $this->_order = $this->orderRepository->get($this->response->getOrderId());
        if (!$this->_order->getId()) {
            $message = __("Order for id %s not found", $orderId);
            $this->_debugData['exception'] = $message;
            $this->_debug();
            throw new \Exception($message);
        }
        //reinitialize config with method code and store id taken from order
        $methodCode = $this->_order->getPayment()->getMethod();
        $this->_config =  $this->configFactory->create()
            ->setMethod($methodCode)
            ->setStoreId($this->_order->getStoreId());
        return $this->_order;
    }
    /**
     * @param string $comment
     * @return string
     */
    protected function _createIpnComment($comment = '')
    {
        $status = $this->response->getTransactionStatus();
        $message = __('IPN "%s", approval code "%s".', $status, $this->response->getApprovalCode());
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
            $this->logger->debug($this->_debugData);
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
}