<?php

namespace Rocapay;


class Rocapay
{

    /**
     * @var string API Auth Token
     */
    private $apiAuthToken;

    /**
     * @var string API Base URL
     */
    private $apiBaseUrl = 'https://rocapay.com/api';

    public function __construct($apiAuthToken)
    {
        $this->apiAuthToken = $apiAuthToken;
    }

    /**
     * Get a list of supported cryptocurrencies
     *
     * @return array
     */
    public function getCryptoCurrencies()
    {
        $url = $this->apiBaseUrl . '/crypto-currencies';

        $response = $this->executeRequest($url);

        return $response['cryptoCurrencies'];
    }

    /**
     * Create a payment
     *
     * @param float $amount
     * @param string $fiatCurrency
     * @param string $callbackUrl
     * @param string $description
     * @param string $cryptoCurrency
     * @return array
     */
    public function createPayment($amount, $fiatCurrency, $callbackUrl = '', $description = '', $cryptoCurrency = '')
    {
        $url = $this->apiBaseUrl . '/payment';

        $params = array(
            'token' => $this->apiAuthToken,
            'amount' => $amount,
            'currency' => $fiatCurrency,
            'cryptoCurrency' => $cryptoCurrency,
            'callbackUrl' => $callbackUrl,
            'description' => $description
        );

        return $this->executeRequest($url, true, $params);
    }

    /**
     * Get a payment
     *
     * @param $paymentId
     * @return array
     */
    public function checkPayment($paymentId)
    {
        $url = $this->apiBaseUrl . '/payment-type';

        $params = array(
            'token' => $this->apiAuthToken,
            'paymentId' => $paymentId
        );

        return $this->executeRequest($url, true, $params);
    }

    /**
     * Make CURL Request
     *
     * @param $url
     * @param bool $isPost
     * @param null $params
     * @return array
     */
    private function executeRequest($url, $isPost = true, $params = null)
    {
        $curl = curl_init();

        $curlOptions = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false
        );

        if ($isPost) {
            $curlOptions[CURLOPT_POST] = true;
            $curlOptions[CURLOPT_POSTFIELDS] = http_build_query($params);
        }

        curl_setopt_array($curl, $curlOptions);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }
}