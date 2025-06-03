<?php

namespace Drupal\hello_world\Service;

// ConfigFactoryInterface located in Drupal's core module: C:\xampp\htdocs\drupal-integration\drupal-9.5.11\core\lib\Drupal\Core\Config\ConfigFactoryInterface.php
// ClientInterface located in C:\xampp\htdocs\drupal-integration\drupal-9.5.11\vendor\guzzlehttp\guzzle\src\ClientInterface.php
// LoggerInterface located in \vendor\psr\log\Psr\Log\LoggerInterface.php
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

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
    public function __construct(ConfigFactoryInterface $config_factory, ClientInterface $http_client, LoggerInterface $logger) {
        $this->config = $config_factory->get('hello_world.settings'); // get() method fetches value of hello_world.settings.yml
        $this->httpClient = $http_client; // Guzzle HTTP client used to make API requests
        $this->logger = $logger;
    }
    
    /**
     * Sends a POST request to the PaySecure API.
     * 
     * @param array $payload
     * The data to send.
     * 
     * @return array
     * The decoded response or error info.
     */
    public function sendPaymentRequest(array $payload): array {
        $api_key = $this->config->get('paysecure_api_key');
        $endpoint = $this->config->get('paysecure_endpoint');

        try {
            $response = $this->httpClient->post($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
        ],
        'json' => $payload,
        'timeout' => 30,
    ]);

    return json_decode($response->getBody()->getContents(), TRUE);

}
catch (\Exception $e) {
    $this->logger->error('PaySecure API request failed: @message', ['@message' => $e->getMessage()]);
    return [
        'error' => TRUE,
        'message' => $e->getMessage() 
    ];
}
    }
}  
