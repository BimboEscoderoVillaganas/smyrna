<?php
session_start();

// Include database configuration
require_once '../../db/db_connection.php';

// Check if user is logged in and active
if (!isset($_SESSION['user_id']) || $_SESSION['user_status'] !== 'active') {
    header('Location: ../../login.php');
    exit();
}

// Initialize variables
$error_message = '';
$success_message = '';
$current_attendance = null;
$has_checked_in = false;
$has_checked_out = false;
$can_add_summary = false;

// Get user's current position (you might want to store this in users table)
$user_position = "Employee"; // Default position

try {
    $pdo = getDBConnection();

    // Check if user has already checked in today
    $stmt = $pdo->prepare("
        SELECT * FROM attendance
        WHERE user_id = ? AND DATE(checkin_time) = CURDATE()
        ORDER BY checkin_time DESC
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $current_attendance = $stmt->fetch();

    if ($current_attendance) {
        $has_checked_in = true;
        $has_checked_out = !empty($current_attendance['checkout_time']);
        $can_add_summary = $has_checked_out;
    }

    // Process check-in
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkin'])) {
        $location = $_POST['location'] ?? '';

        if (empty($location)) {
            $error_message = 'Location is required';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO attendance (user_id, name, position, checkin_time, checkin_location)
                VALUES (?, ?, ?, NOW(), ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $_SESSION['user_name'],
                $user_position,
                $location
            ]);

            $success_message = 'Successfully checked in!';
            header('Location: checkin.php');
            exit();
        }
    }

    // Process check-out
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
        $location = $_POST['location'] ?? '';

        if (empty($location)) {
            $error_message = 'Location is required';
        } else if ($current_attendance && empty($current_attendance['checkout_time'])) {
            $stmt = $pdo->prepare("
                UPDATE attendance
                SET checkout_time = NOW(), checkout_location = ?
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([
                $location,
                $current_attendance['id'],
                $_SESSION['user_id']
            ]);

            $success_message = 'Successfully checked out!';
            header('Location: checkin.php');
            exit();
        }
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = 'System error. Please try again later.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SMYRNA - Check-in/Check-out</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        .location-info {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Employee Check-in/Check-out System</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                        <?php endif; ?>

                        <div class="location-info">
                            <h5>Current Location</h5>
                            <p id="location-display">Getting your location...</p>
                            <input type="hidden" id="location-input" name="location">
                        </div>

                        <div class="status-info mb-4">
                            <h5>Today's Status</h5>
                            <?php if ($has_checked_in): ?>
                                <span class="badge badge-success status-badge">Checked In: <?php echo date('h:i A', strtotime($current_attendance['checkin_time'])); ?></span>
                            <?php else: ?>
                                <span class="badge badge-secondary status-badge">Not Checked In</span>
                            <?php endif; ?>

                            <?php if ($has_checked_out): ?>
                                <span class="badge badge-info status-badge">Checked Out: <?php echo date('h:i A', strtotime($current_attendance['checkout_time'])); ?></span>
                            <?php elseif ($has_checked_in): ?>
                                <span class="badge badge-warning status-badge">Currently Working</span>
                            <?php endif; ?>
                        </div>

                        <form method="POST" class="mb-4">
                            <input type="hidden" name="location" id="form-location">

                            <?php if (!$has_checked_in): ?>
                                <button type="submit" name="checkin" class="btn btn-success btn-lg btn-block">
                                    Check In
                                </button>
                            <?php elseif (!$has_checked_out): ?>
                                <button type="submit" name="checkout" class="btn btn-primary btn-lg btn-block">
                                    Check Out
                                </button>
                            <?php endif; ?>
                        </form>

                        <?php if ($can_add_summary): ?>
                            <div class="text-center">
                                <a href="work_summary.php?attendance_id=<?php echo $current_attendance['id']; ?>" class="btn btn-info">
                                    Add Work Summary
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <h5>Recent Activities</h5>
                            <?php
                            try {
                                $stmt = $pdo->prepare("
                                    SELECT * FROM attendance
                                    WHERE user_id = ?
                                    ORDER BY checkin_time DESC
                                    LIMIT 5
                                ");
                                $stmt->execute([$_SESSION['user_id']]);
                                $recent_activities = $stmt->fetchAll();

                                if ($recent_activities) {
                                    echo '<ul class="list-group">';
                                    foreach ($recent_activities as $activity) {
                                        echo '<li class="list-group-item">';
                                        echo date('M j, Y', strtotime($activity['checkin_time'])) . ' - ';
                                        echo 'In: ' . date('h:i A', strtotime($activity['checkin_time']));
                                        if ($activity['checkout_time']) {
                                            echo ' | Out: ' . date('h:i A', strtotime($activity['checkout_time']));
                                        }
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                } else {
                                    echo '<p>No recent activities found.</p>';
                                }
                            } catch (PDOException $e) {
                                echo '<p>Unable to load recent activities.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Get user's location
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;

                        // Reverse geocoding to get address
                        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`)
                            .then(response => response.json())
                            .then(data => {
                                const address = data.display_name || 'Location acquired';
                                document.getElementById('location-display').textContent = address;
                                document.getElementById('location-input').value = address;
                                document.getElementById('form-location').value = address;
                            })
                            .catch(error => {
                                const locationText = `Lat: ${latitude.toFixed(6)}, Long: ${longitude.toFixed(6)}`;
                                document.getElementById('location-display').textContent = locationText;
                                document.getElementById('location-input').value = locationText;
                                document.getElementById('form-location').value = locationText;
                            });
                    },
                    function(error) {
                        document.getElementById('location-display').textContent = 'Unable to get location. Please enable location services.';
                        console.error('Geolocation error:', error);
                    }
                );
            } else {
                document.getElementById('location-display').textContent = 'Geolocation is not supported by this browser.';
            }
        }

        // Get location when page loads
        window.onload = getLocation;
    </script>
</body>
</html>

