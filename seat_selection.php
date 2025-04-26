<?php
session_start();
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header("Location: customersignin.html");
    exit();
}
// seat_selection.php
// Connect to database
include 'dbconnect.php';

$flight_id = $_POST['flightno'] ?? $_GET['flightno'] ?? 1; // Get flight id from POST or GET, default 1

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_seats = $_POST['selected_seats'] ?? [];
    if (!is_array($selected_seats)) {
        $selected_seats = [];
    }

    if (count($selected_seats) > 0) {
        // Mark selected seats as booked in the database
        foreach ($selected_seats as $seat) {
            // Check if seat is already booked
            $stmt = $conn->prepare("SELECT is_booked FROM seats WHERE flight_id = ? AND seat_number = ?");
            $stmt->bind_param("is", $flight_id, $seat);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($row['is_booked']) {
                    // Seat already booked, skip or handle error
                    continue;
                } else {
                    // Update seat to booked
                    $update = $conn->prepare("UPDATE seats SET is_booked = 1 WHERE flight_id = ? AND seat_number = ?");
                    $update->bind_param("is", $flight_id, $seat);
                    $update->execute();
                    $update->close();
                }
            } else {
                // Insert new seat booking record
                $insert = $conn->prepare("INSERT INTO seats (flight_id, seat_number, is_booked) VALUES (?, ?, 1)");
                $insert->bind_param("is", $flight_id, $seat);
                $insert->execute();
                $insert->close();
            }
            $stmt->close();
        }
        // Redirect to payment page after booking seats
        $flightno = $_POST['flightno'] ?? '';
        $classtype = $_POST['classtype'] ?? '';
        $date = $_POST['date'] ?? '';
        $price = $_POST['price'] ?? '';
        $selected_seats_str = implode(',', $selected_seats);
        // Pass data via GET or POST, here using GET for simplicity
        header("Location: pay.php?flightno=$flightno&classtype=$classtype&date=$date&price=$price&seats=$selected_seats_str");
        exit();
    }
}

// Fetch seat status for the flight
$seats = [];
$stmt = $conn->prepare("SELECT seat_number, is_booked FROM seats WHERE flight_id = ?");
$stmt->bind_param("i", $flight_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $seats[$row['seat_number']] = $row['is_booked'];
}
$stmt->close();

$conn->close();

// Seat map configuration
$rows = 10;
$cols = ['A', 'B', 'C', 'D', 'E', 'F'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Select Seats</title>
<style>
  body {
    font-family: Arial, sans-serif;
    padding: 20px;
  }
  .seat-map {
    display: grid;
    grid-template-columns: repeat(6, 40px);
    grid-gap: 10px;
    margin-bottom: 20px;
  }
  .seat {
    width: 40px;
    height: 40px;
    background-color: #90ee90;
    border: 1px solid #333;
    border-radius: 5px;
    cursor: pointer;
    text-align: center;
    line-height: 40px;
    user-select: none;
  }
  .seat.booked {
    background-color: #d3d3d3;
    cursor: not-allowed;
  }
  .seat.selected {
    background-color: #ffa500;
  }
  .legend {
    margin-top: 10px;
  }
  .legend span {
    display: inline-block;
    width: 20px;
    height: 20px;
    margin-right: 5px;
    vertical-align: middle;
    border: 1px solid #333;
    border-radius: 3px;
  }
  .legend .available {
    background-color: #90ee90;
  }
  .legend .booked {
    background-color: #d3d3d3;
  }
  .legend .selected {
    background-color: #ffa500;
  }
  button {
    padding: 10px 20px;
    font-size: 16px;
  }
</style>
</head>
<body>
<h1>Select Your Seats</h1>
<?php if (!empty($message)): ?>
  <p style="color: green; font-weight: bold;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<form method="POST" action="seat_selection.php" id="seatForm">
  <div class="seat-map" id="seatMap">
    <?php
    for ($r = 1; $r <= $rows; $r++) {
        foreach ($cols as $c) {
            $seat_id = $c . $r;
            $is_booked = isset($seats[$seat_id]) && $seats[$seat_id];
            $class = "seat" . ($is_booked ? " booked" : "");
            echo "<div class=\"$class\" data-seat=\"$seat_id\">$seat_id</div>";
        }
    }
    ?>
  </div>
  <input type="hidden" name="selected_seats[]" id="selectedSeats" />
  <input type="hidden" name="flightno" value="<?php echo htmlspecialchars($flight_id); ?>" />
  <input type="hidden" name="classtype" value="<?php echo htmlspecialchars($_POST['classtype'] ?? $_GET['classtype'] ?? ''); ?>" />
  <input type="hidden" name="date" value="<?php echo htmlspecialchars($_POST['date'] ?? $_GET['date'] ?? ''); ?>" />
  <input type="hidden" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? $_GET['price'] ?? ''); ?>" />
  <button type="submit">Book Selected Seats</button>
</form>
<script>
  const seatMap = document.getElementById('seatMap');
  const selectedSeatsInput = document.getElementById('selectedSeats');
  let selectedSeats = [];

  seatMap.addEventListener('click', function(e) {
    const target = e.target;
    if (!target.classList.contains('seat') || target.classList.contains('booked')) {
      return;
    }
    const seat = target.getAttribute('data-seat');
    if (selectedSeats.includes(seat)) {
      selectedSeats = selectedSeats.filter(s => s !== seat);
      target.classList.remove('selected');
    } else {
      selectedSeats.push(seat);
      target.classList.add('selected');
    }
    // Update hidden input with selected seats as array
    // Remove all previous inputs first
    const form = document.getElementById('seatForm');
    // Remove existing inputs for selected_seats[]
    const existingInputs = form.querySelectorAll('input[name="selected_seats[]"]');
    existingInputs.forEach(input => input.remove());
    // Add new inputs for each selected seat
    selectedSeats.forEach(seat => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'selected_seats[]';
      input.value = seat;
      form.appendChild(input);
    });
  });
</script>
</body>
</html>
