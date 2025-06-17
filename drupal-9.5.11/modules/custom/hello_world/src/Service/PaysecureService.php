<?php

namespace Drupal\hello_world\Service;

// ConfigFactoryInterface located in Drupal's core module: C:\xampp\htdocs\drupal-integration\drupal-9.5.11\core\lib\Drupal\Core\Config\ConfigFactoryInterface.php
// ClientInterface located in C:\xampp\htdocs\drupal-integration\drupal-9.5.11\vendor\guzzlehttp\guzzle\src\ClientInterface.php
// LoggerInterface located in \vendor\psr\log\Psr\Log\LoggerInterface.php
use Drupal\Core\State\StateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\Component\Serialization\Json;

/**
 * Service to communicate with PaySecure API.
 */
class PaysecureService {

    /**
     * The Configuration Object.
     * 
     * @var \Drupal\Core\Config\Config
     */
    protected $config;  

    /**
     * The State service.
     *
     * @var \Drupal\Core\State\StateInterface
     */
    protected $state;

    /**
     * Guzzle HTTP client.
     * 
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;

    /**
     * Logger channel.
     * 
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructs the PaysecureService object.
     */

    public function __construct(ConfigFactoryInterface $config_factory, StateInterface $state, ClientInterface $http_client, LoggerInterface $logger) {
        $this->config = $config_factory->get('hello_world.settings'); // get() method fetches value of hello_world.settings.yml
         $this->state = $state;
        $this->httpClient = $http_client; // Guzzle HTTP client used to make API requests
        $this->logger = $logger;
    }

    /**
     * Gets the API key from state storage.
     *
     * @return string
     *   The API key or empty string if not found.
     */

    protected function getApiKey(): string {
        return $this->state->get('hello_world.paysecure_api_key') ?? '';
    }

    /**
     * Sets the API key in state storage.
     *
     * @param string $key
     */

    public function setApiKey(string $key) : void {
        $this->state->set('hello_world.paysecure_api_key', $key);
    }

    /**
     * Sets the endpoint in state storage.
     *
     * @param string $url
     */

    public function setEndpoint(string $url) {
  $this->state->set('hello_world.paysecure_endpoint', $url);
  $this->logger->info('PaySecure endpoint set to: @url', ['@url' => $url]);
}

/**
 * Builds and returns a purchase URL.
 *
 * @param string $transaction_id
 * @param float $amount
 * @param string $currency
 *
 * @return string
 *   A fully constructed purchase URL (for redirect or API use).
 */
public function getPurchaseUrl(string $transaction_id, float $amount, string $currency = 'USD'): string {
    $endpoint = rtrim($this->state->get('hello_world.paysecure_endpoint'), '/');
    $api_key = $this->getApiKey();

    if (empty($endpoint) || empty($api_key)) {
        $this->logger->error('Missing endpoint or API key for purchase URL generation.');
        return '';
    }

    $query = http_build_query([
        'transaction_id' => $transaction_id,
        'amount' => $amount,
        'currency' => $currency,
        'api_key' => $api_key,
    ]);

     return $endpoint . '?' . $query;
}

    /**
     * Validates the payment payload.
     *
     * @param array $payload
     * @return array
     */

     protected function validatePayload(array $payload): array {
    // Validate purchase section
    if (empty($payload['purchase']['currency']) || 
        !in_array(strtoupper($payload['purchase']['currency']), ['USD', 'EUR', 'GBP'])) {
        return [
            'valid' => FALSE,
            'message' => 'Invalid or unsupported currency',
        ];
    }

    // Validate product price
    if (empty($payload['purchase']['products'][0]['price']) || 
        !is_numeric($payload['purchase']['products'][0]['price']) || 
        $payload['purchase']['products'][0]['price'] <= 0) {
        return [
            'valid' => FALSE,
            'message' => 'Invalid product price',
        ];
    }

    // Validate brand_id
    if (empty($payload['brand_id'])) {
        return [
            'valid' => FALSE,
            'message' => 'Missing brand ID',
        ];
    }

    // Validate client email
    if (empty($payload['client']['email']) || !filter_var($payload['client']['email'], FILTER_VALIDATE_EMAIL)) {
        return [
            'valid' => FALSE,
            'message' => 'Invalid client email',
        ];
    }

    return ['valid' => TRUE];
}


    /**
     * Validates the API response.
     *
     * @param array $response
     * @return bool
     */
     
     protected function validateResponse($response) {
    \Drupal::logger('hello_world')->debug('<pre>@res</pre>', ['@res' => print_r($response, TRUE)]);
    
    if (!is_array($response)) {
        return FALSE;
    }
    if (!isset($response['status'])) {
        \Drupal::logger('hello_world')->debug('Missing Key: status');
        return FALSE;
    }
    
     if (empty($response['status'])){
            \Drupal::logger('hello_world')->debug('Status is empty');
            return FALSE;
           }

           if (!isset($response['transaction_id'])) {
        \Drupal::logger('hello_world')->debug("Missing Key: transaction_id");
    }

    if (empty($response['checkout_url'])) {
        \Drupal::logger('hello_world')->debug('Missing or empty checkout_url');
        return FALSE;
    }
    
           return TRUE;
        }
        
     /**
     * Sends a POST request to the PaySecure API.
     *
     * @param array $payload
     * @return array
     */
    
    public function sendPaymentRequest(array $payload): array {
        // Validate configuration
        $api_key = $this->getApiKey();
        $endpoint = $this->state->get('hello_world.paysecure_endpoint');

        if (empty($api_key) || empty($endpoint)) {
            $this->logger->error('Missing PaySecure configuration');
            return [
                'error' => TRUE,
                'message' => 'Invalid configuration'
            ];
        }

        // Validate payload
        $validation = $this->validatePayload($payload);
        if (!$validation['valid']) {
            $this->logger->error('Invalid payload: @message', ['@message' => $validation['message']]);
            return [
                'error' => TRUE,
                'message' => $validation['message']
            ];
        }

        // Log payment method before sending 
         $this->logger->info('Initiating payment request. Method: @method, Amount: @amount, Transaction ID: @id', [
    '@method' => $payload['payment_method'] ?? 'not specified',
    '@amount' => $payload['amount'] ?? 'not specified',
    '@id' => $payload['transaction_id'] ?? 'not specified',
]);

         try {
            $this->logger->info('PaySecure endpoint: @endpoint', ['@endpoint' => $endpoint]);
            $response = $this->httpClient->post($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 30,
                'verify' => TRUE,
                'http_errors' => TRUE,
            ]);

            $raw = $response->getBody()->getContents();
            $this->logger->debug('Raw response from PaySecure: @raw', ['@raw' => $raw]);
            $result = Json::decode($raw);

            if (!$this->validateResponse($result)) {
                throw new \RuntimeException('Invalid response from payment gateway');
            }

            $this->logger->info('Payment request successful: @trans_id', [
                '@trans_id' => $payload['transaction_id'] ?? 'unknown',
            ]);

            return $result;
}
catch (GuzzleException $e) {
            $this->logger->error('PaySecure API request failed: @message', [
                '@message' => $e->getMessage()
            ]);
            return [
                'error' => TRUE,
                'message' => 'Payment gateway communication error',
                'debug_message' => $e->getMessage()
            ];
}
catch (\Exception $e) {
            $this->logger->critical('Unexpected error in payment processing: @message', [
                '@message' => $e->getMessage()
            ]);
            return [
                'error' => TRUE,
                'message' => 'An unexpected error occurred',
                'debug_message' => $e->getMessage()
            ];
        }
    }
}
    
