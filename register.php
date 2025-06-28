<?php
include_once 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitizeInput($_POST["name"]);
    $phone = sanitizeInput($_POST["phone"]);
    $email = sanitizeInput($_POST["email"]);
    $password = $_POST["password"];
    $address = sanitizeInput($_POST["address"]);

    // Validate inputs
    $errors = [];
    
    if (strlen($name) < 2) {
        $errors[] = "Name must be at least 2 characters long.";
    }
    
    if (strlen($phone) < 10) {
        $errors[] = "Please enter a valid phone number.";
    }
    
    if (!validateEmail($email)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    
    if (strlen($address) < 10) {
        $errors[] = "Please provide a complete address.";
    }

    if (empty($errors)) {
        // Check if customer already exists
        $existing_customer = getCustomerByEmail($conn, $email);
        if ($existing_customer) {
            $error_message = "An account with this email already exists. Please use a different email or try logging in.";
        } else {
            // Create new customer
            $customer_id = createCustomer($conn, $name, $phone, $email, $address, $password);
            if ($customer_id) {
                $success_message = "Registration successful! Your account has been created. You can now log in.";
                // Clear form data on success
                $_POST = [];
            } else {
                $error_message = "Registration failed. This email may already be in use or there was a server error. Please try again.";
            }
        }
    } else {
        $error_message = implode(" ", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BLACKTIE</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-background">
        <div class="container">
            <h2>Account Registration</h2>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error" id="errorAlert">
                    <span class="alert-icon">❌</span>
                    <div class="alert-content"><?php echo htmlspecialchars($error_message); ?></div>
                    <button class="alert-close" onclick="closeAlert('errorAlert')">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success" id="successAlert">
                    <span class="alert-icon">✅</span>
                    <div class="alert-content">
                        <?php echo htmlspecialchars($success_message); ?>
                        <br><small>Redirecting to login page in <span id="countdown">3</span> seconds...</small>
                    </div>
                    <button class="alert-close" onclick="closeAlert('successAlert')">&times;</button>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" required 
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                       placeholder="Enter your full name" />

                <label for="phone">Phone Number</label>
                <input type="text" name="phone" id="phone" required 
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                       placeholder="Enter your phone number" />

                <label for="email">Email</label>
                <input type="email" name="email" id="email" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                       placeholder="Enter your email address" />

                <label for="password">Password</label>
                <input type="password" name="password" id="password" required 
                       placeholder="Create a secure password (min. 6 characters)" />

                <label for="address">Address</label>
                <textarea name="address" id="address" required 
                          placeholder="Enter your complete address"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>

                <input type="hidden" name="role" value="customer" />

                <button type="submit">Submit</button>
            </form>

            <p>Already have an account? <a href="login.php">Log In</a>.</p>
        </div>
    </div>

    <script>
        // Auto-focus first input
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            if (nameInput && !nameInput.value) {
                nameInput.focus();
            }
        });

        // Close alert function
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.style.animation = 'slideDown 0.3s ease reverse';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }
        }

        // Success message countdown and redirect
        <?php if (isset($success_message)): ?>
        let countdown = 3;
        const countdownElement = document.getElementById('countdown');
        const countdownInterval = setInterval(() => {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                window.location.href = 'login.php';
            }
        }, 1000);
        <?php endif; ?>

        // Auto-hide error alerts after 7 seconds
        setTimeout(() => {
            const errorAlert = document.getElementById('errorAlert');
            if (errorAlert) {
                closeAlert('errorAlert');
            }
        }, 7000);

        // Form validation feedback
        const form = document.querySelector('form');
        const inputs = form.querySelectorAll('input[required], textarea[required]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.style.borderColor = '#e74c3c';
                } else {
                    this.style.borderColor = '#27ae60';
                }
            });
            
            input.addEventListener('focus', function() {
                this.style.borderColor = '#007bff';
            });
        });
    </script>
</body>
</html>
