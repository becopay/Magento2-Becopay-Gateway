<?php
/**
 * User: Becopay Team
 * Version: 1.0.0
 * Date: 11/6/18
 * Time: 5:00 PM
 */

namespace Becopay\BecopayPaymentGateway\Model\Helper;

use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Becopay\PaymentGateway;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\Builder;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order;
use Magento\Checkout\Model\Cart as CustomerCart;

/**
 * Class OrderProcess
 *
 * @package Becopay\BecopayPaymentGateway\Model\Helper
 */
class OrderProcess
{

    /**
     * Becopay invoice success status
     */
    const STATUS_SUCCESS = 'success';
    /**
     * Becopay invoice waiting status
     */
    const STATUS_WAITING = 'waiting';
    /**
     * Becopay invoice failed status
     */
    const STATUS_FAILED = 'failed';
    /**
     * Merchant default currency
     */
    const MERCHANT_CURRENCY = 'IRR';
    /**
     * @var
     */
    public $error;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var PaymentGateway
     */
    protected $payment;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;
    /**
     * @var
     */
    protected $becopayGateway;
    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;
    /**
     * @var Builder
     */
    protected $transactionBuilder;
    /**
     * @var InvoiceService
     */
    protected $invoiceService;
    /**
     * @var Transaction
     */
    protected $transaction;
    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @var Merchant currency set on panel
     */
    private $merchantCurrency;


    /**
     * OrderProcess constructor.
     *
     * @param Session                 $session
     * @param CustomerSession         $customerSession
     * @param ScopeConfigInterface    $config
     * @param Builder                 $transactionBuilder
     * @param InvoiceService          $invoiceService
     * @param Transaction             $transaction
     * @param CartManagementInterface $cartManagement
     * @param CustomerCart            $cart
     */
    public function __construct(
        Session $session,
        CustomerSession $customerSession,
        ScopeConfigInterface $config,
        Builder $transactionBuilder,
        InvoiceService $invoiceService,
        Transaction $transaction,
        CartManagementInterface $cartManagement,
        CustomerCart $cart
    )
    {
        $this->session = $session;
        $this->customerSession = $customerSession;
        $this->config = $config;
        $this->cartManagement = $cartManagement;
        $this->transactionBuilder = $transactionBuilder;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->cart = $cart;
    }

    /**
     * Get becopay config and init payment gateway class
     */
    private function initBecopayGateway()
    {
        $this->becopayGateway = new PaymentGateway(
            $this->getConfig('api_base_url'),
            $this->getConfig('api_key'),
            $this->getConfig('mobile')
        );
        $this->merchantCurrency = $this->getConfig('merchant_currency') ?: $this::MERCHANT_CURRENCY;
    }

    /**
     * Create Becopay invoice
     *
     * @return bool
     */
    public function createInvoice()
    {
        $this->error = '';

        $this->initBecopayGateway();

        $amount = $this->getAmount();

        $order = $this->getOrder();
        $address = $order->getShippingAddress();

        $description = implode(', ', [
            'OrderId: ' . $this->getOrderId(),
            'Email: ' . $address->getEmail(),
            'Amount: ' . $amount . ' ' . $this->getCurrency()
        ]);

        $invoice = $this->becopayGateway->create(
            uniqid($this->getOrderId() . '-'),
            $amount,
            $description,
            $this->getCurrency(),
            $this->merchantCurrency
        );

        if (!$invoice) {
            $this->error = $this->becopayGateway->error;
            return false;
        }

        if (
            $invoice->merchantCur != $this->merchantCurrency ||
            $invoice->payerCur != $this->getCurrency() ||
            $invoice->payerAmount != $amount
        ) {
            $this->error = 'Gateway response not valid';
            return false;
        }

        return $invoice;

    }

    /**
     * Check Becopay invoice
     *
     * @param $orderId
     * @return bool
     */
    public function CheckInvoice($orderId)
    {
        $this->error = '';

        $this->initBecopayGateway();

        $amount = $this->getAmount();

        $invoice = $this->becopayGateway->checkByOrderId($orderId);

        if (!$invoice) {
            $this->error = $this->becopayGateway->error;
            return false;
        }

        if (
            $invoice->merchantCur != $this->merchantCurrency ||
            $invoice->payerCur != $this->getCurrency() ||
            $invoice->payerAmount != $amount
        ) {
            $this->error = 'amount is not same. invoice id ' . $invoice->id;
            return false;
        }

        return $invoice;

    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->session->getQuote();
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->getOrder()->getId();
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        $order = $this->getOrder();
        $amount = $order->getGrandTotal();

        return floatval($amount);
    }

    /**
     * Return store currency
     *
     * @return string
     */
    public function getCurrency()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $currencysymbol = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currency = $currencysymbol->getStore()->getCurrentCurrencyCode();

        return $currency;
    }

    /**
     * Get becopay configuration on magento
     *
     * @param $key
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this->config->getValue('payment/becopay_gateway/' . $key, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Create order and add transaction and invoice
     *
     * @param $paymentData
     */
    public function placeOrder($paymentData)
    {
        $orderId = $this->cartManagement->placeOrder($this->getOrderId());

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Api\Data\OrderInterface')->load($orderId);

        $order->setState($order->getState());
        $order->setStatus($this->orderPaidStatus());

        $this->addInvoiceToOrder($order);
        $this->addTransactionToOrder($order, (array)$paymentData);
    }

    /**
     * return order paid status
     *
     * @return mixed
     */
    public function orderPaidStatus()
    {
        $order = $this->getConfig('after_order_status');
        if (!empty($order))
            return $order;
        return Order::STATE_PROCESSING;
    }

    /**
     * Add transaction to magento order
     *
     * @param       $order
     * @param array $paymentData
     * @return mixed
     */
    public function addTransactionToOrder($order, $paymentData = array())
    {
        try {
            // Prepare payment object
            $payment = $order->getPayment();
            $payment->setMethod('becopay_gateway');
            $payment->setLastTransId($paymentData['id']);
            $payment->setTransactionId($paymentData['id']);
            $payment->setAdditionalInformation([Transaction::RAW_DETAILS => (array)$paymentData]);

            // Formatted price
            $formatedPrice = $order->getBaseCurrency()->formatTxt($order->getGrandTotal());

            // Prepare transaction
            $transaction = $this->transactionBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($paymentData['id'])
                ->setAdditionalInformation([Transaction::RAW_DETAILS => (array)$paymentData])
                ->setFailSafe(true)
                ->build(Transaction::TYPE_CAPTURE);

            // Add transaction to payment
            $payment->addTransactionCommentsToOrder($transaction, __('The paid amount is %1.', $formatedPrice));
            $payment->setParentTransactionId(null);

            // Save payment, transaction and order
            $payment->save();
            $order->save();
            $transaction->save();

            return $transaction->getTransactionId();

        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }
    }

    /**
     * Add invoice to magento order
     *
     * @param $order
     */
    public function addInvoiceToOrder($order)
    {
        if ($order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->setState(Invoice::STATE_PAID);
            $invoice->save();

            //send notification code
            $order->addStatusHistoryComment(
                __('Notified customer about invoice #%1.', $invoice->getId())
            )
                ->setIsCustomerNotified(true)
                ->save();
        }
    }

    /**
     * Clear the cart
     */
    public function clearCart(){
        $allItems = $this->session->getQuote()->getAllVisibleItems();
        foreach ($allItems as $item) {
            $itemId = $item->getItemId();
            $this->cart->removeItem($itemId)->save();
        }
    }
}