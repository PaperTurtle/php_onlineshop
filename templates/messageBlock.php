<?php
include "svgTemplate.html";

/**
 * Erstellt einen Meldungsblock mit Bootstrap-Alerts
 * 
 * @param string $type Typ der Meldung (success, info, warning, danger)
 * @param string $icon Icon der Meldung (siehe Fontawesome Icons)
 * @param string $message Meldung
 */
function generateMessageBlock(string $type, string $icon, string $message): void
{
    echo "
    <div class='flash-alert'>
        <div class='alert alert-$type alert-dismissible fade show' role='alert'>
            <svg class='bi flex-shrink-0 me-2' width='24' height='24' role='img' aria-label='Alert:'>
                <use xlink:href='#$icon' />
            </svg>
            $message
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>
    </div>
    <script src='./js/flashVanish.js'></script>";
}

/**
 * Zeigt eine Meldung aus der Session an und entfernt sie danach
 * 
 * @param string $type Typ der Meldung (success, info, warning, danger)
 * @param string $icon Icon der Meldung (siehe Fontawesome Icons)
 * @param string $sessionKey Key der Session (optional)
 * @param string $message Meldung (optional)
 * 
 * @return void 
 */
function showMessageFromSession(string $type, string $icon, ?string $sessionKey = null, ?string $message = null): void
{
    if ($message !== null && isset($_SESSION[$sessionKey])) {
        generateMessageBlock($type, $icon, $message);
        unset($_SESSION[$sessionKey]);
        return;
    }
    if ($sessionKey !== null && isset($_SESSION[$sessionKey])) {
        generateMessageBlock($type, $icon, $_SESSION[$sessionKey]);
        unset($_SESSION[$sessionKey]);
        return;
    }
}
