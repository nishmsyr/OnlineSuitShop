<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BlackTie | Login Page</title>
    <link rel="stylesheet" href="login.css" />
  </head>
  <body>
    <header class="header">
      <h2>Log Into Your Account</h2>
    </header>

    <div class="logo-container">
      <img src="./assets/blacktie.png" alt="Blacktie Logo" class="logo" />
      <hr />
    </div>

    <h1 align="center" style="margin-bottom: 20px">Log In</h1>
    <div class="user-type">
  <input type="radio" id="customer" name="user" required />
  <label for="customer">Customer</label>

  <input type="radio" id="staff" name="user" required />
  <label for="staff">Staff</label>
</div>

    <div class="login-container">
      <form>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required />

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required />

        <div class="button-container">
          <button
            type="submit"
            class="login-button"
            onclick="document.location='index.php'"
          >
            Login
          </button>
          <a href="register.php" class="register-link">Register</a>
        </div>
      </form>
    </div>

    <footer class="footer">
      <p>Copyright &#169; | Blacktie Suit Shop.</p>
    </footer>
  </body>
</html>
