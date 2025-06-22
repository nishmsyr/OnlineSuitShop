<?php
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = $_POST['customer_id'];
    $customer_name = $_POST['customer_name'];
    $customer_phone_num = $_POST['customer_phone_num'];
    $customer_email = $_POST['customer_email'];
    $customer_address = $_POST['customer_address'];
    $password = $_POST['password'];

    $sql = "INSERT INTO customer (customer_id, customer_name, customer_phone_num, customer_email, customer_address, password)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $customer_id, $customer_name, $customer_phone_num, $customer_email, $customer_address, $password);

    if ($stmt->execute()) {
        echo '
        <html>
        <head>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
        <script>
            Swal.fire({
                title: "Registered Successfully!",
                text: "You will now be redirected to login.",
                icon: "success",
                confirmButtonText: "OK"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "login.php";
                }
            });
        </script>
        </body>
        </html>';
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BlackTie | Register Page</title>
    <link rel="stylesheet" href="register.css" />
  </head>
  <body>
    <header class="header">
      <h2>Account Registration</h2>
    </header>

    <div class="logo-container">
      <img src="./assets/blacktie.png" alt="Blacktie Logo" class="logo" />
      <hr />
    </div>

    <div class="register-container">
      <form method="post" action="register.php">
        <h1>Register</h1>
        <label for="customer_id">ID</label>
        <input type="text" id="customer_id" name="customer_id" required />

        <label for="customer_name">Name</label>
        <input type="text" id="customer_name" name="customer_name" required />

        <label for="customer_phone_num">Phone Number</label>
        <input
          type="text"
          id="customer_phone_num"
          name="customer_phone_num"
          required
        />

        <label for="customer_email">Email</label>
        <input
          type="email"
          id="customer_email"
          name="customer_email"
          required
        />

        <label for="customer_address">Address</label>
        <input
          type="text"
          id="customer_address"
          name="customer_address"
          required
        />

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required />

        <div class="button-container">
          <button type="submit" class="submit-button">Submit</button>
          <button type="reset" class="reset-button">Reset</button>
        </div>
      </form>
    </div>
    <div class="signInLink">
      <p>Already has an account? <a href="login.php">Sign in</a>.</p>
    </div>

    <footer class="footer">
      <p>Copyright &#169; | Blacktie Suit Shop.</p>
    </footer>
  </body>
</html>
