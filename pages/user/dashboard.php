<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_status'] !== 'active' || $_SESSION['user_type'] !== 'user') {
    header('Location: ../../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> (User)</h1>
    <p>This is the user dashboard.</p>
    <a href="../../index.php">Logout</a>
</body>
</html>