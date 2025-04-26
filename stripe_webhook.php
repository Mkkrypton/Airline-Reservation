<?php
require 'vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51RHlJx2MSd0udS2kBrQrkWaE4T0699KWRr8otM3BS32NdA1d0n7AiQK1hQjbOjpIntdI224vUP8eWvXi9v5rKX8800EEdHCXHf'); // Replace with your Stripe secret key

// You can find your endpoint's secret in your webhook settings
$endpoint_secret = 'whsec_5NbbDhtuIAQK810MhTEmeqLQFtENycN1';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(400);
    exit();
}

// Handle the checkout.session.completed event
if ($event->type == 'checkout.session.completed') {
    $session = $event->data->object;

    // Fulfill the purchase, update booking as paid
    $client_reference_id = $session->client_reference_id; // You can pass booking id or user id here if needed

    // Connect to DB and update booking payment status
    include_once 'dbconnect2.php';

    // Example: update all unpaid bookings for the user as paid
    $user = $client_reference_id; // Assuming client_reference_id is username
    if ($user) {
        $sql = "UPDATE book SET paid = 1 WHERE username = '$user' AND paid = 0";
        mysqli_query($con, $sql);
    }

    mysqli_close($con);
}

http_response_code(200);
?>
