<?php

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;
use Drupal\user\Entity\User;

define('DRUPAL_ROOT', getcwd());
require_once DRUPAL_ROOT . '/autoload.php';
require_once DRUPAL_ROOT . '/core/includes/bootstrap.inc';

$request = Request::createFromGlobals();
$kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
$kernel->boot();
\Drupal::setContainer($kernel->getContainer());

try {
    $user = User::load(1); // Load admin user (UID 1)
    if ($user) {
        $user->setUsername('admin');
        $user->setPassword('admin123'); // New password
        $user->save();

        \Drupal::service('flood')->clear('user.failed_login_user', 1);
        \Drupal::service('flood')->clear('user.failed_login_ip', $request->getClientIp());

        echo "✅ Admin password reset to 'admin123'. Try logging in.";
    } else {
        echo "❌ Could not load admin user.";
    }
} catch (Exception $e) {
    echo "❌ Exception occurred: " . $e->getMessage();
}