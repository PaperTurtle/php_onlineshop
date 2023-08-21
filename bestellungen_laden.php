<?php
session_start();
include 'templates/connect.php';

$benutzer_id = $_SESSION['benutzer_id'];

$currentURL = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$targetURL = "http://localhost/Garten_2/bestellungen_laden.php";
if ($currentURL === $targetURL) {
    // Redirect to profil.php
    header("Location: profil.php");
    exit();
}

// Bestellungen abrufen (5 pro Seite)
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$loadMoreQuery = "SELECT * FROM bestellungen WHERE benutzer_id = ? ORDER BY bestelltDatum DESC LIMIT ?, 5";
$stmt = $conn->prepare($loadMoreQuery);
$stmt->bind_param("ii", $benutzer_id, $start);
$stmt->execute();
$bestellungenResult = $stmt->get_result();
$stmt->close();

if ($bestellungenResult->num_rows > 0) : ?>
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
                                                        // Produktinformationen abrufen
                                                        $produkt_id = $orderedItem['produkt_id'];
                                                        $menge = $orderedItem['menge'];
                                                        $einzelpreis = $orderedItem['einzelpreis'];

                                                        $produktQuery = "SELECT * FROM produkte WHERE produkt_id = ?";
                                                        $stmt = $conn->prepare($produktQuery);
                                                        $stmt->bind_param("i", $produkt_id);
                                                        $stmt->execute();
                                                        $produktResult = $stmt->get_result();
                                                        $produkt = $produktResult->fetch_assoc();
                                                        $stmt->close();

                                                        $produktName = $produkt['name'];
                                                        $gesamtPreis = number_format($menge * $einzelpreis, 2);
                                                        $preisAlleProdukte .= $gesamtPreis;
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
                                                <?php else : ?>
                                                    <li class="list-group-item">Keine bestellten Artikel gefunden.</li>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="row my-4">
                                            <div class="col-md-4 offset-md-8 col-lg-3 offset-lg-9">
                                                <p class="lead fw-bold mb-0 font-monospace" style="color: #332D2D;"><?= $preisAlleProdukte ?> €</p>
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
    <h2 id="keineBestellungenHeadingMehr" class="small text-muted mb-1">Keine weiteren Bestellungen gefunden.</h2>
    <script>
        $(document).ready(function() {
            const loadButton = $('#load_more');
            loadButton.remove();
        });
    </script>
<?php endif; ?>
<?php $conn->close(); ?>