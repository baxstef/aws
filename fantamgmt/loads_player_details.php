<?php
if (isset($_GET['player'])) {
    $playerName = $_GET['player'];
    // Effettua la logica necessaria per ottenere i dettagli del giocatore
    // E.g., fetch information from a database or API
    echo "Dettagli per il giocatore: " . $playerName;
    // Puoi caricare qui i dettagli effettivi dal link del giocatore o da un'altra sorgente
}
