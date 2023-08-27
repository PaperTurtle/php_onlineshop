<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require_once 'templates/head.php';
require_once 'templates/navbar.php';
require_once 'templates/userData.php';
getFullUserData();
?>

<main class="vh-50">
    <?php
    require_once "templates/messageBlock.php";
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'] ?? $fullName;
        $email = $_POST['email'] ?? $email;
        $nachricht = $_POST['nachricht'];

        try {
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

            $mail->setFrom($email, $name);
            $mail->addAddress('geschmacksgarten@gmail.com', "Kundenemail");

            $mail->isHTML(true);
            $mail->Subject = 'Neue Nachricht von ' . $name . ' über das Kontaktformular';
            $mail->Body = '
            <html>
            <body style="color: #000; font-size: 16px; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;">
                <div id="wrapper" style="max-width: 600px; margin: auto auto; padding: 20px;">
                    <div id="content" style="font-size: 16px; padding: 25px; background-color: #fff; border-radius: 10px; border-color: #A3D0F8; border-width: 4px 1px; border-style: solid;">
                        <h1 style="font-size: 22px;">
                            <center>Kontaktformular Nachricht</center>
                        </h1>
                        <p><u>Hier sind die Details der Kontaktformular-Nachricht:</u></p>
                        <p><strong>Name: </strong> ' . $name . '</p>
                        <p><strong>Email: </strong> ' . $email . '</p>
                        <p><strong>Nachricht: </strong> ' . $nachricht . '</p>
                    </div>
                </div>
                <div id="footer" style="margin-bottom: 20px; padding: 0px 8px; text-align: center;">
                    <p><em><small>&copy; Geschmacksgarten</small></em></p>
                </div>
            </body>
            </html>';

            $mail->send();
            $_SESSION["successful_message"] = true;
            showMessageFromSession(type: "success", icon: "check-circle-fill", sessionKey: "successful_message", message: "Deine Nachricht wurde erfolgreich versendet!");
        } catch (Exception $e) {
            showMessageFromSession(type: "danger", icon: "exclamation-circle-fill", sessionKey: "error_message", message: "Es ist ein Fehler aufgetreten. Bitte versuche es später erneut.");
        }
    }
    ?>
    <section class="text-center p-4">
        <div class="p-5 bg-image bg-fade-in rounded" style="
        background-image: url('./img/contact_img.jpg');
        height: 300px;"></div>
        <div class="card mx-4 mx-md-5 shadow-5-strong" style="
        margin-top: -100px;
        background: hsla(0, 0%, 100%, 0.8);
        backdrop-filter: blur(30px);">
            <div class="card-body py-5 px-md-5">
                <div class="row d-flex justify-content-center">
                    <div class="col-lg-8">
                        <h2 class="fw-bold mb-5">Kontakt</h2>
                        <form action="kontakt.php" method="POST" class="needs-validation g-3" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-outline input-group">
                                        <span class="input-group-text" id="inputGroupAt"><i class="fa-solid fa-user fa-sm"></i></span>
                                        <input type="text" id="name" name="name" class="form-control" value="<?= $global_fullName; ?>" required>
                                        <label class="form-label" for="name">Name</label>
                                        <div class="invalid-feedback">
                                            Bitte gib deinen Namen ein!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-outline input-group">
                                        <span class="input-group-text" id="inputGroupAt"><i class="fa-solid fa-envelope fa-sm"></i></span>
                                        <input type="email" id="email" name="email" class="form-control" value="<?= $global_email; ?>" required>
                                        <label class="form-label" for="email">E-Mail</label>
                                        <div class="invalid-feedback">
                                            Bitte gib deine Email ein!
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-outline mb-4">
                                <textarea id="nachricht" name="nachricht" class="form-control" rows="3" placeholder="Schreibe deine Nachricht hier..." required></textarea>
                                <div class="invalid-feedback">
                                    Bitte schreibe eine Nachricht!
                                </div>
                            </div>
                            <div class="pt-1 mb-4">
                                <button name="kontakt" type="submit" class="btn btn-dark btn-lg">Absenden
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'templates/footer.php'; ?>