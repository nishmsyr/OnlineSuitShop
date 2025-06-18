<header class="navbar">
      <nav id="hamburger-nav">
        <div class="hamburger-menu">
          <div class="hamburger-icon" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
          </div>
          <div class="menu-links">
            <li><a href="home.php" onclick="toggleMenu()">Home</a></li>
            <li><a href="shop.php" onclick="toggleMenu()">Shop</a></li>
            <li>
              <a href="promotion.php" onclick="toggleMenu()">Promotion</a>
            </li>
            <li><a href="store.php" onclick="toggleMenu()">Store</a></li>
          </div>
        </div>
      </nav>
      <div class="logo">
        <img src="./assets/blacktieWhite.png" alt="Blacktie Logo" />
      </div>
      <div class="nav">
        <div class="nav-top">
          <input type="search" placeholder="Search..." class="search-box" />
        </div>
        <div class="nav-center">
          <a href="menu.php">Home</a>

          <div class="dropdown">
            <button class="dropbtn" onclick="document.location='shop.php'">
              Shop <img src="./assets/arrow.png" alt="Arrow" class="arrow" />
            </button>
            <div class="dropdown-content">
              <a href="shop.php">Suit</a>
              <a href="#">Tie</a>
              <a href="#">Accessories</a>
            </div>
          </div>

          <a href="promotion.php">Promotion</a>
          <a href="store.php">Store</a>
        </div>
      </div>
      <div class="nav-right">
        <i class="fas fa-user" onclick="document.location='login.php'"></i>
        <a href="login.php"><b>Log In</b></a>
        <i
          class="fas fa-shopping-cart"
          onclick="document.location='cart.php'"
        ></i>
      </div>
    </header>