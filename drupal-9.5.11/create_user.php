<?php

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;
use Drupal\user\Entity\User;

require_once __DIR__ . '/autoload.php';
$autoloader = require_once __DIR__ . '/core/includes/bootstrap.inc';

$request = Request::createFromGlobals();
$kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
$kernel->boot();
$kernel->prepareLegacyRequest($request);

// CONFIGURATION — SET YOUR NEW USER DETAILS
$username = 'newadmin';
$email = 'newadmin@example.com';
$password = 'newpassword123';

// Check if username already exists
$existing_user = \Drupal\user\Entity\User::loadByUsername($username);
if ($existing_user) {
    echo "❌ User '$username' already exists.\n";
    exit;
}

// Create the new user
$user = User::create([
    'name' => $username,
    'mail' => $email,
    'pass' => $password,
    'status' => 1,
]);

$user->addRole('administrator'); // Give admin access
$user->save();

echo "✅ User '$username' created successfully with password '$password'\n";