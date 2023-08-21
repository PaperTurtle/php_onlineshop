<?php
ob_start();
session_start();
include 'templates/head.php';
include 'templates/navbar.php';
include 'templates/connect.php';
include "templates/userData.php";
/*-----------------------------------------------------------*/

/**
 * Initialisiert den Warenkorb aus der Datenbank
 * 
 * @param mysqli $conn Datenbankverbindung
 * @param int $userId Benutzer-ID
 * @return array Warenkorb
 */
function initializeCartFromDatabase(mysqli $conn, int $userId): array
{
    $loadQuery = "SELECT produkt_id, menge FROM warenkorb WHERE benutzer_id = ?";
    $stmt = $conn->prepare($loadQuery);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $cart = array();
    while ($row = $result->fetch_assoc()) {
        $cart[$row['produkt_id']] = $row['menge'];
    }
    $stmt->close();

    return $cart;
}

/**
 * Fügt ein Produkt zum Warenkorb hinzu
 * 
 * @param mysqli $conn Datenbankverbindung
 * @param int $userId Benutzer-ID
 * @param int $productId Produkt-ID
 * @param int $quantity Menge
 */
function addToCart(mysqli $conn, int $userId, int $productId, int $quantity): void
{
    // Checkt, ob das Produkt bereits im Warenkorb ist
    // Wenn ja, dann erhöhe die Menge
    if (isset($_SESSION['warenkorb'][$productId])) {
        $_SESSION['warenkorb'][$productId] += $quantity;
    } else {
        $_SESSION['warenkorb'][$productId] = $quantity;
    }

    // SQL-Query zum Hinzufügen des Produkts zum Warenkorb
    // REPLACE INTO fügt ein Produkt hinzu, wenn es noch nicht im Warenkorb ist, oder aktualisiert die Menge, wenn es bereits im Warenkorb ist
    $insertUpdateQuery = "REPLACE INTO warenkorb (benutzer_id, produkt_id, menge) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertUpdateQuery);
    $stmt->bind_param('iii', $userId, $productId, $_SESSION['warenkorb'][$productId]);
    $stmt->execute();
    $stmt->close();
}

/**
 * Entfernt ein Produkt aus dem Warenkorb
 * 
 * @param mysqli $conn Datenbankverbindung
 * @param int $userId Benutzer-ID
 * @param int $productId Produkt-ID
 */
function removeFromCart(mysqli $conn, int $userId, int $productId): void
{
    // SQL-Query zum Löschen des Produkts aus dem Warenkorb
    $deleteQuery = "DELETE FROM warenkorb WHERE benutzer_id = ? AND produkt_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param('ii', $userId, $productId);
    $stmt->execute();
    $stmt->close();
    // Produkt aus dem Warenkorb entfernen
    unset($_SESSION['warenkorb'][$productId]);
}
if (!isset($_SESSION['benutzer_id'])) {
    $_SESSION["not_logged_in"] = true;
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}
getFullUserData();


// Überprüfen, ob der Warenkorb noch nicht existiert, dann initialisieren
if (!isset($_SESSION['warenkorb'])) {
    $_SESSION['warenkorb'] = initializeCartFromDatabase(conn: $conn, userId: $_SESSION['benutzer_id']);
}

// Produkt zum Warenkorb hinzufügen
if (isset($_POST['produkt_id']) && isset($_POST['menge'])) {
    $produkt_id = $_POST['produkt_id'];
    $menge = $_POST['menge'];
    addToCart(conn: $conn, userId: $_SESSION['benutzer_id'], productId: $produkt_id, quantity: $menge);
}

// Produkt aus dem Warenkorb entfernen
if (isset($_GET['remove']) && isset($_SESSION['warenkorb'][$_GET['remove']])) {
    $produkt_id = $_GET['remove'];
    removeFromCart(conn: $conn, userId: $_SESSION['benutzer_id'], productId: $produkt_id);
}

