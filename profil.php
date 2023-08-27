<?php
ob_start();
session_start();
require_once 'templates/head.php';
require_once 'templates/navbar.php';
require_once 'templates/connect.php';
require_once 'templates/userData.php';

// Überprüfen, ob der Benutzer bereits angemeldet ist
if (!isset($_SESSION['benutzer_id'])) {
  $_SESSION["not_logged_in"] = true;
  $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
  header("Location: login.php");
  exit();
}

if (!isset($_SESSION["csrf_token"])) {
  $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

getFullUserData();

/**
 * Aktualisiert die Benutzerdaten in der Datenbank
 * 
 * @param mysqli $conn MySQLi-Verbindungsobjekt
 * @param int $benutzer_id ID des Benutzers
 * @param string $neuerBenutzername Neuer Benutzername
 * @param string $neueEmail Neue E-Mail-Adresse
 * @param string $neuerVorname Neuer Vorname
 * @param string $neuerNachname Neuer Nachname
 */
function updateUser(mysqli $conn, int $benutzer_id, string $neuerBenutzername, string $neueEmail, string $neuerVorname, string $neuerNachname): void
{
  $updateQuery = "UPDATE benutzer SET username = ?, email = ?, vorname = ?, nachname = ? WHERE benutzer_id = ?";
  $stmt = $conn->prepare($updateQuery);
  $stmt->bind_param("ssssi", $neuerBenutzername, $neueEmail, $neuerVorname, $neuerNachname, $benutzer_id);
  $stmt->execute();
  $stmt->close();
}

// Benutzerdaten abrufen
$benutzer_id = $_SESSION['benutzer_id'];

if (isset($_SESSION["benutzer_id"])) {
  $benutzername = $global_username;
  $email = $global_email;
  $vorname = $global_vorname;
  $nachname = $global_nachname;

  // Benutzerdaten aktualisieren, wenn das Formular abgeschickt wurde
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {

    // CSRF-Token überprüfen
    if (!hash_equals($_SESSION["csrf_token"], $_POST["token"])) {
      // CSRF-Token ungültig
      $_SESSION["invalid_csrf_token_message"] = "CSRF-Token ungültig! Bitte versuchen Sie es erneut.";
      header("Location: profil.php");
      exit();
    }

    $neuerBenutzername = $_POST['benutzername'];
    $neueEmail = $_POST['email'];
    $neuerVorname = $_POST['vorname'];
    $neuerNachname = $_POST['nachname'];

    updateUser($conn, $benutzer_id, $neuerBenutzername, $neueEmail, $neuerVorname, $neuerNachname);

    // Benutzerdaten in der Session aktualisieren
    $_SESSION['username'] = $neuerBenutzername;

    // Redirect back to the same page to refresh
    header("Location: profil.php");
    exit();
  }
} else {
  // Fehler: Benutzerdaten nicht gefunden
  header("Location: index.php");
  exit();
}

// Bestellungen des Benutzers abrufen
$bestellungenQuery = "SELECT * FROM bestellungen WHERE benutzer_id = ? ORDER BY bestelltDatum DESC LIMIT 5";
$stmt = $conn->prepare($bestellungenQuery);
$stmt->bind_param("i", $benutzer_id);
$stmt->execute();
$bestellungenResult = $stmt->get_result();
$stmt->close();
ob_end_flush();
?>

<main class="min-vh-100">
  <?php
  require_once "templates/messageBlock.php";
  showMessageFromSession(type: "danger", icon: "exclamation-triangle-fill", sessionKey: "user_delete_error");
  showMessageFromSession(type: "danger", icon: "exclamation-triangle-fill", sessionKey: "invalid_csrf_token_message");
  ?>
  <!-- Profil -->
  <section>
    <!-- Kundeninformationen -->
    <div class="container py-5">
      <div class="row">
        <!-- Generelle Informationen -->
        <div class="col-lg-4">
          <div class="card mb-4" id="userContainer" style="display:none;">
            <div class="card-body text-center">
              <img src="./img/user_avatar.png" alt="avatar" class="rounded-circle img-fluid" style="width: 150px;">
              <h5 class="my-3"><?= htmlspecialchars($benutzername); ?></h5>
              <p class="text-muted mb-1">Kunde</p>
              <p class="text-muted mb-4">Spandau, Berlin, Deutschland</p>
              <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal" id="deleteButton">
                Konto löschen <i class="fa-solid fa-user-xmark"></i>
              </button>
            </div>
          </div>
          <div class="card mb-4" id="updateUserContainer" style="display:none;">
            <div class="card-body mb-4 mb-lg-0 text-center">
              <div class="card-body p-0">
                <p class="mb-2">Haben sich deine Daten geändert? Möchtest du sie vielleicht ändern?</p>
                <button type="submit" name="update" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateAccountModal">Daten aktualisieren <i class="fa-solid fa-user-pen"></i></button>
              </div>
            </div>
          </div>
        </div>
        <!-- Weitere Informationen -->
        <div class="col-lg-8" id="kontodatenContainer" style="display:none;">
          <div class="card mb-4">
            <div class="card-header">
              <h2 class="mb-0">Kontodaten</h2>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-sm-3">
                  <p class="mb-0">Benutzer ID</p>
                </div>
                <div class="col-sm-9">
                  <p class="text-muted mb-0"><?= $benutzer_id; ?></p>
                </div>
              </div>
              <hr>
              <div class="row">
                <div class="col-sm-3">
                  <p class="mb-0">Vorname</p>
                </div>
                <div class="col-sm-9">
                  <p class="text-muted mb-0"><?= htmlspecialchars($vorname); ?></p>
                </div>
              </div>
              <hr>
              <div class="row">
                <div class="col-sm-3">
                  <p class="mb-0">Nachname</p>
                </div>
                <div class="col-sm-9">
                  <p class="text-muted mb-0"><?= htmlspecialchars($nachname); ?></p>
                </div>
              </div>
              <hr>
              <div class="row">
                <div class="col-sm-3">
                  <p class="mb-0">Email</p>
                </div>
                <div class="col-sm-9">
                  <p class="text-muted mb-0"><?= htmlspecialchars($email); ?></p>
                </div>
              </div>
              <hr>
            </div>
          </div>
          <!-- Bestellungen -->
          <div class="row" id="bestellungenContainer" style="display:none;">
            <div class="container">
              <div class="card">
                <div class="card-header">
                  <h2 class="mb-0">Bestellungen</h2>
                </div>
                <div class="card-body">
                  <div class="accordion accordion-borderless">
                    <div class="accordion" id="accordionFlushExample">
                      <?php if ($bestellungenResult->num_rows > 0) : ?>
                        <?php while ($bestellung = $bestellungenResult->fetch_assoc()) : ?>
                          <?php
                          // Bestellungsdaten abrufen
                          $bestellungs_id = $bestellung['bestellungs_id'];
                          $bestellungsDatum = $bestellung['bestelltDatum'];
                          $gesamtbetrag = $bestellung['gesamtbetrag'];
                          $collapseId = "collapse-" . $bestellungs_id;
                          $preisAlleProdukte = 0;

                          // Bestellte Artikel abrufen
                          $orderedItemsQuery = "SELECT * FROM bestellteprodukte WHERE bestellungs_id = ?";
                          $stmt = $conn->prepare($orderedItemsQuery);
                          $stmt->bind_param("i", $bestellungs_id);
                          $stmt->execute();
                          $orderedItemsResult = $stmt->get_result();
                          $stmt->close();
                          ?>
                          <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-<?= $bestellungs_id; ?>">
                              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId; ?>" aria-expanded="false" aria-controls="<?= $collapseId; ?>"><i class="fas fa-receipt fa-sm me-2 opacity-70"></i>
                                Bestellung #<?= $bestellungs_id; ?> am <?= date('d.m.Y', strtotime($bestellungsDatum)); ?>
                              </button>
                            </h2>
                            <div id="<?= $collapseId; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?= $bestellungs_id; ?>" data-bs-parent="#accordionFlushExample">
                              <div class="accordion-body d-flex justify-content-center align-items-center">
                                <div class="container py-5 h-100" style="width:60em;">
                                  <div class="row d-flex justify-content-center align-items-center h-100">
                                    <div class="col-lg-8 col-xl-10">
                                      <div class="card border-top border-bottom border-3" style="border-color: #332D2D !important;">
                                        <div class="card-body p-5">
                                          <p class="lead fw-bold mb-5" style="color: #332D2D;">Kaufbeleg</p>
                                          <div class="row">
                                            <div class="col mb-3">
                                              <p class="small text-muted mb-1">Bestelldatum</p>
                                              <p><?= date('d.m.Y', strtotime($bestellungsDatum)); ?></p>
                                            </div>
                                            <div class="col mb-3">
                                              <p class="small text-muted mb-1">Bestellungs ID</p>
                                              <p><?= $bestellungs_id; ?></p>
                                            </div>
                                          </div>
                                          <div class="mx-n5 px-5 py-4" style="background-color: #f2f2f2;">
                                            <div class="row">
                                              <?php if ($orderedItemsResult->num_rows > 0) : ?>
                                                <?php while ($orderedItem = $orderedItemsResult->fetch_assoc()) : ?>
                                                  <?php
                                                  // Bestellte Artikel abrufen
                                                  $produkt_id = $orderedItem['produkt_id'];
                                                  $menge = $orderedItem['menge'];
                                                  $einzelpreis = $orderedItem['einzelpreis'];
                                                  // Produkt abrufen
                                                  $produktQuery = "SELECT * FROM produkte WHERE produkt_id = ?";
                                                  $stmt = $conn->prepare($produktQuery);
                                                  $stmt->bind_param("i", $produkt_id);
                                                  $stmt->execute();
                                                  $produktResult = $stmt->get_result();
                                                  $produkt = $produktResult->fetch_assoc();
                                                  $stmt->close();
                                                  // Produkt Daten
                                                  $produktName = $produkt['name'];
                                                  $gesamtPreis = number_format($menge * $einzelpreis, 2);
                                                  $preisAlleProdukte .= $gesamtPreis;
                                                  $preisAlleProdukteFormatted = number_format($preisAlleProdukte + 10, 2, '.', '');
                                                  ?>
                                                  <div class="col-md-6 col-lg-6">
                                                    <p> <?= $produktName; ?></p>
                                                  </div>
                                                  <div class="col-md-1 col-lg-3">
                                                    <p> <?= "x" . $menge; ?> </p>
                                                  </div>
                                                  <div class="col-md-3 col-lg-3">
                                                    <p class="font-monospace"><?= $gesamtPreis; ?> €</p>
                                                  </div>
                                                <?php endwhile; ?>
                                                <div class="col-md-6 col-lg-9">
                                                  <p>Shipping</p>
                                                </div>
                                                <div class="col-md-3 col-lg-3">
                                                  <p class="font-monospace">10.00 €</p>
                                                </div>
                                              <?php else : ?>
                                                <li class="list-group-item">Keine bestellten Artikel gefunden.</li>
                                              <?php endif; ?>
                                            </div>
                                          </div>
                                          <div class="row my-4">
                                            <div class="col-md-4 offset-md-8 col-lg-3 offset-lg-9">
                                              <p class="lead fw-bold mb-0 font-monospace" style="color: #332D2D;"><?= $preisAlleProdukteFormatted ?> €</p>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        <?php endwhile; ?>
                      <?php else : ?>
                        <h2 id="keineBestellungenHeading" class="small text-muted mb-1">Keine Bestellungen gefunden.</h2>
                        <script>
                          $(document).ready(function() {
                            const loadButton = $('#load_more');
                            loadButton.remove();
                          });
                        </script>
                      <?php endif; ?>
                      <?php $conn->close(); ?>
                    </div>
                  </div>
                  <?php if ($bestellungenResult->num_rows > 4) : ?>
                    <div class="text-center pt-3">
                      <button id="load_more" class="btn btn-info">Mehr Bestellungen laden <i class="fa-solid fa-spinner"></i></button>
                      <input type="hidden" id="start" value="5">
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Delete-Modal -->
      <div class="modal fade" id="deleteAccountModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title" id="deleteAccountModalLabel">Konto löschen <i class="fa-solid fa-exclamation"></i> <i class="fa-solid fa-question"></i></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              Möchten Sie wirklich Ihr Benutzerkonto löschen?
              Alle ihre Daten und Bestellungen werden ebenfalls gelöscht.
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen <i class="fa-solid fa-times-circle"></i></button>
              <a href="benutzer_entfernen.php" class="btn btn-danger">Konto löschen <i class="fa-solid fa-trash"></i></a>
            </div>
          </div>
        </div>
      </div>
      <!-- Update-Modal -->
      <div class="modal fade" id="updateAccountModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="updateAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="deleteAccountModalLabel">Konto aktualisieren</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="profil.php" class="needs-validation g-3" novalidate>
              <input type="hidden" name="token" value="<?= $_SESSION["csrf_token"] ?>">
              <div class="modal-body">
                <div class="form-outline input-group mb-4">
                  <span class="input-group-text" id="inputGroupPrepend"><i class="fa-solid fa-at fa-sm"></i></span>
                  <input type="text" name="benutzername" id="benutzername" class="form-control" value="<?= $benutzername; ?>" required>
                  <label for="benutzername" class="form-label" aria-describedby="inputGroupPrepend">Benutzername</label>
                  <div class="invalid-feedback">
                    Bitte gib einen neuen Benutzernamen ein!
                  </div>
                </div>
                <div class="form-outline input-group mb-4">
                  <span class="input-group-text" id="inputGroupPrepend"><i class="fa-solid fa-envelope fa-sm"></i></span>
                  <input type="email" name="email" class="form-control" value="<?= $email; ?>" required>
                  <label for="email" class="form-label">E-Mail</label>
                  <div class="invalid-feedback">
                    Bitte gib deine neue Email ein!
                  </div>
                </div>
                <div class="form-outline input-group mb-4">
                  <span class="input-group-text" id="inputGroupPrepend"><i class="fa-solid fa-user"></i></span>
                  <input type="text" name="vorname" class="form-control" value="<?= $vorname; ?>" required>
                  <label for="vorname" class="form-label">Vorname</label>
                  <div class="invalid-feedback">
                    Bitte gib deinen Namen ein!
                  </div>
                </div>
                <div class="form-outline input-group mb-4">
                  <span class="input-group-text" id="inputGroupPrepend"><i class="fa-solid fa-user"></i></span>
                  <input type="text" name="nachname" class="form-control" value="<?= $nachname; ?>" required>
                  <label for="nachname" class="form-label">Nachname</label>
                  <div class="invalid-feedback">
                    Bitte gib deinen Nachnamen ein!
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen <i class="fa-solid fa-times-circle"></i></button>
                <button type="submit" id="update" name="update" class="btn btn-primary">Aktualisieren <i class="fa-solid fa-sync"></i></button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
<script src="./js/mehrBestellungen.js">
</script>
<script>
  $(document).ready(function() {
    $('#userContainer, #updateUserContainer, #kontodatenContainer, #bestellungenContainer').fadeIn(1000);
  });
  const deleteButton = document.getElementById('deleteButton');
  <?php if (isset($_SESSION["user_restricted_delete"])) { ?>
    deleteButton.click();
    <?php unset($_SESSION["user_restricted_delete"]); ?>
  <?php } ?>
</script>
<?php require_once 'templates/footer.php'; ?>