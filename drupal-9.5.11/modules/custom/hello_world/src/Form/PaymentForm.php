<?php
namespace Drupal\hello_world\Form;

// importing necessary classes from Drupal Core
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\hello_world\Service\PaysecureService;

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
        $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Pay Now'),
        ];
        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $amount = $form_state->getValue('amount');
        $response = $this->paysecureService->sendPaymentRequest([
            'amount' => $amount,
            'currency' => 'USD', // Assuming USD, can be dynamic
            'description' => 'Test Payment'
        ]);

        \Drupal::messenger()->addMessage($this->t('API Response: @response', ['@response' => print_r($response, TRUE)]));
    }
}