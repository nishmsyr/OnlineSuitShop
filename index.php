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
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />
  </head>
  <body>
    <!-- Navigation Bar -->
    <div class="navbar">
      <!-- Left: Logo and Nav Links -->
      <div class="left">
        <h1 class="logo">BLACKTIE</h1>
        <a href="index.php" class="nav-link">Home</a>
        <a href="shop.php" class="nav-link">Shop</a>
      </div>

      <!-- Right: Profile, Cart, and Logout -->
      <div class="right">
        <!-- Profile Icon -->
        <button
          class="icon-btn"
          title="Profile"
          onclick="window.location.href='profile.php'"
        >
          <i class="fas fa-user"></i>
        </button>

        <!-- Cart Icon -->
        <button
          class="icon-btn"
          title="Cart"
          onclick="window.location.href='cart.php'"
        >
          <i class="fas fa-shopping-cart"></i>
        </button>

        <!-- Logout Button -->
        <button class="logout-btn" onclick="window.location.href='login.php'">
          Logout
        </button>
      </div>
    </div>

    <!-- Hero Section -->
    <div class="hero">
      <h1 class="hero-title">BLACKTIE</h1>
      <p class="hero-subtitle">WHERE CONFIDENCE WEARS A SUIT</p>

      <!-- Shop Now Button -->
      <div class="shop-now-wrapper">
        <button
          class="shop-now-button"
          onclick="window.location.href='shop.php'"
        >
          Shop Now
        </button>
      </div>
    </div>
  </body>
</html>