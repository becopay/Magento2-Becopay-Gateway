<?php
/**
 * User: Becopay Team
 * Version: 1.0.0
 * Date: 11/6/18
 * Time: 5:00 PM
 */
namespace Becopay\BecopayPaymentGateway\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Becopay\BecopayPaymentGateway\Gateway\Http\Client\ClientMock;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'becopay_gateway';
    const SUCCESS = 1;
    const FAILURE = 0;
    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'transactionResults' => [
                        self::SUCCESS => __('Success'),
                        self::FAILURE => __('Fraud')
                    ]
                ]
            ]
        ];
    }
}
