<?php
// Verifica se il form è stato inviato e se un file è stato caricato
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    // Controlla che il file sia stato caricato correttamente
    if ($_FILES["file"]["error"] === UPLOAD_ERR_OK) {
        // Percorso temporaneo del file caricato
        $fileTmpPath = $_FILES['file']['tmp_name'];

        // Legge il contenuto del file caricato
        $contenuto = file_get_contents($fileTmpPath);

        // Elimina tutto prima del div con class="tab-content transfers-tab-content no-border loading-box"
        $pattern_inizio = '/.*?<div class="col-xs-12 no-padding mb-4 wrapped" data-wrap-uid="7">
                    <div class="ranking widget box raised" data-competition-type="2">/s';
        //  $pattern_inizio = '/.*?<div class="ranking widget box raised" data-competition-type="2">/s';
        $contenuto = preg_replace($pattern_inizio, '<div class="col-xs-12 no-padding mb-4 wrapped" data-wrap-uid="3">
', $contenuto, 1);

        // Elimina tutto dopo <!--SIDE CONTENTS Else-->
        $pattern_fine = '/<!--SIDE CONTENTS Else-->.*$/s';
        $contenuto = preg_replace($pattern_fine, '', $contenuto, 1);

        // Specifica il percorso del file da sovrascrivere
        $file = 'classifica.html';

        // Scrive il contenuto rimanente nel file (sovrascrivendo il precedente)
        file_put_contents($file, $contenuto);

        echo "Il contenuto è stato salvato con successo in $file";
    } else {
        echo "Errore durante il caricamento del file.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload File HTML e Salva Contenuto</title>
</head>

<body>

    <h2>Carica un file HTML</h2>

    <!-- Form per l'upload del file -->
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="file" accept=".html"><br><br>
        <input type="submit" value="Carica e Salva">
    </form>

</body>

</html>