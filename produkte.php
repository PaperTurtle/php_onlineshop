<?php
session_start();
require_once 'templates/head.php';
require_once 'templates/navbar.php';

/**
 * Erstellt eine Bootstrap-Karte für ein Produkt und gibt sie als HTML-Code zurück
 * 
 * @param array $row Ein Array mit den Produktdaten
 * @param int $index Der Index der Produktkarte
 * @return string HTML-Code für eine Bootstrap-Karte
 */
function createProductCard(array $row, int $index): string
{
    return '
        <div class="col-md-3 mb-4 card-fade" style="display: none;" data-index="' . $index . '">
            <div class="card h-100">
                <img src="data:image/jpeg;base64,' . base64_encode($row['bild']) . '" class="card-img-top mx-auto" style="height: 200px; width: 200px;" alt="' . $row["name"] . '">
                <div class="card-body">
                    <h5 class="card-title">' . $row["name"] . '</h5>
                    <p class="text-muted fw-light fs-6">' . $row["kategorie"] . '</p>
                    <p class="card-text fst-italic">' . $row["beschreibung"] . '</p>
                    <p class="card-text font-monospace">' . $row["preis"] . ' € pro Packung</p>
                    <a href="produkt.php?id=' . $row["produkt_id"] . '" class="btn btn-success">Details <i class="fa-solid fa-angles-right"></i></a>
                </div>
            </div>
        </div>
    ';
}

$index = 0;
?>

<main background-color="eee" class="min-vh-100">
    <div class="container-fluid p-3">
        <form action="" method="GET" class="search-form mt-4 mb-6">
            <div class="row justify-content-md-start">
                <div class="col-md-3 mb-3">
                    <div class="input-group">
                        <div class="form-outline">
                            <input type="text" name="search" id="search" class="form-control">
                            <label class="form-label" for="search">Suche nach Namen</label>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="input-group">
                        <select name="category" id="category" class="form-select">
                            <option value="">Alle Kategorien</option>
                            <option value="Obst">Obst</option>
                            <option value="Gemüse">Gemüse</option>
                            <option value="Proteine">Proteine</option>
                            <option value="Milchprodukte">Milchprodukte</option>
                            <option value="Backwaren">Backwaren</option>
                            <option value="Frühstück">Frühstück</option>
                            <option value="Grundnahrungsmittel">Grundnahrungsmittel</option>
                            <option value="Fisch">Fisch</option>
                            <option value="Süßigkeiten">Süßigkeiten</option>
                            <option value="Getränke">Getränke</option>
                            <option value="Brotaufstriche">Brotaufstriche</option>
                            <option value="Soßen">Soßen</option>
                            <option value="Öle">Öle</option>
                            <option value="Würzmittel">Würzmittel</option>
                        </select>
                        <button type="submit" class="btn btn-success">Filtern <i class="fa-solid fa-arrow-down-short-wide"></i></button>
                    </div>
                </div>
            </div>
        </form>
        <div class="row">
            <?php
            // Datenbankverbindung herstellen
            require_once 'templates/connect.php';

            // SQL-Query vorbereiten
            $sql = "SELECT * FROM produkte";

            // Überprüfen, ob eine Produktsuche durchgeführt wurde
            if (isset($_GET['search'])) {
                $search = $_GET['search'];
                // Produktnamen im SQL-Query filtern
                $sql .= " WHERE name LIKE '%$search%'";
            }

            // Überprüfen, ob eine Kategorieauswahl getroffen wurde
            if (isset($_GET['category'])) {
                $category = $_GET['category'];
                if (!empty($category)) {
                    // Kategorie im SQL-Query filtern
                    $sql .= " AND kategorie = '$category'";
                }
            }

            $result = $conn->query($sql);

            // Überprüfen,ob Produkte gefunden wurden
            if ($result->num_rows > 0) {
                // Produkte als Bootstrap-Karten darstellen
                while ($row = $result->fetch_assoc()) {
                    echo createProductCard($row, $index);
                    $index++;
                }
            } else {
                // Keine Produkte gefunden -> Fehlermeldung anzeigen
                require_once "templates/svgTemplate.html"; ?>
                <div class='alert alert-warning fade show' role='alert'>
                    <svg class='bi flex-shrink-0 me-2' width='24' height='24' role='img' aria-label='Alert:'>
                        <use xlink:href='#exclamation-triangle-fill' />
                    </svg>
                    Keine Produkte gefunden.
                </div>
            <?php }
            mysqli_close($conn);
            ?>
        </div>
    </div>
</main>
<script>
    $(document).ready(function() {
        let cards = $(".card-fade");
        cards.each(function(index) {
            let card = $(this);
            setTimeout(function() {
                card.fadeIn("slow");
            }, 30 * index);
        });
    });
</script>

<?php require_once 'templates/footer.php'; ?>