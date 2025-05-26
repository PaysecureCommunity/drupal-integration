<?php
/**
 * we use backslashes (\) in namespace means this file is part of Drupal\hello_world\Controller namespace
 * controller created for handling business logic for custom page/ routes in Drupal
 */
namespace Drupal\hello_world\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns a simple hello world message.
 */
class HelloController extends ControllerBase {
    /**
     * callback for /hello route
     * 
     * @return array
     * Render array with a simple message.  
     */
    public function hello() {
        return [
            '#type' => 'markup',
            '#markup' => $this->t('Hello World from controller!'),
        ];
    }
} 
?>