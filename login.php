<?php
include_once 'functions.php';

$session_id = initializeSession();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST["role"];
    $email = sanitizeInput($_POST["email"]);
    $password = $_POST["password"];

    if ($role === "customer") {
        $customer = getCustomerByEmail($conn, $email);
        if ($customer && password_verify($password, $customer['PASSWORD'])) {
            $_SESSION["customer_id"] = $customer["customer_id"];
            $_SESSION["customer_name"] = $customer["customer_name"];
            $_SESSION["user_role"] = "customer";
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Invalid email or password. Please check your credentials and try again.";
        }
    } elseif ($role === "admin") {
        // Check admin table (you'll need to create this)
        $admin = getAdminByEmail($conn, $email);
        if ($admin && password_verify($password, $admin['admin_password'])) {
            $_SESSION["admin_id"] = $admin["admin_id"];
            $_SESSION["admin_name"] = $admin["admin_name"];
            $_SESSION["user_role"] = "admin";
            header("Location: admin.php");
            exit();
        } else {
            $error_message = "Invalid admin credentials. Please verify your login details.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BLACKTIE</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="auth-background">
        <div class="container">
            <h2>Login</h2>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error" id="errorAlert">
                    <span class="alert-icon">❌</span>
                    <div class="alert-content"><?php echo htmlspecialchars($error_message); ?></div>
                    <button class="alert-close" onclick="closeAlert('errorAlert')">&times;</button>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="radio-group">
                    <label>
                        <input type="radio" name="role" value="customer" required 
                               <?php echo (isset($_POST['role']) && $_POST['role'] === 'customer') ? 'checked' : ''; ?> /> 
                        Customer
                    </label>
                    <label>
                        <input type="radio" name="role" value="admin" required 
                               <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'checked' : ''; ?> /> 
                        Admin
                    </label>
                </div>

                <label for="email">Email</label>
                <input type="email" name="email" id="email" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />

                <label for="password">Password</label>
                <input type="password" name="password" id="password" required />

                <button type="submit">Log In</button>
            </form>

            <p>Don't have an account? <a href="register.php">Create Account</a>.</p>
        </div>
    </div>

    <script>
        // Auto-focus first input or email if error occurred
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const firstRadio = document.querySelector('input[name="role"]');
            
            if (emailInput.value) {
                emailInput.focus();
            } else if (firstRadio && !document.querySelector('input[name="role"]:checked')) {
                firstRadio.focus();
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

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.id) {
                    closeAlert(alert.id);
                }
            });
        }, 5000);
    </script>
</body>
</html>
