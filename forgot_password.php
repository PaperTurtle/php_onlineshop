<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
include 'templates/head.php';
include 'templates/navbar.php';
include 'templates/connect.php';

if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Wenn das Zurücksetzen-Formular abgesendet wurde
if (isset($_POST['send_reset_email'])) {
    if (!hash_equals($_SESSION["csrf_token"], $_POST["token"])) {
        // CSRF-Token ungültig
        $_SESSION["invalid_csrf_token_message"] = "CSRF-Token ungültig! Bitte versuchen Sie es erneut.";
        header("Location: login.php");
        exit();
    }

    // Eindeutige Zufallszeichenfolge für den Link generieren
    $selector = bin2hex(random_bytes(8));
    $token = random_bytes(32);
    $url = "http://localhost/Garten_2/reset_password.php?selector=" . $selector . "&validator=" . bin2hex($token);
    $expires = date("U") + 1800;
    $userEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    $sql_check_user = "SELECT * FROM benutzer WHERE email = ?";
    $stmt_check_user = mysqli_stmt_init($conn);

    // Überprüfen, ob die E-Mail-Adresse in der Datenbank existiert
    if (!mysqli_stmt_prepare($stmt_check_user, $sql_check_user)) {
        $_SESSION["error_forgot_password"] = "Es ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut.";
        header("Location: forgot_password.php");
        exit();
    }

    // E-Mail-Adresse an das Prepared Statement binden und ausführen 
    mysqli_stmt_bind_param($stmt_check_user, "s", $userEmail);
    mysqli_stmt_execute($stmt_check_user);
    $result_check_user = mysqli_stmt_get_result($stmt_check_user);

    // Wenn die E-Mail-Adresse nicht in der Datenbank existiert -> Fehlermeldung
    if (mysqli_num_rows($result_check_user) <= 0) {
        $_SESSION["error_forgot_password"] = "Es wurde kein Benutzer mit dieser E-Mail-Adresse gefunden.";
        header("Location: forgot_password.php");
        exit();
    }

    // Vorherige Zurücksetzungsanfragen für diese E-Mail löschen
    $sql = "DELETE FROM passwordReset WHERE passwordResetEmail = ?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $_SESSION["error_forgot_password"] = "Es ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut.";
        header("Location: forgot_password.php");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $userEmail);
    mysqli_stmt_execute($stmt);

    // Neue Zurücksetzungsanfrage in der Datenbank speichern
    $sql = "INSERT INTO passwordReset (passwordResetEmail, passwordResetSelector, passwordResetToken, passwordResetExpires) VALUES (?, ?, ?, ?)";
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $_SESSION["error_forgot_password"] = "Es ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut.";
        header("Location: forgot_password.php");
        exit();
    }

    // Token hashen und an das Prepared Statement binden
    $hashedToken = password_hash($token, PASSWORD_DEFAULT);
    mysqli_stmt_bind_param($stmt, "ssss", $userEmail, $selector, $hashedToken, $expires);
    mysqli_stmt_execute($stmt);


    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    try {
        // PHPMailer-Konfiguration und E-Mail senden
        require 'phpmailer/src/Exception.php';
        require 'phpmailer/src/PHPMailer.php';
        require 'phpmailer/src/SMTP.php';

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'geschmacksgarten@gmail.com';
        $mail->Password = "woimhlywrostdecz";
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';
        $mail->ContentType = 'text/html; charset=UTF-8';
        $mail->setFrom('geschmacksgarten@gmail.com', 'Geschmacksgarten');
        $mail->addAddress($userEmail);

        // E-Mail-Inhalt festlegen
        $mail->isHTML(true);
        $mail->Subject = 'Passwort zurücksetzen';
        $mail->Body = '<html>
            <body style="color: #000; font-size: 16px; text-decoration: none; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;">
                <div id="wrapper" style="max-width: 600px; margin:auto auto; padding: 20px;">
                    <div id="content" style="font-size: 16px; padding: 25px; background-color: #fff; border-radius: 10px; border-color: #A3D0F8; border-width: 4px 1px; border-style: solid;">
                        <h1 style="font-size: 22px;">
                            <center>Passwort zurücksetzen</center>
                        </h1>
                        <p>Hallo Kunde!</p>
    
                        <p>Wir haben eine Anfrage erhalten, um Ihr Passwort zurückzusetzen. Keine Sorge, falls Sie diese Anfrage nicht selbst gestellt haben – es könnte sich um ein Versehen handeln. Wenn Sie jedoch tatsächlich Ihr Passwort zurücksetzen möchten, nutzen Sie bitte den folgenden Link:</p>
                        <p style="display:flex; justify-content: center; margin-top:10px;">
                            <center>
                                <a target="_blank" style="border: 1px solid #0561B3; background-color: #238CEA; color: #fff; text-decoration: none; font-size: 18px; padding: 10px 20px;"  href="' . $url . '">Passwort zurücksetzen</a>
                            </center>
                        </p>
                        <p>Bitte beachten Sie, dass dieser Link nur für begrenzte Zeit gültig ist, also stellen Sie sicher, dass Sie die Aktion rechtzeitig abschließen.</p>
                    </div>
                    <div id="footer" style="margin-bottom: 20px; padding: 0px 8px; text-align: center;">
                        <p>Vielen Dank und freundliche Grüße,</p>
                        <p><em><small>Ihr Geschmacksgarten-Team</small></em></p>
                    </div>
                </div>
            </body>
        </html>';

        $mail->send();
        // Erfolgreiche E-Mail-Versendung: Weiterleitung zur Anmeldeseite
        $_SESSION["success_forgot_password"] = "Wir haben Ihnen eine E-Mail mit einem Link zum Zurücksetzen Ihres Passworts gesendet.";
        header("Location: login.php");
    } catch (Exception $e) {
        // Fehler beim Senden der E-Mail: Fehlermeldung anzeigen
        $_SESSION["error_forgot_password"] = "Es ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut.";
        header("Location: forgot_password.php");
        exit();
    }
}
?>
<main style="background-color: #eee">
    <?php
    include "templates/messageBlock.php";
    showMessageFromSession(type: "danger", icon: "exclamation-triangle-fill",  sessionKey: "error_forgot_password");
    ?>
    <section class="vh-80 p-3" style="background-color: #eee; border-radius: 0.5rem;">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-md-4">
                    <div class="card text-center" style="width: 500px;">
                        <div class="card-header h5 text-white bg-dark">Passwort zurücksetzen</div>
                        <div class="card-body px-5">
                            <form method="POST" action="forgot_password.php" class="needs-validation g-3" novalidate>
                                <p class="card-text py-2">
                                    Gib deine E-Mail-Adresse ein, und wir senden dir eine E-Mail mit Anweisungen zum Zurücksetzen deines Passworts.
                                </p>
                                <input type="hidden" name="token" value="<?= $_SESSION["csrf_token"] ?>">
                                <div class="form-outline mb-5">
                                    <input type="email" id="typeEmail" name="email" class="form-control my-3" required />
                                    <label class="form-label" for="typeEmail">Email</label>
                                    <div class="invalid-feedback">
                                        Bitte gib deine Email ein!
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-dark w-100" name="send_reset_email">Reset</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php include 'templates/footer.php'; ?>