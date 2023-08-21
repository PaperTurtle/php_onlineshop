<?php
session_start();

if (!isset($_SESSION['benutzer_id'])) {
    $_SESSION["not_logged_in"] = true;
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

require_once "templates/connect.php";

try {
    // Überprüfen, ob der Warenkorb leer ist
    if (!isset($_SESSION["warenkorb"]) || count($_SESSION["warenkorb"]) == 0) {
        $_SESSION["warenkorb_leer"] = "Der Warenkorb ist leer! Keine Bestellung möglich!";
        header("Location: warenkorb.php");
        exit();
    }

    // Transaktion starten
    $conn->begin_transaction();

    $benutzer_id = $_SESSION["benutzer_id"];

    $gesamtBetrag = 0;

    // Bestellung in die Tabelle "bestellungen" einfügen
    $insertBestellungQuery = "INSERT INTO bestellungen (benutzer_id, bestelltDatum, gesamtbetrag) VALUES (?, NOW(), ?)";
    $stmt = $conn->prepare($insertBestellungQuery);
    $gesamtBetrag += 10;
    $stmt->bind_param("id", $benutzer_id, $gesamtBetrag);
    $stmt->execute();

    // Die eingefügte Bestellungs-ID abrufen
    $bestellungs_id = $stmt->insert_id;

    // Für jedes Produkt im Warenkorb die Bestellung einfügen
    foreach ($_SESSION["warenkorb"] as $produkt_id => $menge) {
        // Produkt-Preis abrufen
        $selectPriceQuery = "SELECT preis FROM produkte WHERE produkt_id = ?";
        $stmtPrice = $conn->prepare($selectPriceQuery);
        $stmtPrice->bind_param("i", $produkt_id);
        $stmtPrice->execute();
        $resultPrice = $stmtPrice->get_result();

        if ($resultPrice->num_rows == 1) {
            $rowPrice = $resultPrice->fetch_assoc();
            $preis = $rowPrice["preis"];
            $gesamtpreis = $preis * $menge;
            $gesamtBetrag += $gesamtpreis;

            // Bestellte Produkte in die Tabelle "bestellteprodukte" einfügen
            $insertBestellteProdukteQuery = "INSERT INTO bestellteprodukte (bestellungs_id, produkt_id, menge, einzelpreis) VALUES (?, ?, ?, ?)";
            $stmtProdukte = $conn->prepare($insertBestellteProdukteQuery);
            $stmtProdukte->bind_param("iiid", $bestellungs_id, $produkt_id, $menge, $preis);
            $stmtProdukte->execute();
            $stmtProdukte->close();
        }

        $stmtPrice->close();
    }

    // Gesamtbetrag aktualisieren
    $updateBestellungQuery = "UPDATE bestellungen SET gesamtbetrag = ? WHERE bestellungs_id = ?";
    $stmtUpdate = $conn->prepare($updateBestellungQuery);
    $stmtUpdate->bind_param("di", $gesamtBetrag, $bestellungs_id);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    // Warenkorb leeren
    $deleteFromWarenkorbQuery = "DELETE FROM warenkorb WHERE benutzer_id = ?";
    $stmtDelete = $conn->prepare($deleteFromWarenkorbQuery);
    $stmtDelete->bind_param("i", $benutzer_id);
    $stmtDelete->execute();
    $stmtDelete->close();

    // Transaktion abschließen
    $stmt->close();
    $conn->commit();

    // Session-Warenkorb leeren
    $_SESSION["warenkorb"] = [];

    // Datenbankverbindung schließen
    mysqli_close($conn);

    // Weiterleitung zur Warenkorbseite
    $_SESSION["bestellung_status"] = "Ihre Bestellung wurde erfolgreich abgeschlossen!";
    header("Location: warenkorb.php");
    exit();
} catch (mysqli_sql_exception $e) {
    // Bei einem Fehler wird die Transaktion zurückgesetzt
    $conn->rollback();
    mysqli_close($conn);
    // Fehlermeldung setzen und zur Warenkorbseite weiterleiten
    $_SESSION["bestellung_error"] = "Fehler beim Abschließen der Bestellung!";
    header("Location: warenkorb.php");
    exit();
}
