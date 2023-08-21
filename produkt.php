<?php
ob_start();
session_start();
require_once 'templates/head.php';
require_once 'templates/navbar.php';

// Überprüfen, ob der Benutzer bereits angemeldet ist
if (!isset($_SESSION['benutzer_id'])) {
    $_SESSION["not_logged_in"] = true;
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

if (isset($_GET["id"]) && ctype_digit($_GET["id"])) {
    $produkt_id = $_GET["id"];

    require_once 'templates/connect.php';

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
        header("Location: produkte.php");
        exit();
    }

    $stmt->close();

    if (isset($_POST['menge'])) {
        $menge = $_POST['menge'];

        // Check if the quantity is 1 or above
        if ($menge >= 1) {
            // Warenkorb in der Session speichern
            if (isset($_SESSION['warenkorb'][$produkt_id])) {
                $_SESSION['warenkorb'][$produkt_id] += $menge;
            } else {
                $_SESSION['warenkorb'][$produkt_id] = $menge;
            }

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
ob_end_flush();
?>
<main class="d-flex justify-content-center" style="background-color: #eee; border-radius: 0.5rem;">
    <div class="card mt-4 mb-4" style="width: 18rem;" id="productContainer">
        <?php
        require_once "templates/messageBlock.php";
        if (isset($_SESSION['quantity_error'])) { ?>
            <div class='alert alert-danger alert-dismissible fade show' role='alert'>
                <svg class='bi flex-shrink-0 me-2' width='24' height='24' role='img' aria-label='Alert:'>
                    <use xlink:href='#exclamation-triangle-fill' />
                </svg>
                <?= $_SESSION['quantity_error'] ?>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>
        <?php
            unset($_SESSION['quantity_error']);
        } ?>
        <img src="data:image/jpeg;base64,<?= base64_encode($row['bild']); ?>" class="card-img-top mx-auto" style="height: 220px; width: 220px;" alt="<?= $row["name"]; ?>">
        <div class="card-body">
            <h5 class="card-title"><?= $row["name"]; ?></h5>
            <p class="card-text fst-italic"><?= $row["beschreibung"]; ?></p>
            <p class="card-text font-monospace">Preis: <?= $row["preis"]; ?> €</p>
            <form action="produkt.php?id=<?= $produkt_id; ?>" method="post">
                <div class="mb-3 form-outline">
                    <input type="number" name="menge" value="1" min="1" class="form-control">
                    <label for="menge" class="form-label">Menge:</label>
                </div>
                <div class="text-center mx-auto">
                    <button type="submit" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalWindow">Zum Warenkorb hinzufügen <i class="fa-solid fa-plus"></i></button>
                </div>
            </form>
        </div>
    </div>
    <?php if (isset($_GET['added']) && $_GET['added'] === 'true') : ?>
        <div class="modal fade show" id="modalWindow" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalWindowLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalWindowLabel">Artikel hinzugefügt!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
            // Modal-Fenster anzeigen
            window.addEventListener('DOMContentLoaded', () => {
                var modal = new bootstrap.Modal(document.getElementById('modalWindow'));
                modal.show();
            });
        </script>
    <?php endif; ?>
</main>
<?php require_once 'templates/footer.php'; ?>