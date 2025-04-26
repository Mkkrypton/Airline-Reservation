<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Log errors to demo/error_log.txt
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

require '../vendor/autoload.php';

session_start();

$user = $_SESSION['user'] ?? null;

if (!$user) {
    // Return JSON error instead of redirect for AJAX request
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

// Read raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$total_amount = $data['total_amount'] ?? 0;

if (!$total_amount || $total_amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid amount']);
    exit();
}

\Stripe\Stripe::setApiKey('sk_test_51RHlJx2MSd0udS2kBrQrkWaE4T0699KWRr8otM3BS32NdA1d0n7AiQK1hQjbOjpIntdI224vUP8eWvXi9v5rKX8800EEdHCXHf'); // Replace with your Stripe secret key

// Create a new Stripe Checkout Session
$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'inr',
            'product_data' => [
                'name' => 'Airline Reservation Payment',
            ],
            'unit_amount' => intval($total_amount * 100), // amount in paise
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => 'http://localhost/AirlineReservation_com/demo/pay.php?status=success&payment_method=stripe',
    'cancel_url' => 'http://localhost/AirlineReservation_com/demo/pay.php?status=cancel',
    'client_reference_id' => $user,
]);

header('Content-Type: application/json');
echo json_encode(['id' => $session->id]);
exit();
?>
