<?php
session_start();

// Include database configuration
require_once '../../db/db_connection.php';

// Check if user is logged in and active
if (!isset($_SESSION['user_id']) || $_SESSION['user_status'] !== 'active') {
    header('Location: ../../login.php');
    exit();
}

// Check if attendance_id is provided
$attendance_id = $_GET['attendance_id'] ?? 0;
if (!$attendance_id) {
    header('Location: checkin.php');
    exit();
}

// Initialize variables
$error_message = '';
$success_message = '';
$attendance = null;

try {
    $pdo = getDBConnection();

    // Verify the attendance record belongs to the user
    $stmt = $pdo->prepare("
        SELECT * FROM attendance
        WHERE id = ? AND user_id = ? AND checkout_time IS NOT NULL
    ");
    $stmt->execute([$attendance_id, $_SESSION['user_id']]);
    $attendance = $stmt->fetch();

    if (!$attendance) {
        header('Location: checkin.php');
        exit();
    }

    // Check if summary already exists
    $stmt = $pdo->prepare("SELECT * FROM work_summaries WHERE attendance_id = ?");
    $stmt->execute([$attendance_id]);
    $existing_summary = $stmt->fetch();

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $summary = trim($_POST['summary'] ?? '');

        if (empty($summary)) {
            $error_message = 'Work summary is required';
        } else {
            if ($existing_summary) {
                // Update existing summary
                $stmt = $pdo->prepare("
                    UPDATE work_summaries
                    SET summary = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$summary, $existing_summary['id']]);
            } else {
                // Insert new summary
                $stmt = $pdo->prepare("
                    INSERT INTO work_summaries (attendance_id, user_id, work_date, summary)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $attendance_id,
                    $_SESSION['user_id'],
                    date('Y-m-d', strtotime($attendance['checkin_time'])),
                    $summary
                ]);
            }

            $success_message = 'Work summary saved successfully!';
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
    <title>SMYRNA - Work Summary</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0">Daily Work Summary</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>

                        <div class="attendance-info mb-4">
                            <h5>Attendance Details</h5>
                            <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($attendance['checkin_time'])); ?></p>
                            <p><strong>Check-in:</strong> <?php echo date('h:i A', strtotime($attendance['checkin_time'])); ?> at <?php echo htmlspecialchars($attendance['checkin_location']); ?></p>
                            <p><strong>Check-out:</strong> <?php echo date('h:i A', strtotime($attendance['checkout_time'])); ?> at <?php echo htmlspecialchars($attendance['checkout_location']); ?></p>
                        </div>

                        <form method="POST">
                            <div class="form-group">
                                <label for="summary"><strong>Work Summary</strong></label>
                                <textarea class="form-control" id="summary" name="summary" rows="8"
                                          placeholder="Describe what you accomplished today, tasks completed, challenges faced, and plans for tomorrow..."
                                          required><?php echo isset($existing_summary['summary']) ? htmlspecialchars($existing_summary['summary']) : ''; ?></textarea>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-success btn-lg">Save Summary</button>
                                <a href="checkin.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
