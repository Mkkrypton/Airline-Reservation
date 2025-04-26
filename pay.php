
<?php
// Start the session
session_start();

$user = $_SESSION['user'] ?? null;

if (!$user) {
    header("Location: customersignin.html");
    exit();
}

// Get total amount from POST or default to 0
$total_amount = $_POST['total_amount'] ?? 0;

// Calculate total amount based on price per seat and number of selected seats
$price_per_seat = isset($_GET['price']) ? floatval($_GET['price']) : 0;
$selected_seats = isset($_GET['seats']) ? explode(',', $_GET['seats']) : [];
$number_of_seats = count(array_filter($selected_seats, fn($seat) => trim($seat) !== ''));
if ($price_per_seat > 0 && $number_of_seats > 0) {
    $total_amount = $price_per_seat * $number_of_seats;
}

// Get booking details from POST if available
$flightno = $_POST['flightno'] ?? null;
$classtype = $_POST['classtype'] ?? null;
$date = $_POST['date'] ?? null;

// Check if redirected from payment success
$payment_status = $_GET['status'] ?? '';
$payment_method = $_GET['payment_method'] ?? '';

include_once 'dbconnect2.php';

// Insert booking record if flightno, classtype, and date are provided and booking not exists
if ($flightno && $classtype && $date && $payment_status !== 'success') {
    // Check if booking already exists for this user, flight, class, date, and unpaid
    $check_sql = "SELECT * FROM book WHERE username = '$user' AND flightno = '$flightno' AND classtype = '$classtype' AND date = '$date' AND paid = 0";
    $check_result = mysqli_query($con, $check_sql);
    if (mysqli_num_rows($check_result) == 0) {
        $time = date("Y-m-d H:i:s");
        $insert_sql = "INSERT INTO book (time, date, flightno, username, classtype, paid) VALUES ('$time', '$date', '$flightno', '$user', '$classtype', 0)";
        mysqli_query($con, $insert_sql);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Pay</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" type="image/x-icon" href="https://lh3.googleusercontent.com/-HtZivmahJYI/VUZKoVuFx3I/AAAAAAAAAcM/thmMtUUPjbA/Blue_square_A-3.PNG" />
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="forcompany.css">
  <link rel="stylesheet" href="homepage.css">
  <script src="login.js"> </script>
  <script src="jump.js"> </script>
</head>
<body>
  <nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="homepage.php"><span class="glyphicon glyphicon-home"></span> Home</a>
      </div>
      <div class="collapse navbar-collapse" id="myNavbar">
        <ul class="nav navbar-nav navbar-right">
<?php if (isset($_SESSION['user']) && !empty($_SESSION['user'])): ?>
          <li id="bookings">
            <a class="navbar-brand" href="mybookings.php"><span class="glyphicon glyphicon-list-alt"></span> My Bookings</a>
          </li>
          <li class="dropdown" id="old">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-user" id="wuser">Welcome!</span><span class="caret"></span></a>
            <ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu">
              <li><a href="showhistory.php">History</a></li>
              <li><a href="#" id="logout">Sign out</a></li>
            </ul>
          </li>
<?php else: ?>
          <li class="dropdown" id="new">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-user"> Sign in&nbsp;</span><span class="caret"></span></a>
            <ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu">
              <li><a href="signup.html">Register</a></li>
              <li class="dropdown-submenu">
                <a tabindex="-1" href="#">Sign in</a>
                <ul class="dropdown-menu">
                  <li><a href="customersignin.html">Customer Sign in</a></li>
                </ul>
              </li>
            </ul>
          </li>
<?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <div class="jumbotron text-center">
    <h1>airease.com</h1>
    <p>We specialize in your air plan!</p>
  </div>

  <div class="container">
<?php if ($payment_status === 'success'): ?>
    <h2>Payment Finished</h2>
    <p>Thank you for your payment.</p>
    <p>Payment method used: <strong><?php echo htmlspecialchars($payment_method); ?></strong></p>
    <div><img src="smile.jpg" alt="smile" id="smile"></div>
<?php else: ?>
    <h2>Payment Summary</h2>
    <p>Total Amount to Pay: <strong>₹<?php echo number_format($total_amount, 2); ?></strong></p>

    <button id="checkout-button" class="btn btn-primary">Pay with Stripe</button>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
      var stripe = Stripe('pk_test_51RHlJx2MSd0udS2k5Wjrh34N4Pt4gRyfWaF4J2VV3uBrbQp06LhCg3kRqqFDRyNVYq7LaRkapmxYd3WKTgJKOoJ900HHLITLcF'); // Replace with your Stripe publishable key

      var checkoutButton = document.getElementById('checkout-button');
      checkoutButton.addEventListener('click', function () {
        fetch('process_payment.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            total_amount: <?php echo json_encode($total_amount); ?>
          })
        })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(function (session) {
          if(session.error){
            alert(session.error);
            return;
          }
          return stripe.redirectToCheckout({ sessionId: session.id });
        })
        .then(function (result) {
          if (result && result.error) {
            alert(result.error.message);
          }
        })
        .catch(function (error) {
          console.error('Error:', error);
          alert('Payment failed: ' + error.message);
        });
      });
    </script>
<?php endif; ?>
  </div>

  <footer class="container-fluid text-center">
    <a href="#signUpPage" title="To Top">
      <span class="glyphicon glyphicon-chevron-up"></span>
    </a>
    <p>© 2025 airease.com. All rights reserved.</p>
  </footer>
</body>
</html>
