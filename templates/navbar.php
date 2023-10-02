<?php
require_once 'templates/connect.php';

if (isset($_SESSION["username"])) {
    $countQuery = "SELECT COUNT(DISTINCT produkt_id) FROM warenkorb WHERE benutzer_id = {$_SESSION["benutzer_id"]}";
    $countResult = $conn->query($countQuery);
    $totalMenge = ($countResult->num_rows > 0) ? $countResult->fetch_row()[0] : 0;
} else {
    $totalMenge = 0;
}
$badgeHTML = '';
if ($totalMenge > 0) {
    $badgeHTML = '<span class="badge rounded-pill badge-notification bg-danger">' . $totalMenge . '</span>';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#"><img src="./img/lebensmittel.png" width="80px" height="80px" alt="LebensmittelShop-Logo"></a>
        <button class="navbar-toggler" type="button" data-mdb-toggle="collapse" data-mdb-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
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
        </div>
    </div>
</nav>