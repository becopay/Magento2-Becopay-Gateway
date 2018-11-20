<?php
/**
 * User: Becopay Team
 * Version: 1.0.0
 * Date: 11/5/18
 * Time: 6:02 PM
 */

namespace Becopay\BecopayPaymentGateway\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Becopay\BecopayPaymentGateway\Model\Helper\OrderProcess;

/**
 * Responsible for callback request from becopay.
 */
class Callback extends Action
{

    /**
     * @var OrderPlace
     */
    private $orderProcess;

    public function __construct(Context $context, OrderProcess $orderProcess)
    {
        parent::__construct($context);
        $this->orderProcess = $orderProcess;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        // Get order id
        $orderId = $this->getRequest()->getParam('orderId');
        $orderArray = explode('-', $orderId);

        $quoteId = $orderArray[0];

        if (
            !isset($orderId) ||
            !$this->orderProcess->getOrderId() ||
            $quoteId != $this->orderProcess->getOrderId()
        ) {
            $this->messageManager->addErrorMessage('invalid request');
            return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
        }

        try {
            $payment = $this->orderProcess->CheckInvoice($orderId);
            if ($payment && $payment->status == OrderProcess::STATUS_SUCCESS) {
                $this->orderProcess->placeOrder($payment);
                return $resultRedirect->setPath('checkout/onepage/success', ['_secure' => true]);
            } else if (!$payment) {
                $this->messageManager->addErrorMessage($this->orderProcess->error);
            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
    }


}