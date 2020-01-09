<?php
/**
 * Plugin Name: RocaPay WooCommerce Payment Gateway
 * Plugin URI: https://rocapay.com/pages/integrations
 * Description: A payment gateway for using RocaPay's services (https://rocapay.com).
 * Version: 1.0.0
 * Author: RocaPay
 * License: MIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check if WooCommerce is installed
if ( ! in_array( 'woocommerce/woocommerce.php',
	apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

// Register the class as a WooCommerce gateway
add_filter( 'woocommerce_payment_gateways', 'rocapay_add_gateway_class' );
function rocapay_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_RocaPay_Gateway';

	return $gateways;
}

add_action( 'woocommerce_api_rocapay/payment/callback', 'rocapay_postback_url_handler' );
function rocapay_postback_url_handler() {
    $requestContent = json_decode( file_get_contents( 'php://input' ), true );
    $wcOrderId = (int)filter_var( $requestContent['description'], FILTER_SANITIZE_NUMBER_INT );
    $order = wc_get_order( $wcOrderId );

    if ($order === false) {
        wc_add_notice('Order ' . $wcOrderId . ' does not exists', 'error' );
        return;
    }

    $paymentIdFromRocapay = $requestContent['transaction_id'];
    $paymentIdFromWc = get_post_meta( $wcOrderId, 'rocapay_payment_id', true );

    if (empty( $paymentIdFromWc ) || $paymentIdFromRocapay !== $paymentIdFromWc) {
        wc_add_notice( "Tokens don't match.", 'error' );
        return;
    }

    if ($requestContent['status'] !== 'success') {
        $order->update_status( 'failed' );
        return;
    }

    $order->payment_complete();
}

add_action( 'plugins_loaded', 'rocapay_init_gateway_class' );
function rocapay_init_gateway_class() {

    require_once(__DIR__ . '/includes/Rocapay.php');

	class WC_Rocapay_Gateway extends WC_Payment_Gateway {

	    private $apiAuthToken;

	    private $rocapaySdk;

		public function __construct() {
			$this->id                 = 'rocapay';
			$this->icon               = '';
			$this->has_fields         = false;
			$this->method_title       = 'RocaPay';
			$this->method_description = 'RocaPay Payment Gateway';

            $this->init_form_fields();
            $this->init_settings();
            $this->init_rocapay_sdk();

			add_action(
				'woocommerce_update_options_payment_gateways_' . $this->id,
				array( $this, 'process_admin_options' )
			);
		}

		public function init_settings() {
			parent::init_settings();
			$this->title        = ! empty( $this->settings['title'] ) ? $this->settings['title'] : $this->form_fields['title']['default'];
			$this->description  = ! empty( $this->settings['description'] ) ? $this->settings['description'] : $this->form_fields['description']['default'];
			$this->apiAuthToken = ! empty( $this->settings['api_auth_token'] ) ? $this->settings['api_auth_token'] : $this->form_fields['api_auth_token']['default'];
		}

		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'      => array(
					'title'       => 'Enable/Disable',
					'label'       => 'Enable the RocaPay Payment Gateway',
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no',
				),
				'title'        => array(
					'title'       => 'Title',
					'type'        => 'text',
					'description' => 'This is the title which the user sees during checkout.',
					'default'     => 'RocaPay',
					'placeholder' => 'RocaPay',
				),
				'description'  => array(
					'title'       => 'Description',
					'type'        => 'textarea',
					'description' => 'This is the description which the user sees during checkout.',
					'default'     => 'Pay with cryptocurrency through RocaPay.',
					'placeholder' => 'Pay with cryptocurrency through RocaPay.',
				),
				'api_auth_token'    => array(
					'title'       => 'API Token',
					'description' => 'This is the token that is given to you in the widget menu of your RocaPay dashboard.',
					'type'        => 'text',
                    'default'     => '',
				),
			);
		}

		public function process_payment( $order_id ) {
            $order        = wc_get_order( $order_id );
			$amount       = $order->get_total();
			$fiatCurrency = $order->get_currency();
			$callBackUrl  = $this->get_return_url( $order );
			$description  = 'Order: ' . $order->get_id();

			$rocapayPayment = $this->rocapaySdk->createPayment( $amount, $fiatCurrency, $callBackUrl, $description );

			if ( isset($rocapayPayment['status']) && $rocapayPayment['status'] === 'success' ) {
			    update_post_meta( $order_id, 'rocapay_payment_id', $rocapayPayment['paymentId'] );

				return array(
					'result'   => 'success',
					'redirect' => $rocapayPayment['paymentUrl'],
				);
			}

			wc_add_notice( 'An unexpected error occurred. Please contact the site administrator.', 'error' );

			return array(
			    'result' => 'failed',
            );
		}

		private function init_rocapay_sdk() {
            $this->rocapaySdk = new Rocapay\Rocapay( $this->apiAuthToken );
        }
	}
}
