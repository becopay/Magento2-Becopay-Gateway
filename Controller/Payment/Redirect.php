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
 * Create invoice and redirect user to gateway url
 */
class Redirect extends Action
{

    /**
     * @var OrderPlace
     */
    private $orderProcess;

    public function __construct(Context $context,OrderProcess $orderProcess)
    {
        parent::__construct($context);
        $this->orderProcess = $orderProcess;
    }

    /**
     * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->orderProcess->getOrderId()) {
            $this->messageManager->addErrorMessage('Invalid request');
            return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
        }
        try {
            $payment = $this->orderProcess->createInvoice();
            if(!$payment){
                $this->messageManager->addErrorMessage($this->orderProcess->error);
                return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
            }

            header('Location: '.$payment->gatewayUrl);
            die;

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
        }

    }
}