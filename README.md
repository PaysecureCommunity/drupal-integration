# Drupal PaySecure Integration Module

This module provides integration with the PaySecure payment gateway for drupal 9.

## Features

- Admin configuration form for API key and Endpoint
- Payment form to send requests to PaySecure
- Route handling for payment return  URLs
- Modular service-based architecture

## Requirements:

- Drupal 9.5.x
- Guzzle (included with Druapl core)

## Installation:
1. Place the module in:
`web/modules/custom/hello_world` (or `modules/custom/hello_world` depending on your setup)
2. Enable the module:
- using the admin UI: Go to **Extend** → search for "Hello World" and enable it. 

## Configuration

1. Navigate to:
`/admin/config/development/hello-world`
2. Enter your PaySecure API Key and Endpoint.
3. Save the Configuration.

## Usage

- Go to `/hello/payment-form` to open the payment form.
- On successful transaction, the return is handled by: `/hello/payment-return`.

## Notes

- Make sure your API Key and Endpoint are valid and reachable.
- This module uses Guzzle via Drupal's service container for API calls.

## Maintainers

- admin <admin@example.com> 
