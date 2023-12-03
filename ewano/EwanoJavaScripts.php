<?php
include_once('EwanoAssist.php');

class EwanoJavaScripts {
    public function __construct(){
        $this->assist = new EwanoAssist();
//        $this->development = $this->assist->development;
        $this->development = true;
        $this->developmentResultStatus = true;
        $this->script_url = 'https://static-ebcom.mci.ir/static/ewano/assets/ewano-web-toolkit-v1.min.js';
        add_action('wp_head', function() {
            $this->injectEwanoScript();
        }, 8);
    }

    public function onWebAppReady () {
        add_action('wp_footer', function() {
            $this->injectOnWebAppReadyScript();
        }, 11);
    }

    public function ewanoPay ($amount, $ewanoOrderId) {
        add_action('wp_footer', function() use ($amount, $ewanoOrderId) {
            $this->injectPayScript($amount, $ewanoOrderId);
        }, 12);
    }

    public function overrideEwanoPaymentResultMethod ($callbackUrl) {
        add_action('wp_footer', function() use ($callbackUrl) {
            $this->injectPaymentResultScript($callbackUrl);
        }, 9);
    }

    private function injectEwanoScript () {
        if ($this->assist->isFromEwano() || $this->assist->hasEwanoFlag()) {
            echo '<script src="' . esc_url($this->script_url) . '"></script>';
        }
    }

    private function injectOnWebAppReadyScript () {
        ?>
        <script>
            window.ewano.onWebAppReady()
            console.log('ewano.onWebAppReady');
        </script>
        <?php
    }


    private function injectPayScript ($amount, $ewanoOrderId) {
        ?>
        <script>
            window.ewano.pay(<?php echo $amount; ?>, '<?php echo $ewanoOrderId; ?>');
            console.log('ewano.pay -> $amount:', <?php echo $amount; ?>);
            console.log('ewano.pay -> $ewanoOrderId:', <?php echo $ewanoOrderId; ?>);
        </script>
        <?php
    }

    private function injectPaymentResultScript ($callbackUrl) {
        ?>
        <script>
            console.log('ewano.overrideEwanoPaymentResultMethod -> $callbackUrl:', '<?php echo $callbackUrl; ?>')
            <?php
                if ($this->development) {
                    ?>
                        console.log('ewano.paymentResult development -> status:', '<?php echo $this->developmentResultStatus ? '1' : '0'; ?>')
                        let url = new URL('<?php echo $callbackUrl; ?>');
                        url.searchParams.append('status', '<?php echo $this->developmentResultStatus ? '1' : '0'; ?>');
                        // console.log('url.toString()', url.toString())
                        window.location.href = url.toString();
                    <?php
                }
            ?>
            window.ewano.paymentResult = (status) => {
                console.log('ewano.paymentResult -> status:', status)
                let url = new URL('<?php echo $callbackUrl; ?>');
                url.searchParams.append('status', status ? '1' : '0');
                window.location.href = url.toString();
                // const ewanoCustomEvent = new CustomEvent('ewano-payment-result', { detail: { status } });
                // window.document.dispatchEvent(ewanoCustomEvent);
            }
        </script>
        <?php
    }
}
