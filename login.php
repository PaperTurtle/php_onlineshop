<?php
ob_start();
session_start();
require_once 'templates/head.php';
require_once 'templates/navbar.php';

// Überprüfen, ob der Benutzer bereits angemeldet ist
if (isset($_SESSION['username'])) {
    // Check, ob der Benutzer von einer anderen Seite weitergeleitet wurde
    if (isset($_SESSION['redirect_url'])) {
        $redirectUrl = $_SESSION['redirect_url'];
        unset($_SESSION['redirect_url']);
        header("Location: $redirectUrl");
    } else {
        // Benutzer ist bereits angemeldet -> Weiterleitung zur Startseite
        $_SESSION["already_logged_in"] = "Sie sind bereits angemeldet!";
        header("Location: index.php");
    }
    exit();
}

if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Überprüfen, ob das Formular abgeschickt wurde (Login-Button geklickt?)
if (isset($_POST['login'])) {
    require_once 'templates/connect.php';

    // Benutzername und Passwort aus dem Formular auslesen
    $username = filter_var($_POST['username'], FILTER_UNSAFE_RAW);
    $password = $_POST['password'];

    // Vorbereiten der SELECT-Anweisung, um den Benutzer aus der Datenbank abzurufen
    $selectQuery = "SELECT benutzer_id, passwort, vorname FROM benutzer WHERE username = ?";
    $stmt        = $conn->prepare($selectQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row            = $result->fetch_assoc();
        $hashedPassword = $row['passwort'];

        // Überprüfen, ob das eingegebene Passwort mit dem gehashten Passwort in der Datenbank übereinstimmt
        if (password_verify($password, $hashedPassword)) {
            // Anmeldung erfolgreich
            $_SESSION['username']    = $username;
            $_SESSION['benutzer_id'] = $row['benutzer_id'];
            $_SESSION["benutzer_vorname"] = $row["vorname"];

            // Schließen des vorbereiteten Statements
            $stmt->close();

            // Schließen der Datenbankverbindung
            $conn->close();

            // Weiterleitung zur Startseite oder einer anderen passenden Seite
            if (isset($_SESSION['redirect_url'])) {
                $redirectUrl = $_SESSION['redirect_url'];
                unset($_SESSION['redirect_url']); // Clear the redirect URL from the session
                header("Location: $redirectUrl");
            } else {
                header("Location: index.php");
            }
        } else {
            // Falsches Passwort
            $_SESSION['login_error'] = "Falsches Passwort. Bitte versuche es erneut.";
        }
    } else {
        // Benutzer nicht gefunden
        $_SESSION['login_error'] = "Benutzer nicht gefunden. Bitte überprüfe deine Eingaben.";
    }
    // Schließen des vorbereiteten Statements und der Datenbankverbindung im Fehlerfall
    $stmt->close();
    $conn->close();
}
ob_end_flush();
?>
<main class="vh-90">
    <?php
    require_once "templates/messageBlock.php";
    showMessageFromSession(type: "success", icon: "check-circle-fill", sessionKey: "registration_success");
    showMessageFromSession(type: "success", icon: "check-circle-fill", sessionKey: "password_reset", message: "Ihr Passwort wurde erfolgreich zurückgesetzt!");
    showMessageFromSession(type: "success", icon: "check-circle-fill", sessionKey: "success_forgot_password");
    showMessageFromSession(type: "danger", icon: "exclamation-triangle-fill",  sessionKey: "login_error");
    showMessageFromSession(type: "primary", icon: "info-fill", sessionKey: "not_logged_in", message: "Sie müssen angemeldet sein, um diese Seite zu sehen!");
    ?>
    <section class="vh-80 p-3" style="background-color: #eee; border-radius: 0.5rem;">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col col-xl-10">
                    <div class="card" style="border-radius: 1rem;">
                        <div class="row g-0">
                            <div class="col-md-6 col-lg-5 d-none d-md-block">
                                <img src="./img/login_img.jpg" class="img-fluid fade-in-img" style="border-radius: 1rem 0 0 1rem;" alt="Login Image" />
                            </div>
                            <div class="col-md-6 col-lg-7 d-flex align-items-center">
                                <div class="card-body p-4 p-lg-5 text-black">
                                    <form method="POST" action="login.php" class="needs-validation g-3" novalidate>
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"]; ?>">
                                        <div class="d-flex align-items-center mb-3 pb-1">
                                            <img src="./img/lebensmittel.png" class="img-fluid" style="width:100px; height:100px" alt="Lebensmittelshop-Logo" />
                                            <span class="h1 fw-bold mb-0">Geschmacksgarten</span>
                                        </div>
                                        <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Anmeldung</h5>
                                        <div class="form-outline input-group mb-4">
                                            <span class="input-group-text" id="inputGroupPrepend">@</span>
                                            <input type="text" name="username" class="form-control" id="floatingUsername" required aria-describedby="inputGroupPrepend">
                                            <label for="floatingUsername" class="form-label">Benutzername</label>
                                            <div class="invalid-feedback">
                                                Bitte gib einen Benutzernamen ein!
                                            </div>
                                        </div>
                                        <div class="form-outline mb-4">
                                            <input type="password" name="password" class="form-control" id="floatingPassword" required>
                                            <label for="floatingPassword" class="form-label">Passwort</label>
                                            <div class="invalid-feedback">
                                                Bitte gib ein Passwort ein!
                                            </div>
                                        </div>
                                        <div class="pt-1 mb-4">
                                            <button class="btn btn-dark btn-lg btn-block" name="login" type="submit">Login <i class="fa-solid fa-check"></i></button>
                                        </div>
                                        <a class="small text-muted" href="forgot_password.php">Passwort vergessen?</a>
                                        <div class="d-flex align-items-center justify-content-center pb-4">
                                            <p class="mb-0 me-2">Noch kein Profil?</p>
                                            <a href="register.php" class="btn btn-outline-dark">Registriere dich</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'templates/footer.php'; ?>