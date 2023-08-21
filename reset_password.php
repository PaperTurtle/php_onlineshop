<?php

session_start();
require_once 'templates/head.php';
require_once 'templates/navbar.php';

/**
 * Weiterleitung mit Fehlermeldung
 * @param string $errorMsg Fehlermeldung
 * @return void 
 */
function redirectWithError(string $errorMsg): void
{
    $_SESSION['reset_password_error'] = $errorMsg;
    header("Location: reset_password.php");
    exit();
}

// Prüfen, ob der Benutzer bereits angemeldet ist
if (isset($_SESSION['username'])) {
    $_SESSION["already_logged_in"] = "Sie können Ihr Passwort nicht ändern wenn sie angemeldet sind!";
    header("Location: index.php");
    exit();
}

// Prüfen, ob das Formular zum Zurücksetzen des Passworts übermittelt wurde
if (isset($_POST["reset-password-submit"])) {
    require 'templates/connect.php';

    $selector = $_POST["selector"];
    $validator = $_POST["validator"];
    $password = $_POST["password"];
    $currentDate = date("U");

    // Überprüfen, ob die Zurücksetzungsanfrage gültig ist
    $sql = "SELECT * FROM passwordreset WHERE passwordResetSelector = ? AND passwordResetExpires >= ?";
    $stmt = mysqli_stmt_init($conn);

    // Überprüfen, ob das Prepared Statement funktioniert
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        redirectWithError("Sie müssen Ihre Reset-Anfrage erneut einreichen.");
    }

    mysqli_stmt_bind_param($stmt, "ss", $selector, $currentDate);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    // Wenn die Zurücksetzungsanfrage ungültig ist
    if (!$row) {
        redirectWithError("Sie müssen Ihre Reset-Anfrage erneut einreichen.");
    }

    // Token überprüfen und Passwort aktualisieren
    $tokenBin = hex2bin($validator);
    $tokenCheck = password_verify($tokenBin, $row["passwordResetToken"]);

    // Wenn der Token ungültig ist
    if (!$tokenCheck) {
        redirectWithError("Sie müssen Ihre Reset-Anfrage erneut einreichen.");
    }

    $tokenEmail = $row["passwordResetEmail"];

    // Benutzerdaten abrufen
    $sql = "SELECT * FROM benutzer WHERE email = ?";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        redirectWithError("Ein Fehler ist aufgetreten!");
    }

    // Benutzerdaten abrufen
    mysqli_stmt_bind_param($stmt, "s", $tokenEmail);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    // Wenn der Benutzer nicht existiert
    if (!$row) {
        redirectWithError("Ein Fehler ist aufgetreten!");
    }

    // Passwort aktualisieren und Zurücksetzungsanfrage bereinigen
    $sql = "UPDATE benutzer SET passwort = ? WHERE email = ?";
    $stmt = mysqli_stmt_init($conn);

    // Überprüfen, ob das Prepared Statement funktioniert
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        redirectWithError("Ein Fehler ist aufgetreten!");
    }

    // Passwort aktualisieren
    $newPwdHash = password_hash($password, PASSWORD_DEFAULT);
    mysqli_stmt_bind_param($stmt, "ss", $newPwdHash, $tokenEmail);
    mysqli_stmt_execute($stmt);

    // Zurücksetzungsanfrage bereinigen
    $sql = "DELETE FROM passwordreset WHERE passwordResetEmail = ?";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        redirectWithError("Ein Fehler ist aufgetreten!");
    }

    mysqli_stmt_bind_param($stmt, "s", $tokenEmail);
    mysqli_stmt_execute($stmt);
    $_SESION["reset_password"] = true;
    header("Location: login.php");
    exit();
}
?>
<main>
    <?php
    require_once "templates/messageBlock.php";
    showMessageFromSession(type: "danger", icon: "exclamation-triangle-fill",  sessionKey: "reset_password_error");
    ?>
    <section class="vh-80 p-3" style="background-color: #eee; border-radius: 0.5rem;">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-md-4">
                    <div class="card text-center" style="width: 500px;">
                        <div class="card-header h5 text-white bg-dark">Passwort zurücksetzen</div>
                        <div class="card-body px-5">
                            <?php
                            $selector = $_GET["selector"] ?? null;
                            $validator = $_GET["validator"] ?? null;
                            if (empty($selector) || empty($validator)) {
                                echo "
                                <div class='alert alert-danger fade show' role='alert'>
                                    <svg class='bi flex-shrink-0 me-2' width='24' height='24' role='img' aria-label='Alert:'>
                                        <use xlink:href='#exclamation-triangle-fill' />
                                    </svg>
                                    Konnte deinen Antrag nicht validieren!
                                </div>";
                            } else {
                                // Prüfen, ob der Token gültig ist 
                                if (ctype_xdigit($selector) !== false && ctype_xdigit($validator) !== false) { ?>
                                    <form method="POST" action="reset_password.php" class="needs-validation g-3" novalidate>
                                        <input type="hidden" name="selector" value="<?= $selector ?>">
                                        <input type="hidden" name="validator" value="<?= $validator ?>">
                                        <p class="card-text py-2">
                                            Gib dein neues Passwort ein.
                                        </p>
                                        <div class="form-outline mb-5">
                                            <input type="password" id="password" name="password" class="form-control my-3" required />
                                            <label class="form-label" for="password">Passwort</label>
                                            <div class="invalid-feedback">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-dark w-100" name="reset-password-submit">Reset</button>
                                    </form>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'templates/footer.php'; ?>