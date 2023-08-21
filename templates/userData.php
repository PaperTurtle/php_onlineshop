<?php

// Globale Variablen für die Benutzerdaten
global $global_vorname, $global_nachname, $global_fullName, $global_username, $global_email;

/**
 * Holt die vollständigen Benutzerdaten aus der Datenbank basierend auf der Benutzer-ID aus der Sitzung.
 * Die geholten Daten werden in globalen Variablen gespeichert.
 *
 * @return void
 */
function getFullUserData(): void
{
    include "connect.php";

    if (isset($_SESSION["benutzer_id"])) {
        $user_id = $_SESSION["benutzer_id"];

        // Prepared Statement erstellen
        $selectQuery = "SELECT vorname, nachname, email, username FROM benutzer WHERE benutzer_id = ?";
        $stmt = $conn->prepare($selectQuery);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Wenn ein Datensatz gefunden wurde, die Daten in die globalen Variablen speichern
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $GLOBALS['global_vorname'] = $row['vorname'];
            $GLOBALS['global_nachname'] = $row['nachname'];
            $GLOBALS['global_fullName'] = sprintf('%s %s', $row['vorname'], $row['nachname']);
            $GLOBALS['global_username'] = $row['username'];
            $GLOBALS['global_email'] = $row['email'];
        }

        $stmt->close();
        $conn->close();
    }
}
