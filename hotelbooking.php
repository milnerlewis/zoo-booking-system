<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link rel="icon" href="images/RigetZooAdv.png" type="image/png">
	<title>Hotel Booking - Riget Zoo Adventures</title>
	<link rel="stylesheet" type="text/css" href="styles.css?v=3">
	<script src="js/main.js" defer></script>
	<style>
		body {
			background-image: url('images/bookingimg.jpg');
			background-size: cover;
			background-position: center;
			background-attachment: fixed;
		}

		.hotel-page-container {
			width: 90%;
			max-width: 760px;
			margin: 120px auto;
			background: rgba(255, 255, 255, 0.95);
			border-radius: 12px;
			padding: 40px;
			box-shadow: 0 6px 24px rgba(0, 0, 0, 0.15);
		}

		.hotel-page-container h2 {
			margin-top: 0;
			margin-bottom: 12px;
			color: #111;
		}

		.hotel-page-container p {
			color: #555;
			line-height: 1.6;
		}

		.hotel-actions {
			display: flex;
			gap: 12px;
			margin-top: 24px;
			flex-wrap: wrap;
		}

		.hotel-action-link {
			display: inline-block;
			background: #c7d2b9;
			color: #111;
			text-decoration: none;
			font-weight: 600;
			padding: 12px 16px;
			border-radius: 6px;
		}

		.hotel-action-link:hover {
			background: #b8c3aa;
		}
	</style>
</head>
<body>
	<header class="nav-wrap">
		<nav class="navbar" id="navigation">
			<div class="logo" aria-hidden="true"></div>
			<ul class="nav-links">
				<li><a href="index.php">Home</a></li>
				<li><a href="index.php#about-section">About us</a></li>
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
		<section class="hotel-page-container">
			<h2>Hotel Booking</h2>
			<p>
				The hotel booking workflow is being prepared and will be available soon.
				In the meantime, you can book a zoo visit or contact us for hotel availability.
			</p>
			<div class="hotel-actions">
				<a href="booking.php" class="hotel-action-link">Back to Booking Options</a>
				<a href="zoobooking.php" class="hotel-action-link">Book Zoo Visit</a>
				<a href="contact.php" class="hotel-action-link">Contact Us</a>
			</div>
		</section>
	</main>
</body>
</html>
