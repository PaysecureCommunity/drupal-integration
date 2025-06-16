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
use Drupal\Core\Link;
use Drupal\Core\Url;

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

    if (!$transaction_id || !$status) {
        \Drupal::logger('hello_world')->warning('Missing transaction ID or status in return.');
        return [
            '#type' => 'markup',
            '#markup' => $this->t('Missing transaction ID or status.'),
        ];
    }

    \Drupal::logger('hello_world')->notice('Payment return received: @id - @status', [
        '@id' => $transaction_id,
        '@status' => $status,
    ]);

     return [
        '#type' => 'markup',
        '#markup' => $this->t('Payment status for Transaction ID: @id and Status:@status', [
            '@id' => $transaction_id,
            '@status' => $status,
        ]),
    ];
}

    public function paymentReturnUI() {
  $transaction_id = 'TX123';  // hardcoded for demo
  $status = 'success';

  \Drupal::logger('hello_world')->notice('Payment return UI shown: @id - @status', [
    '@id' => $transaction_id,
    '@status' => $status,
  ]); 

  return [
    '#type' => 'markup',
    '#markup' => $this->t('Payment status for Transaction ID: @id and Status: @status', [
      '@id' => $transaction_id,
      '@status' => $status,
    ]),
  ];
}

     public function paymentForm() {
        $form = \Drupal::formBuilder()->getForm('Drupal\hello_world\Form\PaymentForm');
        return $form;
     }

     public function fieldFormatter() {
  return [
    '#markup' => $this->t('This is the HelloWorld Field Formatter page.'),
  ];
}

 public function fieldDemo() {
    return [
      '#markup' => $this->t('This page demonstrates the Hello World Field type.'),
    ];
  }

public function fieldWidget() {
  return [
    '#type' => 'markup',
    '#markup' => $this->t('This is the Hello World Field Widget.'),
  ];
}

public function testPaysecureService() {
    // Updated payload based on real PaySecure API request
    $payload = [
        "client" => [
            "email" => "arun@gmail.com",
            "street_address" => "10 New Burlington Street Apt. 214",
            "city" => "london",
            "full_name" => "Asta",
            "zip_code" => "302144",
            "country" => "ZA",
            "date_of_birth" => "1970-07-10",
            "stateCode" => "QLD",
            "phone" => "+0-7755564318"
        ],
        "purchase" => [   
            "currency" => "usd",
            "products" => [
                [
                    "name" => "a2",
                    "price" => 1
                ]
            ]
        ],
        "brand_id" => "30f7ce6e-3b7e-46a2-9b50-484fc55be689",
        "success_redirect" => "https://api.paysecure.net/getResponse.jsp?issucces=true",
        "failure_redirect" => "https://api.paysecure.net/getResponse.jsp?issucces=false"
    ];

    $result = $this->paysecureService->sendPaymentRequest($payload);

    // Redirect if checkout URL exists:
      if (!empty($result['checkout_url'])) {
        return new RedirectResponse($result['checkout_url']);
      }

    return [
        '#type' => 'markup',
        '#markup' => '<pre>' . htmlspecialchars(print_r($result, TRUE)) . '</pre>',
    ];
}

public function setKeyDemo(Request $request) {
  $apiKey = $request->query->get('key');

  if (!$apiKey) {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('API key is missing in the URL. Use ?key=YOUR_KEY'),
    ];
  }

  $this->paysecureService->setApiKey($apiKey);

  return [
    '#type' => 'markup',
    '#markup' => $this->t('API key has been set to: @key', ['@key' => $apiKey]),
  ];
}

public function setEndpointDemo(Request $request) {
  $endpoint = $request->query->get('url');

  if (!$endpoint) {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Endpoint URL is missing. Use ?url=https://your-endpoint.com'),
    ];
  }

  $this->paysecureService->setEndpoint($endpoint);

  return [
    '#type' => 'markup',
    '#markup' => $this->t('Endpoint has been set to: @url', ['@url' => $endpoint]),
  ];
}

public function purchaseUrlDemo() {
    $transaction_id = 'TXN' . time();
    $amount = 199.99;
    $currency = 'USD';

    $url = $this->paysecureService->getPurchaseUrl($transaction_id, $amount, $currency);

    if (empty($url)) {
        return [
            '#markup' => $this->t('Could not generate purchase URL. Check configuration.'),
        ];
    }

    // Display it as a link
    return [
        '#markup' => $this->t('Purchase URL: <a href="@url" target="_blank">@url</a>', ['@url' => $url]),
    ];
}

public function paymentSuccess() {
    return [
        '#markup' => $this->t('Payment was successful!'),
    ];
}

public function paymentFailure() {
    return [
        '#markup' => $this->t('Payment failed. Please try again.'),
    ];
}

}