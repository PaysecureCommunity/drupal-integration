<?php

namespace Drupal\Tests\hello_world\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\hello_world\Service\PaysecureService;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Tests the PaySecure payment service.
 *
 * @group hello_world
 */
class PaysecureServiceTest extends UnitTestCase {

  /**
   * The payment service.
   *
   * @var \Drupal\hello_world\Service\PaysecureService
   */
  protected $paymentService;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
  parent::setUp();

  // 1. Mock for Config object
  $config = $this->createMock(Config::class);
  $config->method('get')
    ->willReturnMap([
      ['paysecure_endpoint', 'https://test-api.paysecure.com'],
      ['paysecure_mode', 'test'],
    ]);

  // 2. ConfigFactory mock
  $config_factory = $this->createMock(ConfigFactoryInterface::class);
  $config_factory->method('get')
    ->with('hello_world.settings')
    ->willReturn($config);

  // 3. Other dependencies
  $http_client = $this->createMock(ClientInterface::class);
  $logger = $this->createMock(LoggerInterface::class);
  $state = $this->createMock(StateInterface::class);

  // 4. Assign service
    $this->paymentService = new PaysecureService(
      $config_factory,
      $http_client,
      $logger,
      $state 
    );
  }

  /**
   * Tests the payment request validation.
*/
public function testPaymentValidation(): void {
    $payload = [
      'amount' => 100.00,
      'currency' => 'USD',
      'transaction_id' => 'TEST_' . uniqid(),
    ];

    $result = $this->paymentService->sendPaymentRequest($payload);
    $this->assertIsArray($result);
  }

  /**
   * Tests invalid payment amount.
   */
  public function testInvalidAmount(): void {
    $payload = [
      'amount' => -100.00,
      'currency' => 'USD',
      'transaction_id' => 'TEST_' . uniqid(),
    ];

    $result = $this->paymentService->sendPaymentRequest($payload);
    $this->assertTrue($result['error']);
    $this->assertEquals('Invalid payment amount', $result['message']);
}

/**
     * Tests invalid currency.
     */
    public function testInvalidCurrency(): void {
        $payload = [
            'amount' => 100.00,
            'currency' => 'XXX',
            'transaction_id' => 'TEST_' . uniqid(),
        ];

        $result = $this->paymentService->sendPaymentRequest($payload);
        $this->assertTrue($result['error']);
        $this->assertEquals('Invalid or unsupported currency', $result['message']);
    }

    /**
     * Tests API error handling.
     */
    public function testApiError(): void {
        // Mock HTTP client to throw exception
        $http_client = $this->createMock(ClientInterface::class);
        $http_client->method('post')
            ->willThrowException(new GuzzleException());
        
        // ...test implementation...
    }
}