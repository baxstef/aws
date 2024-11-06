<?php

function getPlayerValues($html, $playerName, $teamInitials)
{
    // Carica il documento HTML con DOMDocument
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Suppress warnings from malformed HTML
    $dom->loadHTML($html);
    libxml_clear_errors();

    // Crea un oggetto DOMXPath
    $xpath = new DOMXPath($dom);

    // Trova tutte le righe della tabella che corrispondono ai giocatori
    $rows = $xpath->query("//tr[contains(@class, 'player-row')]");

    foreach ($rows as $row) {
        // Estrai il nome del giocatore
        $nameNode = $xpath->query(".//th[@class='player-name']//span", $row);
        $teamNode = $xpath->query(".//td[@class='player-team']", $row);

        if ($nameNode->length > 0 && $teamNode->length > 0) {
            $name = trim($nameNode->item(0)->nodeValue);
            $team = trim($teamNode->item(0)->nodeValue);

            // Verifica se il nome del giocatore e le iniziali della squadra corrispondono
            if (strcasecmp($name, $playerName) === 0 && strcasecmp($team, $teamInitials) === 0) {
                // Estrai i valori di QI e QA
                $qiNode = $xpath->query(".//td[@data-col-key='c_qi']", $row);
                $qaNode = $xpath->query(".//td[@data-col-key='c_qa']", $row);

                if ($qiNode->length > 0 && $qaNode->length > 0) {
                    $qi = trim($qiNode->item(0)->nodeValue);
                    $qa = trim($qaNode->item(0)->nodeValue);

                    // Restituisci i valori trovati
                    return [
                        'QI' => $qi,
                        'QA' => $qa
                    ];
                }
            }
        }
    }

    // Se il giocatore non è stato trovato, restituisci null
    return null;
}



// da result.php
function fix_duplicate_ids($html)
{
    // Crea un'istanza di DOMDocument
    $dom = new DOMDocument();

    // Ignora gli eventuali errori relativi al formato HTML
    libxml_use_internal_errors(true);

    // Carica il contenuto HTML
    $dom->loadHTML($html);

    // Trova tutti gli elementi con ID duplicati
    $duplicates = array();
    $ids = array();
    foreach ($dom->getElementsByTagName('*') as $node) {
        if ($node->hasAttribute('id')) {
            $id = $node->getAttribute('id');
            if (in_array($id, $ids)) {
                $duplicates[] = $node;
            } else {
                $ids[] = $id;
            }
        }
    }

    // Genera nuovi ID univoci per gli elementi duplicati
    foreach ($duplicates as $node) {
        $id = $node->getAttribute('id');
        $new_id = $id . '-' . uniqid();
        $node->setAttribute('id', $new_id);
    }

    // Restituisce il contenuto HTML corretto
    return $dom->saveHTML();
}


function checksoprasotto($squadra, $squadresopra, $squadresotto, $calsnome, $calsrank, $calsindex, $lasquadra)
{
    $squadra = substr($squadra, 0, -6);
    $squadra = pulisciStringa($squadra);
    $squadra = trim($squadra);
    $calsnome = array_map('trim', $calsnome);

    // Rimuovi caratteri indesiderati
    $squadra = str_replace([':', 'dopo le sostituzioni'], '', $squadra);
    $calsnome = array_map(function ($value) {
        return str_replace([':', 'dopo le sostituzioni'], '', $value);
    }, $calsnome);

    $key = array_search($squadra, $calsnome);
    $keylasquadra = array_search($lasquadra, $calsnome);

    $result = [];

    if ($key !== false && $keylasquadra !== false) {
        $puntis = (float)$calsrank[$key];
        $puntilasquadra = (float)$calsrank[$keylasquadra];

        $div = "<div id='team-" . htmlspecialchars($squadra) . "'>";
        $div .= "<button onclick='toggleTeamDetails(\"" . htmlspecialchars($squadra) . "\")' data-team-id='" . htmlspecialchars($squadra) . "' class='btn btn-success btnpiu'>+</button> ";
        $div .= "<strong>" . htmlspecialchars($squadra) . "</strong>: ";

        if (in_array($squadra, $squadresopra, true)) {
            $ifsoprasotto = "+";
            $sopra = $puntis - $puntilasquadra;
            $div .= " + " . $sopra;
            $div .= "</div><div id='show-" . htmlspecialchars($squadra) . "' style='display:none;'></div>";
            $result[] = ['div' => $div, 'ifsoprasotto' => $ifsoprasotto, 'punti' => $sopra];
        }

        if (in_array($squadra, $squadresotto, true)) {
            $ifsoprasotto = "-";
            $sotto = $puntilasquadra - $puntis;
            $div .= " - " . $sotto;
            $div .= "</div><div id='show-" . htmlspecialchars($squadra) . "' style='display:none;'></div>";
            $result[] = ['div' => $div, 'ifsoprasotto' => $ifsoprasotto, 'punti' => $sotto];
        }
    } else {
        $div = "<div><strong>" . htmlspecialchars($squadra) . "</strong>: Squadra o lasquadra non trovate.</div>";
        $result[] = ['div' => $div, 'ifsoprasotto' => '', 'punti' => 0];
    }
    return $result;
}
