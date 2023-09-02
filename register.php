<?php
session_start();

require_once 'templates/head.php';
require_once 'templates/navbar.php';

// Überprüfen, ob der Benutzer bereits angemeldet ist
if (isset($_SESSION['username'])) {
    // Weiterleiten zur Startseite 
    $_SESSION["already_logged_in"] = "Sie sind bereits angemeldet!";
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

require_once 'templates/connect.php';

// Überprüfen, ob das Registrierungsformular abgesendet wurde
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register'])) {
    // Benutzerdaten aus dem Formular lesen und filtern
    $username = htmlspecialchars($_POST['username']);
    $vorname = ucfirst(strtolower(htmlspecialchars($_POST['vorname'])));
    $nachname = ucfirst(strtolower(htmlspecialchars($_POST['nachname'])));
    $password = htmlspecialchars($_POST['password']);
    $email = strtolower(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL));

    // Überprüfen, ob der Benutzername bereits existiert
    $checkQueryUsername = "SELECT * FROM benutzer WHERE username = ?";
    $stmt = $conn->prepare($checkQueryUsername);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $checkResultUser = $stmt->get_result();
    $stmt->close();

    $checkQueryEmail = "SELECT * FROM benutzer WHERE email = ?";
    $stmt = $conn->prepare($checkQueryEmail);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $checkResultEmail = $stmt->get_result();
    $stmt->close();

    if ($checkResultUser->num_rows > 0) {
        // Benutzername bereits vergeben
        $_SESSION["register_error"] = "Der Benutzername ist bereits vergeben. Bitte wähle einen anderen Benutzernamen.";
    } else if ($checkResultEmail->num_rows > 0) {
        $_SESSION["register_error"] = "Die Email ist bereits vergeben. Bitte wähle eine andere Email.";
    } else {
        // Benutzer in die Datenbank einfügen
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Passwort hashen
        $insertQuery = "INSERT INTO benutzer (username, vorname, nachname, passwort, email) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param('sssss', $username, $vorname, $nachname, $hashedPassword, $email);

        if ($stmt->execute()) {
            // Benutzer automatisch anmelden
            $_SESSION['benutzer_id'] = $stmt->insert_id;
            // Weiterleiten zur Startseite oder einer anderen passenden Seite
            $_SESSION['registration_success'] = "Die Registrierung war erfolgreich! Du kannst dich jetzt anmelden.";
            header("Location: login.php");
            exit();
        } else {
            // Fehler beim Einfügen in die Datenbank
            $_SESSION["register_error"] = "Fehler beim Speichern der Benutzerdaten. Bitte versuche es erneut.";
        }
    }
}
$conn->close();
?>

<main>
    <?php
    require_once "templates/messageBlock.php";
    showMessageFromSession(type: "danger", icon: "exclamation-triangle-fill", sessionKey: "register_error");
    ?>
    <section class="vh-80" style="background-color: #eee; border-radius: 0.5rem;">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col col-xl-10">
                    <div class="card" style="border-radius: 1rem;">
                        <div class="row g-0">
                            <div class="col-md-6 col-lg-5 d-none d-md-block">
                                <img src="./img/register_2.jpg" class="img-fluid fade-in-img" style="border-radius: 1rem 0 0 1rem;" alt="Register-Image" />
                            </div>
                            <div class="col-md-6 col-lg-7 d-flex align-items-center">
                                <div class="card-body p-4 p-lg-5 text-black">
                                    <form method="POST" class="needs-validation g-3" action="register.php" novalidate>
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"]; ?>">
                                        <div class="d-flex align-items-center mb-3 pb-1">
                                            <img src="./img/lebensmittel.png" class="img-fluid" style="width:100px; height:100px" alt="" />
                                            <span class="h1 fw-bold mb-0">Geschmacksgarten</span>
                                        </div>
                                        <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Registrierung</h5>
                                        <div class="form-outline input-group mb-4">
                                            <span class="input-group-text" id="inputGroupAt"><i class="fa-solid fa-at fa-sm"></i></span>
                                            <input type="text" name="username" class="form-control" id="floatingUsername" required aria-describedby="inputGroupPrepend">
                                            <label for="floatingUsername" class="form-label">Benutzername</label>
                                            <div class="invalid-feedback">
                                                Bitte gib einen Benutzernamen ein!
                                            </div>
                                        </div>
                                        <div class="form-outline mb-4 input-group">
                                            <span class="input-group-text" id="inputGroupAt"><i class="fa-solid fa-user"></i></span>
                                            <input type="text" name="vorname" class="form-control" id="floatingVorname" required>
                                            <label for="floatingVorname" class="form-label">Vorname</label>
                                            <div class="invalid-feedback">
                                                Bitte gib deinen Vornamen ein!
                                            </div>
                                        </div>
                                        <div class="form-outline mb-4 input-group">
                                            <span class="input-group-text" id="inputGroupAt"><i class="fa-solid fa-user"></i></span>
                                            <input type="text" name="nachname" class="form-control" id="floatingNachname" required>
                                            <label for="floatingNachname" class="form-label">Nachname</label>
                                            <div class="invalid-feedback">
                                                Bitte gib deinen Nachnamen ein!
                                            </div>
                                        </div>
                                        <div class="form-outline mb-4 input-group">
                                            <span class="input-group-text" id="inputGroupAt"><i class="fa-solid fa-envelope fa-sm"></i></span>
                                            <input type="email" name="email" class="form-control" id="floatingEmail" required>
                                            <label for="floatingEmail" class="form-label">E-Mail</label>
                                            <div class="invalid-feedback">
                                                Bitte gib deine Email ein!
                                            </div>
                                        </div>
                                        <div class="form-outline mb-4 input-group">
                                            <span class="input-group-text" id="inputGroupAt"><i class="fa-solid fa-key fa-sm"></i></span>
                                            <input type="password" name="password" class="form-control" id="floatingPassword" required>
                                            <label for="floatingPassword" class="form-label">Passwort</label>
                                            <div class="invalid-feedback">
                                                Bitte gib ein Passwort ein!
                                            </div>
                                        </div>
                                        <div class="pt-1 mb-4">
                                            <button class="btn btn-dark btn-lg btn-block" type="submit" name="register">Registrieren <i class="fa-solid fa-check"></i></button>
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