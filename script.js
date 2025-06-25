// Cart functionality
let cart = JSON.parse(localStorage.getItem("blacktie-cart")) || [];

// Update cart count on page load
document.addEventListener("DOMContentLoaded", function () {
  updateCartCount();
  if (window.location.pathname.includes("cart.html")) {
    displayCartItems();
  }
});

// Add item to cart
function addToCart(id, name, price) {
  const existingItem = cart.find((item) => item.id === id);

  if (existingItem) {
    existingItem.quantity += 1;
  } else {
    cart.push({
      id: id,
      name: name,
      price: price,
      quantity: 1,
      image: `/placeholder.svg?height=300&width=250`,
    });
  }

  localStorage.setItem("blacktie-cart", JSON.stringify(cart));
  updateCartCount();

  // Show success message
  showNotification(`${name} added to cart!`);
}

// Update cart count
function updateCartCount() {
  const cartCount = document.getElementById("cartCount");
  if (cartCount) {
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = totalItems;

    // Show/hide cart count badge
    if (totalItems > 0) {
      cartCount.style.display = "flex";
    } else {
      cartCount.style.display = "none";
    }
  }
}

// Display cart items on cart page
function displayCartItems() {
  const cartItemsContainer = document.getElementById("cartItems");
  const emptyCart = document.getElementById("emptyCart");

  if (cart.length === 0) {
    cartItemsContainer.style.display = "none";
    emptyCart.style.display = "block";
    document.querySelector(".cart-summary").style.display = "none";
    return;
  }

  cartItemsContainer.style.display = "block";
  emptyCart.style.display = "none";
  document.querySelector(".cart-summary").style.display = "block";

  cartItemsContainer.innerHTML = cart
    .map(
      (item) => `
        <div class="cart-item">
            <img src="${item.image}" alt="${item.name}" class="item-image">
            <div class="item-details">
                <h4 class="item-name">${item.name}</h4>
                <p class="item-price">$${item.price}</p>
            </div>
            <div class="quantity-controls">
                <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                <span class="quantity">${item.quantity}</span>
                <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
            </div>
            <button class="remove-btn" onclick="removeFromCart(${item.id})">Remove</button>
        </div>
    `
    )
    .join("");

  updateCartSummary();
}

// Update item quantity
function updateQuantity(id, change) {
  const item = cart.find((item) => item.id === id);
  if (item) {
    item.quantity += change;
    if (item.quantity <= 0) {
      removeFromCart(id);
      return;
    }
    localStorage.setItem("blacktie-cart", JSON.stringify(cart));
    updateCartCount();
    displayCartItems();
  }
}

// Remove item from cart
function removeFromCart(id) {
  cart = cart.filter((item) => item.id !== id);
  localStorage.setItem("blacktie-cart", JSON.stringify(cart));
  updateCartCount();
  displayCartItems();
  showNotification("Item removed from cart");
}

// Update cart summary
function updateCartSummary() {
  const subtotal = cart.reduce(
    (sum, item) => sum + item.price * item.quantity,
    0
  );
  const tax = subtotal * 0.08; // 8% tax
  const total = subtotal + tax;

  document.getElementById("subtotal").textContent = `$${subtotal.toFixed(2)}`;
  document.getElementById("tax").textContent = `$${tax.toFixed(2)}`;
  document.getElementById("total").textContent = `$${total.toFixed(2)}`;
}

// Show notification
function showNotification(message) {
  // Create notification element
  const notification = document.createElement("div");
  notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: #2ecc71;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 4px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 1001;
        font-weight: 500;
        animation: slideIn 0.3s ease;
    `;

  notification.textContent = message;
  document.body.appendChild(notification);

  // Add CSS animation
  const style = document.createElement("style");
  style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
  document.head.appendChild(style);

  // Remove notification after 3 seconds
  setTimeout(() => {
    notification.style.animation = "slideIn 0.3s ease reverse";
    setTimeout(() => {
      document.body.removeChild(notification);
      document.head.removeChild(style);
    }, 300);
  }, 3000);
}

// Clear cart (for testing)
function clearCart() {
  cart = [];
  localStorage.removeItem("blacktie-cart");
  updateCartCount();
  if (window.location.pathname.includes("cart.html")) {
    displayCartItems();
  }
}
// Add item to cart via AJAX
function addToCart(productId, productName) {
  const btn = event.target;
  btn.disabled = true;
  btn.textContent = "Adding...";

  fetch("api/cart_operations.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `action=add&product_id=${productId}&quantity=1`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        updateCartCount(data.cart_count);
        showNotification(data.message);
      } else {
        showNotification(data.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("An error occurred", "error");
    })
    .finally(() => {
      btn.disabled = false;
      btn.textContent = "Add to Cart";
    });
}

// Update quantity
function updateQuantity(productId, newQuantity) {
  fetch("api/cart_operations.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `action=update&product_id=${productId}&quantity=${newQuantity}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        updateCartCount(data.cart_count);
        location.reload(); // Reload to update cart display
      } else {
        showNotification(data.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("An error occurred", "error");
    });
}

// Remove item from cart
function removeFromCart(productId) {
  if (confirm("Are you sure you want to remove this item?")) {
    fetch("api/cart_operations.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=remove&product_id=${productId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          updateCartCount(data.cart_count);
          location.reload(); // Reload to update cart display
        } else {
          showNotification(data.message, "error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showNotification("An error occurred", "error");
      });
  }
}

// Update cart count in header
function updateCartCount(count) {
  const cartCountElement = document.getElementById("cartCount");
  if (cartCountElement) {
    cartCountElement.textContent = count;
    if (count > 0) {
      cartCountElement.style.display = "flex";
    } else {
      cartCountElement.style.display = "none";
    }
  }
}

// Show notification
function showNotification(message, type = "success") {
  const notification = document.createElement("div");
  notification.className = `notification ${type}`;
  notification.textContent = message;

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.style.animation = "slideIn 0.3s ease reverse";
    setTimeout(() => {
      if (document.body.contains(notification)) {
        document.body.removeChild(notification);
      }
    }, 300);
  }, 3000);
}

// Checkout function
function checkout() {
  alert("Checkout functionality would be implemented here!");
}

// Filter products by category
function filterProducts(category) {
  const products = document.querySelectorAll(".product-card");
  const filterBtns = document.querySelectorAll(".filter-btn");

  // Update active button
  filterBtns.forEach((btn) => btn.classList.remove("active"));
  event.target.classList.add("active");

  // Filter products
  products.forEach((product) => {
    if (category === "all" || product.dataset.category === category) {
      product.style.display = "block";
    } else {
      product.style.display = "none";
    }
  });
}

// Load cart count on page load
document.addEventListener("DOMContentLoaded", () => {
  fetch("api/cart_operations.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "action=get_count",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        updateCartCount(data.cart_count);
      }
    })
    .catch((error) => {
      console.error("Error loading cart count:", error);
    });
});
