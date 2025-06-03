<?php
/**
 * we use backslashes (\) in namespace means this file is part of Drupal\hello_world\Controller namespace
 * controller created for handling business logic for custom page/ routes in Drupal
 */
namespace Drupal\hello_world\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\hello_world\Service\PaysecureService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns responses for Hello World routes.
 */
class HelloController extends ControllerBase {
    /**
     * The PaysecureService instance.
     * 
     * @var \Drupal\hello_world\Service\PaysecureService
     */
    protected $paysecureService;

    /**
     * Constructs a HelloController object.
     * 
     * @param \Drupal\hello_world\Service\PaysecureService $paysecure_service
     * The PaySecure service.
     */
    public function __construct(PaysecureService $paysecure_service) {
        $this->paysecureService = $paysecure_service;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('hello_world.paysecure_service')
        ); 
    }

    /**
     * Basic hello world message.
     */
    public function hello() {
        return [
            '#type' => 'markup',
            '#markup' => $this->t('Hello World from controller!'),
        ];
    }

    /**
     * Handles the PaySecure return URL callback.
     */
    public function paymentReturn(Request $request) {
        $transaction_id = $request->query->get('transaction_id');
        $status = $request->query->get('status');
        
        if(!$transaction_id || !$status) {
            return [
                '#type' => 'markup',
                '#markup' => $this->t('Missing transaction ID or status.'),
            ];
        }

        return [
            '#type' => 'markup',
            '#markup' => $this->t('Payment status for Transaction ID: @id and Status:@status', [
                '@id' => $transaction_id,
                '@status' => $status,
            ]),
        ];
     }
     public function paymentForm() {
        $form = \Drupal::formBuilder()->getForm('Drupal\hello_world\Form\PaymentForm');
        return $form;
     }
} 
