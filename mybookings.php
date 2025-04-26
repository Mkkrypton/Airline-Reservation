<?php
// Start the session
session_start();

if(!isset($_SESSION['user'])){
    header("Location: customersignin.html");
    exit();
}

$user = $_SESSION['user'];

include_once 'dbconnect2.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>My Bookings</title>
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
        <?php
        $user = $_SESSION['user'] ?? null;
        ?>
        <?php if ($user): ?>
            <a class="navbar-brand" href="homepage.php"><span class="glyphicon glyphicon-home"></span> Home</a>
        <?php else: ?>
            <a class="navbar-brand" href="homepage.html"><span class="glyphicon glyphicon-home"></span> Home</a>
        <?php endif; ?>
      </div>
      <div class="collapse navbar-collapse" id="myNavbar">
        <ul class="nav navbar-nav navbar-right">
          <li id="bookings">
            <a class="navbar-brand" href="mybookings.php"><span class="glyphicon glyphicon-list-alt"></span> My Bookings</a>
          </li>
<?php if (isset($_SESSION['user']) && !empty($_SESSION['user'])): ?>
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
    <h1>airease</h1>
    <p>We specialize in your air plan!</p>
  </div>

  <div class="container-fluid text-center">
    <div class="row content">
      <div class="col-sm-2 sidenav">
      </div>
      <div class="col-sm-8 text-left">
        <h1>My Bookings</h1>

<?php

$sql = "SELECT FL.number AS FLnumber, company, type, B.ID AS bookid, time, B.date, departure, d_time, arrival, a_time, C.name AS classname, capacity, price, paid
        FROM flight FL, class C, airplane AP, book B
        WHERE (FL.number = C.number) AND (B.flightno = C.number) AND (classtype = C.name) AND (FL.airplane_id = AP.ID)
        AND B.username = '$user' AND B.paid = 1
        ORDER BY time";

$result = mysqli_query($con, $sql);
$rowcount = mysqli_num_rows($result);

if ($rowcount == 0) {
    echo "<div class='alert alert-info'><strong>No bookings found.</strong></div>";
} else {
    echo "<div class='alert alert-info'>Your booked tickets:</div>";

    echo "<table class='table table-bordered table-striped table-hover'>
          <thead>
          <tr>
            <th>Time</th>
            <th>Flight</th>
            <th>Aircraft</th>
            <th>Date</th>
            <th>Departure</th>
            <th>Departure Time</th>
            <th>Arrival</th>
            <th>Arrival Time</th>
            <th>Class</th>
            <th>Price (₹)</th>
            <th>Status</th>
          </tr>
          </thead>";

    while ($row = mysqli_fetch_array($result)) {
        echo "<tbody><tr>";
        echo "<td>" . $row['time'] . "</td>";
        echo "<td>" . $row['FLnumber'] . "</td>";
        echo "<td>" . $row['company'] . " " . $row['type'] . "</td>";
        echo "<td>" . $row['date'] . "</td>";
        echo "<td>" . $row['departure'] . "</td>";
        echo "<td>" . $row['d_time'] . "</td>";
        echo "<td>" . $row['arrival'] . "</td>";
        echo "<td>" . $row['a_time'] . "</td>";
        echo "<td>" . $row['classname'] . "</td>";

        $price_inr = round($row['price'] );
        echo "<td>₹" . $price_inr . "</td>";

        $status = $row['paid'] == 1 ? "Paid" : "Pending";
        echo "<td>" . $status . "</td>";

        echo "</tr>";
    }
    echo "</tbody></table>";
}

mysqli_close($con);

?>

      </div>
    </div>
  </div>

  <footer class="container-fluid text-center">
    <a href="#signUpPage" title="To Top">
    <span class="glyphicon glyphicon-chevron-up"></span>
    </a>
    <p>© 2025 airease.com. All rights reserved.</p>
  </footer>

</body>
</html>
