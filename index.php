<?php
session_start();
require_once 'templates/connect.php';
require_once "templates/userData.php";
getFullUserData();

if (isset($_SESSION["username"])) {
	$countQuery = "SELECT COUNT(DISTINCT produkt_id) FROM warenkorb WHERE benutzer_id = {$_SESSION["benutzer_id"]}";
	$countResult = $conn->query($countQuery);
	$totalMenge = ($countResult->num_rows > 0) ? $countResult->fetch_row()[0] : 0;
} else {
	$totalMenge = 0;
}
$badgeHTML = ($totalMenge > 0) ? '<span class="badge rounded-pill badge-notification bg-danger">' . $totalMenge . '</span>' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Geschmacksgarten-Onlineshop</title>
	<link rel="icon" href="./img/lebensmittel.png" />

	<!-- Bootstrap CSS and JavaScript Dependencies -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
	<script src="https://kit.fontawesome.com/9997128989.js" crossorigin="anonymous" async defer></script>
	<script type="module" src="https://cdn.jsdelivr.net/npm/minidenticons@4.2.0/minidenticons.min.js" crossorigin="anonymous" defer></script>

	<!-- Font Awesome CSS and Custom Styles -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
	<link rel="stylesheet" href="./css/button.css" />
	<link rel="stylesheet" href="./css/home.css" />
	<link rel="stylesheet" href="./css/flash.css">
	<link rel="stylesheet" href="./css/mdb.min.css" />
	<script src="https://unpkg.com/htmx.org@1.9.6"></script>
</head>

<body class="d-flex text-center text-dark bg-image">
	<?php
	require_once "templates/messageBlock.php";
	showMessageFromSession(type: "success", icon: "check-circle-fill", sessionKey: "user_delete_success");
	showMessageFromSession(type: "success", icon: "check-circle-fill", sessionKey: "success_logout");
	showMessageFromSession(type: "warning", icon: "exclamation-triangle-fill", sessionKey: "error_logout");
	showMessageFromSession(type: "warning", icon: "exclamation-triangle-fill", sessionKey: "already_logged_in");
	?>
	<div id="main-content" class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
		<header class="mb-auto">
			<nav class="navbar navbar-expand-lg justify-content-center navbar-dark">
				<ul class="navbar-nav">
					<?php
					$navItems = [
						['text' => 'Startseite', 'link' => 'index.php', 'icon' => 'fa-house'],
						['text' => 'Produkte', 'link' => 'produkte.php', 'icon' => 'fa-apple-whole'],
						['text' => 'Kontakt', 'link' => 'kontakt.php', 'icon' => 'fa-address-card'],
					];
					if (isset($_SESSION['username'])) {
						// Benutzer ist eingeloggt
						$navItems[] = ['text' => 'Warenkorb', 'link' => 'warenkorb.php', 'icon' => 'fa-cart-shopping'];
						$navItems[] = ['text' => 'Profil', 'link' => 'profil.php', 'icon' => 'fa-user'];
						$navItems[] = ['text' => 'Abmelden', 'link' => 'logout.php', 'icon' => 'fa-right-from-bracket'];
					} else {
						// Benutzer ist nicht eingeloggt
						$navItems[] = ['text' => 'Anmelden', 'link' => 'login.php', 'icon' => 'fa-right-to-bracket'];
						$navItems[] = ['text' => 'Registrieren', 'link' => 'register.php', 'icon' => 'fa-marker'];
					}
					foreach ($navItems as $item) {
						$activeClass = (basename($_SERVER['PHP_SELF']) == $item['link']) ? 'active fw-bold' : '';
						echo '
						<li class="nav-item">
							<a class="nav-link ' . $activeClass . '" href="' . $item['link'] . '">' . $item['text'] . ' 
								<i class="fa-solid ' . $item['icon'] . '"></i>' . ($item['text'] === 'Warenkorb' ? $badgeHTML : '') . '
							</a>
						</li>';
					}
					?>
				</ul>
			</nav>
		</header>
		<main class="px-3">
			<h1 class="text-center text-light position-relative z-index-above display-3">
				<?php
				if (isset($_SESSION["benutzer_id"])) { ?>
					<strong>Willkommen zurück, <?= htmlspecialchars($global_vorname) ?></strong>
				<?php } else { ?>
					<strong>Willkommen im Geschmacksgarten</strong>
				<?php } ?>
			</h1>
			<p class="text-center text-light lead position-relative z-index-above fst-italic">
				Entdecke eine große Auswahl an frischen Lebensmitteln direkt zu dir nach Hause!
				<i class="fa-solid fa-house-user"></i>
			</p>
			<button class="btn btn-lg btn-light" onclick="window.location.href='produkte.php'">
				Stüber auf neue Produkte
			</button>
		</main>
		<button id="back-to-top-btn">
			<i class="fa-solid fa-angles-up"></i>
		</button>
		<footer class="mt-auto text-white-50">
			<p>&copy; <?= date("Y"); ?> Geschmacksgarten-Onlineshop, <span data-bs-toggle="tooltip" data-bs-placement="top" title="Yoooooo">Seweryn Czabanowski</span></p>
		</footer>
	</div>
	<script type="text/javascript" src="./js/mdb.min.js"></script>
	<script type="text/javascript" src="./js/customValidation.js"></script>
	<script type="text/javascript" src="./js/tooltip.js"></script>
	<script type="text/javascript" src="./js/toTopButton.js"></script>
</body>

</html>