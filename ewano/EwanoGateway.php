<?php

class WC_Gateway_Ewano extends WC_Payment_Gateway {

    public function __construct() {

        $this->id                 = 'ewano';
        $this->icon               = apply_filters('woocommerce_ewano_icon', 'https://nodes.alaatv.com/upload/alaaPages/2023-11/ewano_logo.png');
        $this->has_fields         = false;
        $this->method_title       = __( 'Ewano Payment', 'woocommerce' );
        $this->method_description = __( 'Allows payments with ewano gateway.', 'woocommerce' );

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title        = $this->get_option( 'title' );
        $this->description  = $this->get_option( 'description' );
        $this->instructions = $this->get_option( 'instructions', $this->description );

        // Actions
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

        // You can also register a webhook here for 'ewano' callbacks
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'       => __('Enable/Disable', 'woocommerce'),
                'label'       => __('Enable Ewano Payment', 'woocommerce'),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'true'
            ),
            'title' => array(
                'title'       => __('Title', 'woocommerce'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                'default'     => __('Ewano Payment', 'woocommerce'),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __('Description', 'woocommerce'),
                'type'        => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                'default'     => __('Pay with your Ewano account.', 'woocommerce')
            ),
            'api_key' => array(
                'title'       => __('API Key', 'woocommerce'),
                'type'        => 'password',
                'description' => __('This is the API key you received from Ewano when you registered for an account.', 'woocommerce'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            // You can add additional configuration options here as needed
        );
    }

    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );

        // You would collect the payment details needed by Ewano API
        $payment_args = array(
            'api_key'   => $this->api_key, // The API key you have set in init_form_fields()
            'order_id'  => $order_id,
            'amount'    => $order->get_total(),
            'currency'  => get_woocommerce_currency(),
            'customer'  => array(
                'email'     => $order->get_billing_email(),
                'first_name'=> $order->get_billing_first_name(),
                'last_name' => $order->get_billing_last_name(),
                // any other customer details required by the API
            ),
            // any other payment details required by the Ewano API
        );

        // Assuming Ewano API has a function like this to process payment
        $response = ewano_process_payment( $payment_args );

        // Handle response
        if ( $response->success ) {
            // Payment was successful

            // Store transaction ID for reference e.g. $response->transaction_id
            update_post_meta( $order_id, '_transaction_id', $response->transaction_id );

            // Add order note regarding success
            $order->add_order_note( 'Ewano payment completed with Transaction ID: ' . $response->transaction_id );

            // Mark order as Paid
            $order->payment_complete();

            // Empty the cart
            WC()->cart->empty_cart();

            // Return thanks page redirect
            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order )
            );
        } else {
            // Payment failed, add error message for the customer
            wc_add_notice( 'Payment error: ', 'error' . $response->message );

            // Add order note regarding failure
            $order->add_order_note( 'Ewano payment failed: ' . $response->message );

            // No redirect because payment failed
            return array(
                'result'   => 'fail',
                'redirect' => ''
            );
        }
    }
}
