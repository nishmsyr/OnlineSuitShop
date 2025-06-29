<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Home - Blacktie Suit Shop</title>
  <link rel="stylesheet" href="styles.css" />
  <!-- Font Awesome for profile/cart icons -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>
  <!-- Navigation Bar -->
  <div class="navbar">
    <!-- Left: Logo and Nav Links -->
    <div class="left">
      <h1 class="logo">BLACKTIE</h1>
      <a href="admin_home.php" class="nav-link">Home</a>
    </div>

    <!-- Right: Profile, Cart, and Logout -->
    <div class="right">
      <!-- Logout Button -->
      <button class="logout-btn" onclick="window.location.href='login.php'">
        Logout
      </button>
    </div>
  </div>

  <!-- Hero Section -->
  <div class="hero">
    <h1 class="hero-title">BLACKTIE</h1>
    <p class="hero-subtitle">ADMIN DASHBOARD</p>

    <!-- Shop Now Button -->
    <div class="shop-now-wrapper">
      <button
        class="shop-now-button"
        onclick="window.location.href='order.php'">
        View Order
      </button>
    </div>
    <div class="shop-now-wrapper">
      <button
        class="shop-now-button"
        onclick="window.location.href='product.php'">
        View Product
      </button>
    </div>
    <div class="shop-now-wrapper">
      <button
        class="shop-now-button"
        onclick="window.location.href='customer.php'">
        View Customer
      </button>
    </div>
  </div>
</body>

</html>