<?php
// Start session
session_start();

// Check if user was redirected from registration
if (!isset($_SESSION['success_message'])) {
    header('Location: index.php');
    exit();
}

$success_message = $_SESSION['success_message'];
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="author" content="Kodinger">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>SMYRNA - Account Pending Approval</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="assets/css/my-login.css">
</head>
<body class="my-login-page">
    <section class="h-100">
        <div class="container h-100">
            <div class="row justify-content-md-center h-100">
                <div class="card-wrapper">
                    <div class="brand">
                        <img src="assets/img/logo.jpg" alt="bootstrap 4 login page">
                    </div>
                    <div class="card fat">
                        <div class="card-body">
                            <h4 class="card-title text-center">Account Approval Pending</h4>
                            
                            <div class="alert alert-success" role="alert">
                                <?php echo htmlspecialchars($success_message); ?>
                            </div>
                            
                            <div class="text-center">
                                <p>Your account is currently in the queue for admin approval.</p>
                                <p>You will receive an email notification once your account has been activated.</p>
                                <p>Thank you for your patience.</p>
                                
                                <div class="mt-4">
                                    <a href="login.php" class="btn btn-primary">Return to Login</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="footer">
                        &copy; <?php echo date("Y"); ?> SMYRNA TECHNOLOGY AND TRADE OPC
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>