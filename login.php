<?php
include 'connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $role = $_POST["role"];
  $email = $_POST["email"];
  $password = $_POST["password"];

  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
  $stmt->bind_param("ss", $email, $role);
  $stmt->execute();

  $result = $stmt->get_result();
  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();


    if ($password === $user["password"]) {

      $_SESSION["user_id"] = $user["id"];
      $_SESSION["user_name"] = $user["name"];
      $_SESSION["user_role"] = $user["role"];

      if ($user["role"] === "customer") {
        header("Location: index.php");
      } elseif ($user["role"] === "admin") {
        header("Location: adminhome.php");
      }
      exit();
    } else {
      echo "<script>alert('Incorrect password.'); window.location.href='login.php';</script>";
    }
  } else {
    echo "<script>alert('No such user found.'); window.location.href='login.php';</script>";
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
  <title>Login - Blacktie Suit Shop</title>
  <link rel="stylesheet" href="style.css" />
</head>

<body>

  <div class="auth-background">
    <div class="container">
      <h2>Login</h2>

      <form action="login.php" method="POST">
        <!-- Role selection -->
        <div class="radio-group">
          <label><input type="radio" name="role" value="customer" required /> Customer</label>
          <label><input type="radio" name="role" value="admin" required /> Admin</label>
        </div>

        <!-- Email -->
        <label for="email">Email</label>
        <input type="email" name="email" required />

        <!-- Password -->
        <label for="password">Password</label>
        <input type="password" name="password" required />

        <!-- Login Button -->
        <button type="submit">Log In</button>
      </form>


      <!-- Register Redirect -->
      <p align="center"><br>Don't have an account? <a href="register.php">Create Account</a>.</p>
    </div>
  </div>

</body>

</html>