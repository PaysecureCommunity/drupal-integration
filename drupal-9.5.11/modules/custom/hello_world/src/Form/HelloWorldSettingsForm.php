<?php

namespace Drupal\hello_world\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\hello_world\Service\PaysecureService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Markup as CoreMarkup;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Admin settings form for Hello World module.
 */
class HelloWorldSettingsForm extends ConfigFormBase {

  /**
   * The Paysecure service.
   *
   * @var \Drupal\hello_world\Service\PaysecureService
   */
  protected $paysecureService;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
 * The config factory service.
 *
 * @var \Drupal\Core\Config\ConfigFactoryInterface
 */
  protected $configFactory;

  /**
   * Constructs a new HelloWorldSettingsForm.
   */
  public function __construct(ConfigFactoryInterface $config_factory, PaysecureService $paysecureService,  StateInterface $state) {
    parent::__construct($config_factory);
    $this->paysecureService = $paysecureService;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */

   public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('hello_world.paysecure_service'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hello_world_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['hello_world.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get('hello_world.settings');

    $form['hello_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Hello Message'),
      '#default_value' => $config->get('hello.name'),
      '#description' => $this->t('Enter a default message to be used by Hello World Block.'),
    ];

    $form['paysecure_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PaySecure API Key'),
      '#default_value' => $this->state->get('hello_world.paysecure_api_key') ?? '',
      '#required' => TRUE,
    ];

    $form['paysecure_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PaySecure Endpoint'),
      '#default_value' => $this->state->get('hello_world.paysecure_endpoint') ?? '',
      '#required' => TRUE,
    ];

    // Add test button.
    $form['submit_test_api'] = [
      '#type' => 'submit',
      '#value' => $this->t('Test PaySecure API'),
      '#submit' => ['::submitTestApi'],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Submit handler for saving the form configuration.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    
    // Save hello name in config.
    $this->config('hello_world.settings')
      ->set('hello.name', $form_state->getValue('hello_name'))
      ->save();

      // Save API key and endpoint in state (not config).
      $this->state->set('hello_world.paysecure_api_key', $form_state->getValue('paysecure_api_key'));
      $this->state->set('hello_world.paysecure_endpoint', $form_state->getValue('paysecure_endpoint'));

       $this->messenger()->addStatus($this->t('Configuration saved.'));
  }

  /**
   * Submit handler for "Test PaySecure API" button.
   */
  public function submitTestApi(array &$form, FormStateInterface $form_state) {
    $payload = [
      "client" => [
        "email" => "test@example.com",
        "street_address" => "123 Test Lane",
        "city" => "Testville",
        "full_name" => "Test User",
        "zip_code" => "12345",
        "country" => "US",
        "date_of_birth" => "1990-01-01",
        "stateCode" => "CA",
        "phone" => "+1-123-456-7890"
      ],
      "purchase" => [
        "currency" => "USD",
        "products" => [
          ["name" => "Demo Product", "price" => 10]
        ]
      ],
      "brand_id" => "30f7ce6e-3b7e-46a2-9b50-484fc55be689",
      "success_redirect" => "http://localhost:808/drupal-integration/drupal-9.5.11/hello/payment-success",
      "failure_redirect" => "http://localhost:808/drupal-integration/drupal-9.5.11/hello/payment-failure",
    ];

     // Set credentials from state into the service before sending the request.
    $this->paysecureService->setApiKey($this->state->get('hello_world.paysecure_api_key'));
    $this->paysecureService->setEndpoint($this->state->get('hello_world.paysecure_endpoint'));

     // Send payment request.
    $response = $this->paysecureService->sendPaymentRequest($payload);

    // Display response in the admin UI.
    $this->messenger()->addMessage(CoreMarkup::create('<pre>' . print_r($response, TRUE) . '</pre>'));
  }
}