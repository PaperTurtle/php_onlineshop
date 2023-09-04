<?php
ob_start();
session_start();
require_once 'templates/head.php';

// Überprüfen, ob der Benutzer bereits angemeldet ist
if (!isset($_SESSION['benutzer_id'])) {
    $_SESSION["not_logged_in"] = true;
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

if (isset($_GET["id"]) && ctype_digit($_GET["id"])) {
    require_once 'templates/connect.php';
    $produkt_id = $_GET["id"];

    // Produkt aus der Datenbank abrufen (als Prepared Statement)
    $selectQuery = "SELECT * FROM produkte WHERE produkt_id = ?";
    $stmt = $conn->prepare($selectQuery);
    $stmt->bind_param("i", $produkt_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Überprüfen, ob das Produkt existiert
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        // Produkt nicht gefunden -> Weiterleitung zur Produktübersichtsseite
        $stmt->close();
        $conn->close();
        $_SESSION["invalid_product_id"] = "Das Produkt mit der ID $produkt_id existiert nicht.";
        header("Location: produkte.php");
        exit();
    }

    $stmt->close();

    if (isset($_POST['menge'])) {
        $menge = $_POST['menge'];

        // Check if the quantity is 1 or above
        if ($menge >= 1) {
            // Produkt zum Warenkorb hinzufügen, falls es noch nicht existiert, sonst Menge aktualisieren
            $_SESSION['warenkorb'][$produkt_id] = ($_SESSION['warenkorb'][$produkt_id] ?? 0) + $menge;

            // Warenkorb in der Datenbank speichern oder aktualisieren (als Prepared Statement)
            $benutzer_id = $_SESSION['benutzer_id'];
            $insertUpdateQuery = "INSERT INTO warenkorb (benutzer_id, produkt_id, menge) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE menge = ?";
            $stmt = $conn->prepare($insertUpdateQuery);
            $stmt->bind_param("iiii", $benutzer_id, $produkt_id, $menge, $menge);
            $stmt->execute();
            $stmt->close();

            // Weiterleitung zur Produktseite mit einer Erfolgsmeldung (über GET-Parameter)
            $conn->close();
            header("Location: produkt.php?id=$produkt_id&added=true");
            exit();
        } else {
            // Menge ist kleiner als 1 -> Fehlermeldung anzeigen
            $_SESSION["quantity_error"] = "Die Menge muss 1 oder mehr sein, um das Produkt zum Warenkorb hinzuzufügen.";
        }
    }
    $conn->close();
} else {
    // Ungültige Produkt-ID -> Weiterleitung zur Produktübersichtsseite
    header("Location: produkte.php");
    exit();
}

$kategorieFarben = array(
    "Obst" => "#FF5733",
    "Gemüse" => "#4CAF50",
    "Proteine" => "#FFC300",
    "Milchprodukte" => "#FF6B8A",
    "Backwaren" => "#A569BD",
    "Frühstück" => "#48C9B0",
    "Grundnahrungsmittel" => "#FF5733",
    "Fisch" => "#3498DB",
    "Süßigkeiten" => "#E74C3C",
    "Getränke" => "#58D68D",
    "Brotaufstriche" => "#F7DC6F",
    "Soßen" => "#AF7AC5",
    "Öle" => "#45B39D",
    "Süßungsmittel" => "#F1948A",
    "Würzmittel" => "#D5DBDB"
);
ob_end_flush();
?>
<main class="mt-10 mb-10 pt-4">
    <?php
    require_once "templates/messageBlock.php";
    showMessageFromSession(type: "danger", icon: "exclamation-triangle-fill", sessionKey: "quantity_error");
    ?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 mb-4 text-center">
                <img src="data:image/jpeg;base64,<?= base64_encode($row['bild']); ?>" class="img-fluid" style="height: 330px; width: 330px;" alt="<?= $row["name"]; ?>">
            </div>
            <div class="col-md-6 mb-4">
                <div class="p-4">
                    <h1><?= $row["name"]; ?></h1>
                    <div class="mb-3">
                        <?php
                        $kategorie = $row["kategorie"];
                        $badgeFarbe = $kategorieFarben[$kategorie];
                        ?>
                        <a><span class="badge me-1" style="background-color: <?= $badgeFarbe; ?>;"><?= $row["kategorie"]; ?></span></a>
                    </div>
                    <p class="lead mb-4 font-monospace"><span><?= $row["preis"]; ?> €</span></p>
                    <p class="mb-7"><?= $row["beschreibung"]; ?></p>
                    <form class="d-flex justify-content-left" action="produkt.php?id=<?= $produkt_id; ?>" method="post">
                        <div class="form-outline me-1" style="width: 100px;">
                            <input type="number" name="menge" value="1" min="1" class="form-control">
                            <label for="menge" class="form-label">Menge:</label>
                        </div>
                        <button class="btn btn-success ms-1" type="submit" data-bs-toggle="modal" data-bs-target="#modalWindow">
                            Zum Warenkorb hinzufügen
                            <i class="fas fa-shopping-cart ms-1"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php if (isset($_GET['added']) && $_GET['added'] === 'true') : ?>
        <div class="modal fade show" id="modalWindow" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalWindowLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalWindowLabel">Artikel hinzugefügt!</h5>
                    </div>
                    <div class="modal-body">
                        <p><?= $row["name"] ?> wurde(n) deinem Warenkorb hinzugefügt</p>
                    </div>
                    <div class="modal-footer">
                        <a href="produkte.php" class="btn btn-primary">Weiter einkaufen <i class="fa-solid fa-basket-shopping"></i></a>
                        <a href="warenkorb.php" class="btn btn-success">Zum Warenkorb <i class="fa-solid fa-cart-shopping"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                var modal = new bootstrap.Modal(document.getElementById('modalWindow'));
                modal.show();
            });
        </script>
    <?php endif; ?>
</main>
<?php require_once 'templates/footer.php'; ?>