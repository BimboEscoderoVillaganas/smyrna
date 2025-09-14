<?php
// Start session for error messages
session_start();

// Include database configuration
require_once 'db/db_connection.php';

// Initialize variables
$name = $email = '';
$errors = [];

// Process form data when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmed'])) {
    // Get and sanitize input data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $agree = isset($_POST['agree']) ? true : false;
    
    // Validate input
    if (empty($name)) {
        $errors['name'] = true;
    }
    
    if (empty($email)) {
        $errors['email'] = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = true;
    }
    
    if (empty($password)) {
        $errors['password'] = true;
    } elseif (strlen($password) < 8) {
        $errors['password'] = true;
    }
    
    if (!$agree) {
        $errors['agree'] = true;
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            // Get database connection
            $pdo = getDBConnection();
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM user_table WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $errors['email'] = true;
                $_SESSION['error_message'] = 'Email already registered';
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user into database with inactive status and user type
                $stmt = $pdo->prepare("INSERT INTO user_table (name, email, password, user_type, status) VALUES (?, ?, ?, 'user', 'inactive')");
                $stmt->execute([$name, $email, $hashedPassword]);
                
                // Set success message in session
                $_SESSION['success_message'] = 'Account request successfully submitted! Your account is pending admin approval.';
                
                // Redirect to queue page
                header('Location: account_queue.php');
                exit();
            }
        } catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $_SESSION['error_message'] = 'Registration failed. Please try again later.';
        }
    } else {
        $_SESSION['error_message'] = 'Please correct the errors below.';
    }
}

// Check for success/error messages
$success_message = '';
$error_message = '';

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="author" content="Kodinger">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>SMYRNA</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="assets/css/my-login.css">
	<style>
		.modal-backdrop {
			z-index: 1040;
		}
		.modal {
			z-index: 1050;
		}
		.terms-error {
			display: none;
			width: 100%;
			margin-top: 0.25rem;
			font-size: 80%;
			color: #dc3545;
		}
	</style>
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
							<h4 class="card-title">Register</h4>
							
							<?php if (!empty($success_message)): ?>
								<div class="alert alert-success" role="alert">
									<?php echo htmlspecialchars($success_message); ?>
								</div>
							<?php endif; ?>
							
							<?php if (!empty($error_message)): ?>
								<div class="alert alert-danger" role="alert">
									<?php echo htmlspecialchars($error_message); ?>
								</div>
							<?php endif; ?>
							
							<form id="registrationForm" method="POST" class="my-login-validation" novalidate="">
								<div class="form-group">
									<label for="name">Name</label>
									<input id="name" type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" name="name" value="<?php echo htmlspecialchars($name); ?>" required autofocus>
									<div class="invalid-feedback">
										What's your name?
									</div>
								</div>

								<div class="form-group">
									<label for="email">E-Mail Address</label>
									<input id="email" type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
									<div class="invalid-feedback">
										Your email is invalid
									</div>
								</div>

								<div class="form-group">
									<label for="password">Password</label>
									<input id="password" type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" name="password" required data-eye>
									<div class="invalid-feedback">
										Password is required
									</div>
								</div>

								<div class="form-group">
									<div class="custom-checkbox custom-control">
										<input type="checkbox" name="agree" id="agree" class="custom-control-input <?php echo isset($errors['agree']) ? 'is-invalid' : ''; ?>" required="">
										<label for="agree" class="custom-control-label">I agree to the <a href="#">Terms and Conditions</a></label>
										<div class="invalid-feedback">
											You must agree with our Terms and Conditions
										</div>
										<div id="termsError" class="terms-error">
											You must agree with our Terms and Conditions
										</div>
									</div>
								</div>

								<div class="form-group m-0">
									<button type="button" id="registerBtn" class="btn btn-primary btn-block">
										Register
									</button>
									<input type="hidden" name="confirmed" id="confirmed" value="0">
								</div>
								<div class="mt-4 text-center">
									Already have an account? <a href="login.php">Login</a>
								</div>
							</form>
						</div>
					</div>
					
					<div class="footer">
                        &copy; <?php echo date("Y"); ?> SMYRNA TECHNOLOGY AND TRADE OPC
                    </div>
				</div>
			</div>
		</div>
	</section>

	<!-- Confirmation Modal -->
	<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="confirmationModalLabel">Account Approval Process</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<p>Your account will first be checked by the admin. You must wait before the account is activated before you will be able to use your account.</p>
					<p>Do you want to proceed with account creation?</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel Account Creation</button>
					<button type="button" id="proceedBtn" class="btn btn-primary">Proceed</button>
				</div>
			</div>
		</div>
	</div>

	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	<script src="assets/js/my-login.js"></script>
	
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			// Get elements
			const registerBtn = document.getElementById('registerBtn');
			const proceedBtn = document.getElementById('proceedBtn');
			const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
			const confirmedInput = document.getElementById('confirmed');
			const form = document.getElementById('registrationForm');
			const agreeCheckbox = document.getElementById('agree');
			const termsError = document.getElementById('termsError');
			
			// When register button is clicked, show modal instead of submitting
			registerBtn.addEventListener('click', function(e) {
				e.preventDefault();
				
				// Reset error messages
				termsError.style.display = 'none';
				const errorElements = form.querySelectorAll('.is-invalid');
				errorElements.forEach(el => el.classList.remove('is-invalid'));
				
				// Basic validation before showing modal
				let isValid = true;
				const inputs = form.querySelectorAll('input[required]');
				
				inputs.forEach(input => {
					if (!input.value && input.type !== 'checkbox') {
						isValid = false;
						input.classList.add('is-invalid');
					}
				});
				
				// Special validation for checkbox
				if (!agreeCheckbox.checked) {
					isValid = false;
					agreeCheckbox.classList.add('is-invalid');
					termsError.style.display = 'block';
				}
				
				// Special validation for email format
				const emailInput = document.getElementById('email');
				if (emailInput.value && !/\S+@\S+\.\S+/.test(emailInput.value)) {
					isValid = false;
					emailInput.classList.add('is-invalid');
				}
				
				// Special validation for password length
				const passwordInput = document.getElementById('password');
				if (passwordInput.value && passwordInput.value.length < 8) {
					isValid = false;
					passwordInput.classList.add('is-invalid');
				}
				
				if (isValid) {
					// Show the confirmation modal
					confirmationModal.show();
				} else {
					// Show validation errors
					form.classList.add('was-validated');
				}
			});
			
			// When proceed button is clicked, submit the form
			proceedBtn.addEventListener('click', function() {
				// Set confirmed flag
				confirmedInput.value = '1';
				
				// Hide the modal
				confirmationModal.hide();
				
				// Submit the form
				form.submit();
			});
			
			// Add event listener to checkbox to remove error when checked
			agreeCheckbox.addEventListener('change', function() {
				if (this.checked) {
					this.classList.remove('is-invalid');
					termsError.style.display = 'none';
				}
			});
		});
	</script>
</body>
</html>