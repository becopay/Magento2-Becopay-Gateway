/*browser:true*/
/*global define*/
    define([
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Becopay_BecopayPaymentGateway/js/action/save-payment-information',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success',
        'mage/url'
    ], function (
        $,
        Component,
        placeOrderAction,
        savePaymentAction,
        fullScreenLoader,
        redirectOnSuccessAction,
        url
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Becopay_BecopayPaymentGateway/payment/form',
                transactionResult: '',
                paymentMethodNonce: null,
            },

            initObservable: function () {

                this._super()
                    .observe([
                        'transactionResult'
                    ]);
                return this;
            },

            getCode: function() {
                return 'becopay_gateway';
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'transaction_result': this.transactionResult()
                    }
                };
            },
            placeOrder: function (data, event) {
                var self = this,
                    savePayment;

                if (event) {
                    event.preventDefault();
                }

                savePayment = savePaymentAction(self.getData(), self.messageContainer);

                $.when(savePayment).done(function (response) {

                    fullScreenLoader.stopLoader();

                    window.location.replace(url.build('becopay/payment/redirect/'));

                }).fail(function () {
                    fullScreenLoader.stopLoader();
                    self.isPlaceOrderActionAllowed(true);
                    self.messageContainer.addErrorMessage({
                        'message': $.mage.__('An error occurred on the server. Please try again.')
                    });
                });

            },
            _placeOrder: function () {
                var self = this,
                    placeOrder = placeOrderAction(self.getData(), self.messageContainer);

                $.when(placeOrder).done(function() {
                    if (self.redirectAfterPlaceOrder) {
                        redirectOnSuccessAction.execute();
                    }
                }).fail(function() {
                    fullScreenLoader.stopLoader();
                    self.isPlaceOrderActionAllowed(true);
                });
            },
            getTransactionResults: function() {
                return _.map(window.checkoutConfig.payment.becopay_gateway.transactionResults, function(value, key) {

                    return {
                        'value': key,
                        'transaction_result': value
                    }
                });
            },
            setPaymentMethodNonce: function (paymentMethodNonce) {
                this.paymentMethodNonce = paymentMethodNonce;
            },


            beforePlaceOrder: function (data) {
                this.setPaymentMethodNonce(data.nonce);
                this.placeOrder();
            },
        });
    }
);