<?php
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST["name"];
  $phone = $_POST["phone"];
  $email = $_POST["email"];
  $password = $_POST["password"]; // Store as plain text
  $address = $_POST["address"];
  $role = $_POST["role"];

  $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password, address, role) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssss", $name, $phone, $email, $password, $address, $role);

  if ($stmt->execute()) {
    echo "<script>alert('Registration successful! Please login.'); window.location.href='login.php';</script>";
  } else {
    echo "Error: " . $stmt->error;
  }

  $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register - Blacktie Suit Shop</title>
  <link rel="stylesheet" href="style.css" />
</head>

<body>

  <div class="auth-background">
    <div class="container">
      <h2>Account Registration</h2>

      <form action="register.php" method="POST">
        <!-- Name -->
        <label for="name">Name</label>
        <input type="text" name="name" required />

        <!-- Phone -->
        <label for="phone">Phone Number</label>
        <input type="text" name="phone" required />

        <!-- Email -->
        <label for="email">Email</label>
        <input type="email" name="email" required />

        <!-- Password -->
        <label for="password">Password</label>
        <input type="password" name="password" required />

        <!-- Address -->
        <label for="address">Address</label>
        <textarea name="address" required></textarea>

        <!-- Role (default to customer) -->
        <input type="hidden" name="role" value="customer" />

        <button type="submit">Submit</button>
      </form>


      <!-- Login Redirect -->
      <p align="center"><br>Already have an account? <a href="login.php">Log In</a>.</p>

    </div>
  </div>

</body>

</html>