<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_status'] !== 'active') {
    header('Location: ../../login.php');
    exit();
}

// Check if user is admin, if not redirect to access denied page
if ($_SESSION['user_type'] !== 'admin') {
    header('Location: ../../access_denied.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> (Admin)</h1>
                <p>This is the admin dashboard.</p>
                <a href="../../logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>
