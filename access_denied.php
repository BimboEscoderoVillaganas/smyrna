<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="author" content="Kodinger">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>SMYRNA - Access Denied</title>
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
                            <h4 class="card-title text-center">Access Denied</h4>

                            <div class="alert alert-danger" role="alert">
                                <h5 class="alert-heading">Access Restricted</h5>
                                <p>You are not qualified to access the page that you tried to open.</p>
                                <hr>
                                <p class="mb-0">
                                    <?php
                                    if (isset($_SESSION['user_type'])) {
                                        echo "Your account type is: " . htmlspecialchars($_SESSION['user_type']) . ". ";
                                    }
                                    ?>
                                    Only administrators can access this page.
                                </p>
                            </div>

                            <div class="text-center mt-4">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a href="pages/<?php echo htmlspecialchars($_SESSION['user_type']); ?>/dashboard.php" class="btn btn-primary">Return to My Dashboard</a>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-primary">Return to Login</a>
                                <?php endif; ?>
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
