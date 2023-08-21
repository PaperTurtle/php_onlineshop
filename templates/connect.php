<?php

/**
 * Gibt eine Fehlermeldung aus.
 * 
 * @param string $message Die Fehlermeldung, die ausgegeben werden soll.
 * @return void
 */
if (!function_exists('displayError')) {
    function displayError(string $message): void
    {
        include 'templates/svgTemplate.html';

        echo "
        <div class='alert alert-danger alert-dismissible fade show' role='alert'>
            <svg class='bi flex-shrink-0 me-2' width='24' height='24' role='img' aria-label='Warning:'><use xlink:href='#exclamation-triangle-fill'/></svg>$message
        </div>";
    }
}

// Datenbankverbindung herstellen
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lebensmittel_onlineshop";

try {
    $conn = new mysqli($servername, $username, $password, $dbname, 3307);
} catch (mysqli_sql_exception $e) {
    displayError("Verbindung zur Datenbank fehlgeschlagen: " . $e->getMessage());
} catch (Exception $e) {
    displayError($e->getMessage());
}
