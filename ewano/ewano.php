<?php
/*
 Plugin Name: Ewano
 Plugin URI: https://your-plugin-uri.com/
 Description: Injects a custom JavaScript file in the header for custom state.
 Version: 1.0
 Author: Krasus
 Author URI: https://your-website.com/
 */
function initialize_ewano_gateway() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

//    class WC_Gateway_Ewano extends WC_Payment_Gateway {
//        // Your code goes here
//    }

    function add_ewano_gateway( $methods ) {
        include_once('EwanoGateway.php');
        $methods[] = 'WC_Gateway_Ewano';
        return $methods;
    }

    add_filter( 'woocommerce_payment_gateways', 'add_ewano_gateway' );
}

add_action( 'plugins_loaded', 'initialize_ewano_gateway', 11 );


//add_action('init', 'initEwano');
//add_action('wp_head', 'injectEwanoScript');
//add_action('plugins_loaded', 'initEwanoGatewayClass');
//    $userId = get_current_user_id(); // or replace with specific user ID
//    $openOrders = getCurrentUserOpenOrders($userId);
//

//function initEwano () {
////    if (!isFromEwano()) {
////        return null;
////    }
//
////    var_dump($openOrders);
////    foreach($openOrders as $order) {
////        var_dump('$order' . $order . '<br />');
//////        echo 'Order ID: ' . $order->ID . '<br />';
////    }
//
//
////    if (isHomePage()) {
////        EwanoAutoLogin('ewano2');
////    }
//}
