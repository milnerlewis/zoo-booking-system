<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="icon" href="images/RigetZooAdv.png" type="image/png">
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=3">
    <script src="js/main.js" defer></script>
    <script>
        /* no js */
    </script>
    <style>body{background:#f6efe8!important}</style>
    </head>
    <body>
    <header class="nav-wrap">
        <nav class="navbar">
            <div class="logo" aria-hidden="true"></div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="#about-section">About us</a></li>
                <li><a href="booking.php">Book</a></li>
                <li><a href="contact.php">Contact us</a></li>
            </ul>
            <div class="nav-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Log in</a>
                    <a href="signup.php">Sign up</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main id="main-content">
        <section class="hero" role="banner">
            <div class="hero-content">
                <h1>Welcome to<br>Riget Zoo Adventures</h1>
                <div class="sub">Bring your family and explore our amazing animals</div>
                <div class="btn-row">
                    <a class="btn" href="booking.php">Book now</a>
                    <a class="btn" href="index.php#animals">Explore the zoo</a>
                </div>
            </div>
        </section>

        <section class="container" id="animals">
            <div class="section-title">Our animals:</div>
            <div class="cards">
                <article class="card">
                    <div class="thumb" aria-hidden="true"></div>
                    <div class="card-body">
                        <h3>The Lions</h3>
                        <p>See our pride and learn about conservation efforts.</p>
                    </div>
                </article>
                <article class="card">
                    <div class="thumb" aria-hidden="true"></div>
                    <div class="card-body">
                        <h3>Giraffes</h3>
                        <p>Get close to our tall friends during feeding time.</p>
                    </div>
                </article>
            </div>
        </section>
        
        <section id="about-section" class="about-section">
            <div class="container">
                <h2>About Riget Zoo Adventures</h2>
                <div class="about-content">
                    <div class="about-text">
                        <h3>Welcome to Our Wildlife Paradise</h3>
                        <p>Riget Zoo Adventures has been delighting families and wildlife enthusiasts for over 30 years. Nestled in the heart of Adventure City, our 150-acre zoo is home to more than 2,000 animals from around the globe, representing over 400 species.</p>
                        
                        <h3>Our Mission</h3>
                        <p>We are dedicated to wildlife conservation, education, and providing unforgettable experiences that inspire people to care for our planet's incredible biodiversity. Every visit helps support our conservation efforts and animal welfare programs.</p>
                        
                        <h3>What Makes Us Special</h3>
                        <ul class="features-list">
                            <li>African Safari Experience with lions, elephants, giraffes and any other safari wonder you can think of</li>
                            <li>Arctic Adventure featuring penguins and polar bears</li>
                            <li>Tropical Rainforest with exotic birds and plenty of primates</li>
                            <li>Reptile House with fascinating snakes and lizards</li>
                            <li>Daily animal shows, public feedings and educational talks</li>
                            <li>Conservation and research programs alongside breeding initiatives to keep your favourite species going</li>
                        </ul>
                    </div>
                    <div class="about-stats">
                        <div class="stat-card">
                            <h4>2,000+</h4>
                            <p>Animals</p>
                        </div>
                        <div class="stat-card">
                            <h4>400+</h4>
                            <p>Species</p>
                        </div>
                        <div class="stat-card">
                            <h4>150</h4>
                            <p>Acres</p>
                        </div>
                        <div class="stat-card">
                            <h4>30+</h4>
                            <p>Years</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

</body>
</html>
