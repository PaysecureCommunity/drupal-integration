<?php
namespace Drupal\hello_world\Form;

// importing necessary classes from Drupal Core
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\hello_world\Service\PaysecureService;
use Drupal\Core\Routing\TrustedRedirectResponse;

class PaymentForm extends FormBase {
    protected $paysecureService;

    public function __construct(PaysecureService $paysecure_service) {
        $this->paysecureService = $paysecure_service;
    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('hello_world.paysecure_service')
        );
    }

    public function getFormId() {
        return 'paysecure_payment_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
    $form['amount'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Amount'),
        '#required' => TRUE,
    ];

    // Add Payment Method selector
    $form['payment_method'] = [
        '#type' => 'radios', // or '#type' => 'select'
        '#title' => $this->t('Payment Method'),
        '#options' => [
            'upi' => $this->t('UPI'),
            'credit_card' => $this->t('Credit Card'),
            'interac' => $this->t('Interac'),
        ],
        '#default_value' => 'upi',
        '#required' => TRUE,
    ];

    $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Pay Now'),
    ];

    return $form;
}
     public function submitForm(array &$form, FormStateInterface $form_state) {
    $amount = (float) $form_state->getValue('amount');
    $payment_method = $form_state->getValue('payment_method');

    // Validate amount
    if ($amount <=0){
        \Drupal::messenger()->addError($this->t('Amount must be greater than zero.'));
        return;
    }

    // map to paysecure expected payment methods
    $method_map = [
        'upi' => 'UPI',
        'credit_card' => 'CREDIT_CARD',
        'interac' => 'INTERAC-ETRANSFER',
    ];

    $allowed_method = $method_map[$payment_method] ?? 'CREDIT_CARD';    

     // Build a full PaySecure payload
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
                [
                    "name" => ucfirst($payment_method) . " Payment",
                    "price" => (float) $amount
                ]
            ]
        ],
        "brand_id" => "30f7ce6e-3b7e-46a2-9b50-484fc55be689",
        "success_redirect" => "http://localhost:808/drupal-integration/drupal-9.5.11/hello/payment-success",
        "failure_redirect" => "http://localhost:808/drupal-integration/drupal-9.5.11/hello/payment-failure",

        "allowed_methods" => [$allowed_method],
    ];
    
    $response = $this->paysecureService->sendPaymentRequest($payload);

if (!empty($response['checkout_url'])) {

    $form_state->setResponse(new TrustedRedirectResponse($response['checkout_url']));
} else {
    // Display error if no URL is returned
    \Drupal::messenger()->addError($this->t('Failed to initiate payment.'));
}
}
}