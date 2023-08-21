<?php

session_start();

// Prüfen, ob der Benutzer angemeldet ist
if (isset($_SESSION['username'])) {
    // Benutzer abmelden und die Sitzungsvariablen löschen
    session_destroy();
    session_write_close();
    session_regenerate_id();
    // Weiterleitung zur Startseite oder einer anderen passenden Seite
    session_start();
    $_SESSION["success_logout"] = "Sie wurden erfolgreich abgemeldet. Bis zum nächsten Mal!";
} else {
    $_SESSION["error_logout"] = "Sie sind nicht angemeldet!";
}
header("Location: index.php");
exit();
