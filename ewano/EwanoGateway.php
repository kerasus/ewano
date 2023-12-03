<?php
include_once('EwanoApi.php');
include_once('EwanoAuth.php');
include_once('EwanoAssist.php');
include_once('EwanoJavaScripts.php');

function Load_Ewano_Gateway() {

    if ( !class_exists( 'WC_Payment_Gateway' ) || class_exists( 'WC_Ewano' ) || function_exists('Woocommerce_Add_Ewano_Gateway') ) {
        return null;
    }

    // remove all payment gateways
    add_filter( 'woocommerce_available_payment_gateways', 'disable_all_gateways', 9999 );
    function disable_all_gateways( $gateways ) {
        foreach ( $gateways as $gateway_id => $gateway ) {
            if ($gateway_id !== 'WC_Ewano') {
                unset( $gateways[ $gateway_id ] );
            }
        }

        return $gateways;
    }

    // add WC_Ewano payment gateway
    add_filter('woocommerce_payment_gateways', 'Woocommerce_Add_Ewano_Gateway', 999 );
    function Woocommerce_Add_Ewano_Gateway($methods) {
        $methods[] = 'WC_Ewano';
        return $methods;
    }

    class WC_Ewano extends WC_Payment_Gateway {

        public function __construct(){

            $this->api = new EwanoApi();
            $this->assist = new EwanoAssist();
            $this->javaScripts = new EwanoJavaScripts();

            //by Woocommerce.ir
            $this->author = 'Woocommerce.ir';
            //by Woocommerce.ir


            $this->checkout_url = wc_get_checkout_url();
//            $this->checkout_url = $woocommerce->cart->get_checkout_url();


            $this->id = 'WC_Ewano';
            $this->method_title = __('درگاه ایوانو', 'woocommerce');
            $this->method_description = __( 'تنظیمات درگاه پرداخت ایوانو برای افزونه فروشگاه ساز ووکامرس', 'woocommerce');
            $this->icon = apply_filters('WC_Ewano_logo', WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/assets/images/logo.png');
            $this->has_fields = false;

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->settings['title'];
            $this->description = $this->settings['description'];

            $this->terminal = $this->settings['terminal'];

            $this->success_massage = $this->settings['success_massage'];
            $this->failed_massage = $this->settings['failed_massage'];

            if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) )
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            else
                add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );
            add_action('woocommerce_receipt_'.$this->id.'', array($this, 'showGoingToEwanoGatewayTemplate'));
            add_action('woocommerce_api_'.strtolower(get_class($this)).'', array($this, 'showReturnFromEwanoGatewayTemplate') );

        }

        public function admin_options(){
            $action = $this->author;
            do_action( 'WC_Gateway_Payment_Actions', $action );
            parent::admin_options();
        }

        public function init_form_fields(){
            $this->form_fields = apply_filters('WC_Ewano_Config',
                array(
                    'enabled' => array(
                        'title'   => __( 'فعالسازی/غیرفعالسازی', 'woocommerce' ),
                        'type'    => 'checkbox',
                        'label'   => __( 'فعالسازی درگاه ایوانو', 'woocommerce' ),
                        'description' => __( 'برای فعالسازی درگاه پرداخت ایوانو باید چک باکس را تیک بزنید', 'woocommerce' ),
                        'default' => 'yes',
                        'desc_tip'    => true,
                    ),
                    'title' => array(
                        'title'       => __( 'عنوان درگاه', 'woocommerce' ),
                        'type'        => 'text',
                        'description' => __( 'عنوان درگاه که در طی خرید به مشتری نمایش داده میشود', 'woocommerce' ),
                        'default'     => __( 'ایوانو', 'woocommerce' ),
                        'desc_tip'    => true,
                    ),
                    'description' => array(
                        'title'       => __( 'توضیحات درگاه', 'woocommerce' ),
                        'type'        => 'textarea',
                        'desc_tip'    => true,
                        'description' => __( 'توضیحاتی که در طی عملیات پرداخت برای درگاه نمایش داده خواهد شد', 'woocommerce' ),
                        'default'     => __( 'پرداخت امن به وسیله کلیه کارت های عضو شتاب از طریق درگاه ایوانو', 'woocommerce' )
                    ),
                    'terminal' => array(
                        'title'       => __( 'ترمینال آیدی', 'woocommerce' ),
                        'type'        => 'text',
                        'description' => __( 'شماره ترمینال درگاه ایوانو', 'woocommerce' ),
                        'default'     => '',
                        'desc_tip'    => true
                    ),
                    'success_massage' => array(
                        'title'       => __( 'پیام پرداخت موفق', 'woocommerce' ),
                        'type'        => 'textarea',
                        'description' => __( 'متن پیامی که میخواهید بعد از پرداخت موفق به کاربر نمایش دهید را وارد نمایید . همچنین می توانید از شورت کد {transaction_id} برای نمایش کد رهگیری ( کد مرجع تراکنش ) و از شرت کد {SaleOrderId} برای شماره درخواست تراکنش ایوانو استفاده نمایید .', 'woocommerce' ),
                        'default'     => __( 'با تشکر از شما . سفارش شما با موفقیت پرداخت شد .', 'woocommerce' ),
                    ),
                    'failed_massage' => array(
                        'title'       => __( 'پیام پرداخت ناموفق', 'woocommerce' ),
                        'type'        => 'textarea',
                        'description' => __( 'متن پیامی که میخواهید بعد از پرداخت ناموفق به کاربر نمایش دهید را وارد نمایید . همچنین می توانید از شورت کد {fault} برای نمایش دلیل خطای رخ داده استفاده نمایید . این دلیل خطا از سایت ایوانو ارسال میگردد .', 'woocommerce' ),
                        'default'     => __( 'پرداخت شما ناموفق بوده است . لطفا مجددا تلاش نمایید یا در صورت بروز اشکال با مدیر سایت تماس بگیرید .', 'woocommerce' ),
                    ),
                    'make_order_failed_massage' => array(
                        'title'       => __( 'پیام اشکال در ساخت سفارش سمت ایوانو', 'woocommerce' ),
                        'type'        => 'textarea',
                        'description' => __( 'این پیام زمانی نشان داده می شود که مشکلی در هنگام ارتباط با سرویس ایوانو جهت ساخت سفارش رخ می دهد.', 'woocommerce' ),
                        'default'     => __( 'پرداخت با موفقیت انجام نشد. مشکلی در ساخت سفارش سمت ایوانو رخ داده است.', 'woocommerce' ),
                    ),
                    'make_order_invalid_amount_massage' => array(
                        'title'       => __( 'پیام اشکال در بررسی صحت قیمت سفارش', 'woocommerce' ),
                        'type'        => 'textarea',
                        'description' => __( 'این پیام زمانی نشان داده می شود که قیمت محاسبه شده سمت ایوانو با قیمت نهایی سمت شما همخوانی ندارد.', 'woocommerce' ),
                        'default'     => __( 'پرداخت با موفقیت انجام نشد. قیمت محاسبه شده سمت ایوانو صحیح نیست.', 'woocommerce' ),
                    ),
                    'make_order_invalid_order_id_massage' => array(
                        'title'       => __( 'پیام اشکال در بررسی صحت کد سفارش', 'woocommerce' ),
                        'type'        => 'textarea',
                        'description' => __( 'این پیام زمانی نشان داده می شود که کد سفارش اعلام شده از سمت ایوانو با کد سفارش سمت شما همخوانی ندارد.', 'woocommerce' ),
                        'default'     => __( 'پرداخت با موفقیت انجام نشد. کد سفارش اعلام شده از سمت ایوانو صحیح نیست.', 'woocommerce' ),
                    ),
                    'ewano_result_failed_request_massage' => array(
                        'title'       => __( 'پیام اشکال در ارسال اطلاعات به سرویس ایوانو', 'woocommerce' ),
                        'type'        => 'textarea',
                        'description' => __( 'این پیام زمانی نشان داده می شود که مشکلی در ارسال اطلاعات جهت نهایی کردن پرداخت رخ داده است.', 'woocommerce' ),
                        'default'     => __( 'پرداخت با موفقیت انجام نشد. فرایند پرداخت سمت سرویس ایوانو با مشکلی مواجه شده است.', 'woocommerce' ),
                    ),
                    'ewano_result_invalid_ref_id_massage' => array(
                        'title'       => __( 'پیام اشکال در بررسی صحت کد رهگیری', 'woocommerce' ),
                        'type'        => 'textarea',
                        'description' => __( 'این پیام زمانی نشان داده می شود که کد رهگیری که در آدرس کاربر هنگام برگشت از درگاه به همراه دارد با کد رهگیری استعلام شده از سرویس ایوانو همخوانی ندارد.', 'woocommerce' ),
                        'default'     => __( 'کد رهگیری سرویس ایوانو معتبر نیست.', 'woocommerce' ),
                    ),
                )
            );
        }

        public function process_payment( $order_id ) {
            $order = new WC_Order( $order_id );
            return array(
                'result'   => 'success',
                'redirect' => $order->get_checkout_payment_url(true)
            );
        }

        public function showGoingToEwanoGatewayTemplate($orderId){
            global $woocommerce;
            $woocommerce->session->order_id_bankmellat = $orderId;
            $orderDataForGateway = $this->getOrderDataForGateway($orderId);
            $payableAmount = $orderDataForGateway['amount'];

            $data = $this->getDataForMakeOrderRequest($orderId);
            // call alaa api to create ewano order and get
            $response = $this->api->makeOrder($data);
            if ($response === null) {
                wc_add_notice($this->settings['make_order_failed_massage'] , 'error' );
                wp_redirect( $this->checkout_url );
                exit;
            }
            $refCode = $response['ref_id'];
            $wcOrderIdFromService = $response['client_order_id'];
            $ewanoOrderId = $response['third_party_order_id'];
//            $totalAmountFromService = $response['total_amount'];
            $payableAmountFromService = $response['payable_amount'];
            if ((int)$payableAmountFromService !== (int)$payableAmount) {
                wc_add_notice($this->settings['make_order_invalid_amount_massage'] , 'error' );
                wp_redirect( $this->checkout_url );
                exit;
            }
            if ((int)$wcOrderIdFromService !== (int)$orderId) {
                wc_add_notice($this->settings['make_order_invalid_order_id_massage'] , 'error' );
                wp_redirect( $this->checkout_url );
                exit;
            }
            $callBackUrl = add_query_arg( array(
                'ref_code' => $refCode,
                'wc_order' => $orderId,
                'ewano_order' => $ewanoOrderId
            ), WC()->api_request_url('WC_Ewano'));
            $this->javaScripts->overrideEwanoPaymentResultMethod($callBackUrl);
            $this->javaScripts->ewanoPay($payableAmount, $ewanoOrderId);
        }

        private function checkAndShowProperErrorForQueryParamsInReturnFromEwanoGateway () {
            $keys = [
                'ref_code',
                'wc_order',
                'ewano_order',
                'status',
            ];
            $status = true;

            foreach ($keys as $key) {
                if (!isset($_GET[$key])) {
                    $this->failedPayment('پرداخت با موفقیت انجام نشد. پارامتر ' . $key . ' به درستی مشخص نشده است.');
                    $status = false;
                }
            }

            return $status;
        }

        public function showReturnFromEwanoGatewayTemplate () {
            $queryParamStatus = $this->checkAndShowProperErrorForQueryParamsInReturnFromEwanoGateway();
            if (!$queryParamStatus) {
                exit;
            }

            $wcOrderId = $_GET['wc_order'];
            $refCodeFromUrl = $_GET['ref_code'];
            $ewanoOrderId = $_GET['ewano_order'];
            $status = $_GET['status'];

            if ($status !== '1') {
                $this->failedPayment($this->failed_massage);
                exit;
            }

            $order = new WC_Order($wcOrderId);

            if($order->get_status() === 'completed') {
                $Notice = wpautop( wptexturize($this->success_massage));
                wc_add_notice( $Notice , 'success' );
                wp_redirect( add_query_arg( 'wc_status', 'success', $this->get_return_url( $order ) ) );
                exit;
            }

            $response = $this->api->pay($ewanoOrderId);
            if ($response === null) {
                $this->failedPayment($this->settings['ewano_result_failed_request_massage']);
                wp_redirect( $this->checkout_url );
                exit;
            }
            if ($response['status'] !== 'OK') {
//                $message = $response['message'] ?? 'پرداخت با موفقیت انجام نشد. فرایند پرداخت سمت سرویس ایوانو با موفقیت انجام نشد.';
                $this->failedPayment($this->failed_massage);
                wp_redirect( $this->checkout_url );
                exit;
            }

            $refCodeFromPayService = $response['ref_id'];
            if ((int)$refCodeFromPayService !== (int)$refCodeFromUrl) {
                $this->failedPayment($this->settings['ewano_result_invalid_ref_id_massage']);
                wp_redirect( $this->checkout_url );
                exit;
            }

            global $woocommerce;
            $order->payment_complete($ewanoOrderId);
            $woocommerce->cart->empty_cart();
            $order->add_order_note( 'شماره تراکنش ایوانو: ' . $ewanoOrderId, 1 );
            $Notice = wpautop( wptexturize($this->success_massage));
            wc_add_notice( $Notice , 'success' );
            wp_redirect( add_query_arg( 'wc_status', 'success', $this->get_return_url( $order ) ) );
            exit;
        }

        private function failedPayment ($message = 'پرداخت با موفقیت انجام نشد.') {
            wc_add_notice(__($message, 'woocommerce' ) , 'error' );
            wp_redirect( $this->checkout_url );
        }

        private function getOrderDataForGateway ($order_id) {
            $order = new WC_Order( $order_id );
            $totalDiscount = $order->get_discount_total();
            $items = $this->getOrderDataForGateway_items($order);
            $currency = $this->getOrderDataForGateway_currency($order);
            $amount = $this->getOrderDataForGateway_amount($order, $currency);

            return [
                'items'=> $items,
                'order'=> $order,
                'amount'=> $amount,
                'currency'=> $currency,
                'total_discount'=> $totalDiscount
            ];
        }

        private function getOrderDataForGateway_items ($order) {
            $items = $order->get_items();
            $gatewayItems = [];
            foreach ( $items as $item ) {
                $product = $item->get_product();
                $quantity = $item->get_quantity();

                $finalPrice = $item->get_total();
                $basePrice = $item->get_subtotal();

                $finalUnitPrice = $finalPrice / $quantity;
                $baseUnitPrice = $basePrice / $quantity;

                $unitDiscount = $baseUnitPrice - $finalUnitPrice;
                $totalDiscount = $basePrice - $finalPrice;

                $gatewayItems []= [
                    'quantity'=> $quantity,
                    'product'=> $product,
                    'name'=> $product->get_name(),
                    'basePrice'=> $basePrice,
                    'finalPrice'=> $finalPrice,
                    'baseUnitPrice'=> $baseUnitPrice,
                    'finalUnitPrice'=> $finalUnitPrice,
                    'unitDiscount'=> $unitDiscount,
                    'totalDiscount'=> $totalDiscount
                ];
            }
            return $gatewayItems;
        }

        private function getOrderDataForGateway_currency ($order) {
            $order_id = $order->get_id();
            $currency = $order->get_currency();
            return apply_filters( 'WC_Ewano_Currency', $currency, $order_id );
        }

        private function getOrderDataForGateway_amount ($order, $currency) {
            $amount = intval($order->get_total());
            $amount = apply_filters( 'woocommerce_order_amount_total_IRANIAN_gateways_before_check_currency', $amount, $currency );
            if ( strtolower($currency) == strtolower('IRT') || strtolower($currency) == strtolower('TOMAN')
                || strtolower($currency) == strtolower('Iran TOMAN') || strtolower($currency) == strtolower('Iranian TOMAN')
                || strtolower($currency) == strtolower('Iran-TOMAN') || strtolower($currency) == strtolower('Iranian-TOMAN')
                || strtolower($currency) == strtolower('Iran_TOMAN') || strtolower($currency) == strtolower('Iranian_TOMAN')
                || strtolower($currency) == strtolower('تومان') || strtolower($currency) == strtolower('تومان ایران')
            )
                $amount = $amount*10;
            else if ( strtolower($currency) == strtolower('IRHT') )
                $amount = $amount*1000*10;
            else if ( strtolower($currency) == strtolower('IRHR') )
                $amount = $amount*1000;

            $amount = apply_filters( 'woocommerce_order_amount_total_IRANIAN_gateways_after_check_currency', $amount, $currency );
            $amount = apply_filters( 'woocommerce_order_amount_total_IRANIAN_gateways_irr', $amount, $currency );
            $amount = apply_filters( 'woocommerce_order_amount_total_Mellat_gateway', $amount, $currency );

            return $amount;
        }

        private function getDataForMakeOrderRequest ($order_id) {
            $currentUser = wp_get_current_user();
            $username = $currentUser->user_login;
            $orderDataForGateway = $this->getOrderDataForGateway($order_id);
            $totalDiscountOfOrder = $orderDataForGateway['total_discount'];
            $items = array_map(function($n) {
                return [
                    'name' => $n['name'],
                    'quantity' => $n['quantity'],
                    'unit_price' => $n['baseUnitPrice']
                ];
                }, $orderDataForGateway['items']);

            return [
                'msisdn' => $username,
                'id' => $order_id,
                'description' => date('ymdHis'),
                'discountAmount' => $totalDiscountOfOrder,
                'items' => $items
            ];
        }
    }
}
add_action('plugins_loaded', 'Load_Ewano_Gateway');
