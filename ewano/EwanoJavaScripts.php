<?php
include_once('EwanoAssist.php');

class EwanoJavaScripts {
    public function __construct(){
        $this->assist = new EwanoAssist();
        if ($this->assist->isFromEwano()) {
            add_action('wp_head', array($this, 'injectEwanoScript'));
        }
    }

    public function onWebAppReady () {
        add_action('wp_footer', array($this, 'injectOnWebAppReadyScript'));
    }

    public function login () {
        add_action('wp_footer', array($this, 'injectLoginScript'));
    }

    public function ewanoPay () {
        add_action('wp_footer', array($this, 'injectPayScript'));
    }

    public function ewanoPaymentResult () {
        add_action('wp_footer', array($this, 'injectPaymentResultScript'));
    }

    private function injectEwanoScript () {
        $script_url = 'https://static-ebcom.mci.ir/static/ewano/assets/ewano-web-toolkit-v1.min.js';

        if (isFromEwano()) {
            echo '<script src="' . esc_url($script_url) . '"></script>';
        }
    }

    private function injectOnWebAppReadyScript () {
        ?>
        <script>
            APIGateway.ewano.login(uuid)
            window.ewano.onWebAppReady()
            window.ewano.paymentResult = (status) => {
                const ewanoCustomEvent = new CustomEvent('ewano-payment-result', { detail: { status } });
                window.document.dispatchEvent(ewanoCustomEvent);
            }
            window.ewano.pay(amount, orderId, callbackUrl)
        </script>
        <?php
    }

    private function injectLoginScript () {
        ?>
        <script>
            APIGateway.ewano.login(uuid)
        </script>
        <?php
    }

    private function injectPayScript () {
        ?>
        <script>
            window.ewano.pay(amount, orderId, callbackUrl)
        </script>
        <?php
    }

    private function injectPaymentResultScript () {
        ?>
        <script>
            window.ewano.paymentResult = (status) => {
                const ewanoCustomEvent = new CustomEvent('ewano-payment-result', { detail: { status } });
                window.document.dispatchEvent(ewanoCustomEvent);
            }
        </script>
        <?php
    }
}
