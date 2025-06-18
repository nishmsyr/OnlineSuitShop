<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Blacktie</title>
    <link rel="stylesheet" href="style.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />
  </head>
  <body>    
    <?php include 'component/navbar.php'; ?>

    <section class="menuInterface">
      <img src="./assets/suitMenu.png" alt="Menu Image" class="menu-img" />
      <div class="overlay-text">
        <img
          src="./assets/blacktie.png"
          alt="Logo Overlay"
          class="overlay-logo"
        />
      </div>

      <div class="social-icons">
        <a href="https://www.instagram.com/" target="_blank"
          ><img src="./assets/instagram.png" alt="Instagram icon" class="icon"
        /></a>
        <a href="https://www.facebook.com/" target="_blank"
          ><img src="./assets/facebook.png" alt="Facebook icon" class="icon"
        /></a>
        <a href="https://www.tiktok.com/en/" target="_blank"
          ><img src="./assets/tiktok.png" alt="Tiktok icon" class="icon"
        /></a>
      </div>
    </section>
           <?php include 'component/footer.php'; ?>

    <script src="script.js"></script>
  </body>
</html>