$total_preis = 0;
$index = 0;
ob_end_flush();
?>
<main class="min-vh-100">
    <?php
    include "templates/messageBlock.php";
    showMessageFromSession(type: "success", icon: "check-circle-fill", sessionKey: "bestellung_status");
    showMessageFromSession(type: "danger",  icon: "exclamation-triangle-fill", sessionKey: "bestellung_error");
    showMessageFromSession(type: "warning",  icon: "exclamation-triangle-fill", sessionKey: "warenkorb_leer");
    ?>
    <section class="h-100 min-vh-50 h-custom p-4" style="background-color: #eee; border-radius: 0.5rem;">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col">
                    <div class="card">
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-lg-7" id="shoppingCartContainer" style="display:none">
                                    <h5 class="mb-3"><a href="produkte.php" class="text-body"><i class="fas fa-long-arrow-alt-left me-2"></i>Weiter einkaufen</a></h5>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div>
                                            <p class="mb-0">Du hast <?= count($_SESSION['warenkorb']); ?> Artikel in deinen Warenkorb</p>
                                        </div>
                                    </div>
                                    <?php
                                    $gesamtpreis = 0;
                                    foreach ($_SESSION['warenkorb'] as $produkt_id => $menge) : ?>
                                        <?php
                                        // Produkt aus der Datenbank laden
                                        $produktQuery = "SELECT * FROM produkte WHERE produkt_id = $produkt_id";
                                        $result = $conn->query($produktQuery);
                                        if ($result->num_rows > 0) {
                                            $row = $result->fetch_assoc();
                                            $name = $row['name'];
                                            $preis = $row['preis'];
                                            $gesamtpreis = $preis * $menge;
                                            $bild = $row['bild'];
                                        }
                                        $total_preis += $gesamtpreis;
                                        ?>
                                        <div class="card mb-3 card-fade" style="display: none;" data-index="<?= $index ?>">
                                            <div class=" card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div class="d-flex flex-row align-items-center">
                                                        <div>
                                                            <img src="data:image/jpeg;base64,<?= base64_encode($bild); ?>" class="img-fluid rounded-3" alt="Shopping item" style="width: 65px;">
                                                        </div>
                                                        <div class="ms-3">
                                                            <h5><?= $name; ?></h5>
                                                            <p class="small mb-0"><?= $menge; ?> im Korb</p>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-row align-items-center">
                                                        <div style="width: 80px;">
                                                            <h5 class="mb-0 font-monospace"><?= $gesamtpreis; ?>€</h5>
                                                        </div>
                                                        <button onclick="window.location.href='warenkorb.php?remove=<?= $produkt_id; ?>';" class="btn btn-danger btn-floating m-1 delete-button">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php $index++; ?>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (!empty($_SESSION['warenkorb'])) : ?>
                                    <div class="col-lg-5" id="bestellungDetails" style="display:none">
                                        <div class="card bg-primary text-white rounded-3">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-4">
                                                    <h5 class="mb-0">Bestellungsdetails</h5>
                                                    <img src="./img/user_avatar.png" class="img-fluid rounded-3" style="width: 55px;" alt="Avatar">
                                                </div>
                                                <form class="mt-4">
                                                    <div class="form-outline form-white mb-4">
                                                        <input type="text" id="typeName" class="form-control form-control-lg" size="17" value="<?= htmlspecialchars($global_fullName) ?>" required readonly />
                                                        <label class="form-label" for="typeName">Kundenname</label>
                                                    </div>
                                                </form>
                                                <hr class="my-4">
                                                <div class="d-flex justify-content-between">
                                                    <p class="mb-2">Zwischensumme</p>
                                                    <p class="mb-2 font-monospace"><?= $total_preis; ?> €</p>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <p class="mb-2">Shipping</p>
                                                    <p class="mb-2 font-monospace">10.00 €</p>
                                                </div>
                                                <div class="d-flex justify-content-between mb-4">
                                                    <p class="mb-2">Gesamtpreis</p>
                                                    <p class="mb-2 font-monospace"><?= $total_preis + 10.00; ?> €</p>
                                                </div>
                                                <button type="button" class="btn btn-info btn-block btn-lg" onclick="window.location.href='bestellung_abschliessen.php'">
                                                    <div class="d-flex justify-content-between">
                                                        <span class="font-monospace"><?= $total_preis + 10.00; ?> €</span>
                                                        <span>Checkout <i class="fas fa-long-arrow-alt-right ms-2"></i></span>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<script>
    $(document).ready(function() {
        $('#shoppingCartContainer, #bestellungDetails').fadeIn(1000);
    });
    $(document).ready(function() {
        var cards = $(".card-fade");

        cards.each(function(index) {
            var card = $(this);
            setTimeout(function() {
                card.fadeIn("slow");
            }, 50 * index);
        });
    });
    $(document).ready(function() {
        var deleteButtons = $(".delete-button");

        // Add hover effect
        deleteButtons.hover(function() {
            $(this).css({
                transform: 'translateY(-3px)',
                transition: 'transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)'
            });
        }, function() {
            $(this).css({
                transform: 'translateY(0)',
                transition: 'transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)'
            });
        });
    });
</script>
<?php
$conn->close();
include 'templates/footer.php';
?>