# WooCommerce RocaPay Payment Gateway
This payment gateway integration enables you to use [RocaPay](https://rocapay.com/) with WooCommerce.
# Installation
## [WordPress Plugins Directory](https://wordpress.org/plugins/) (coming soon)
1. Navigate to `Plugins / Add New` in your admin panel
2. Enter `RocaPay WooCommerce`
3. Click the `Install Now` button
4. When finished installing, click the `Activate` button
## Direct Download
You can also download the gateway as a [ZIP File](https://github.com/RocaPayOfficial/rocapay-woocommerce-payment-gateway/releases/download/1.0.0/rocapay-woocommerce-payment-gateway-1.0.0.zip) and then use one of the methods below to install it. 
### WordPress Plugin Uploader
1. Navigate to `Plugins` in your admin panel
2. Click the `Upload Plugin` button and proceed to upload the ZIP file
3. Click the `Activate` button of the plugin
### FTP
1. Unpack the ZIP file in `YOUR_WORDPRESS_INSTALLATION_DIR/wp-content/plugins/`
2. Navigate to `Plugins` in your admin panel
3. Click the `Activate` button of the plugin
# Configuration
1. Sign up for a [RocaPay](https://rocapay.com/auth/register) account.
2. Create a widget and put in `YOUR_BASE_URL/wc-api/rocapay/payment/callback` in the Postback URL field.
3. Copy the API key, provided under the implementation tab of your newly created widget, to the payment's gateway configuration menu in the admin panel (`WooCommerce / Settings / Payments / Setup`). 
