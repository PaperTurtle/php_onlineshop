<?php
session_start();

/**
 * Führt eine DELETE-Query aus
 * 
 * @param mysqli $conn Datenbankverbindung
 * @param string $query SQL-Query 
 * @param string $param_type Parameter-Typen 
 * @param string $param_value Parameter-Werte 
 */
function executeDeleteQuery(mysqli $conn, string $query, string $param_type, string $param_value): void
{
    $stmt = $conn->prepare($query);
    $stmt->bind_param($param_type, $param_value);
    $stmt->execute();
    $stmt->close();
}

if (!isset($_SESSION['benutzer_id'])) {
    $_SESSION["not_logged_in"] = true;
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

$benutzer_id = $_SESSION["benutzer_id"];
require_once "templates/connect.php";

try {
    // Überprüfen, ob der Benutzer derzeit auf der Seite "benutzer_entfernen.php" ist
    $currentURL = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $targetURL = "http://localhost/Garten_2/benutzer_entfernen.php";
    if ($currentURL === $targetURL) {
        $_SESSION["user_restricted_delete"] = true;
        header("Location: profil.php");
        exit();
    }
    $conn->begin_transaction();

    // Lösche Benutzer aus der Tabelle "benutzer"
    $deleteUserQuery = "DELETE FROM benutzer WHERE benutzer_id = ?";
    executeDeleteQuery($conn, $deleteUserQuery, "i", $benutzer_id);

    // Bestätige die Transaktion, wenn alle DELETEs erfolgreich sind
    $conn->commit();

    session_unset();
    session_destroy();
    // Leite zur Startseite weiter
    $_SESSION["user_delete_success"] = "Dein Benutzerkonto wurde erfolgreich gelöscht!";
    header("Location: index.php");
    exit();
} catch (mysqli_sql_exception $e) {
    // Rollback der Transaktion bei Fehler
    error_log("Benutzer konnte nicht gelöscht werden: " . $e->getMessage());
    $conn->rollback();
    $_SESSION["user_delete_error"] = "Fehler beim Löschen des Profils!";
    header("Location: profil.php");
} finally {
    $conn->close();
}
