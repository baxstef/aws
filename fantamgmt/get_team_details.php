<?php
// Abilita il reporting degli errori per il debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "simple_html_dom.php";

// Ottieni il nome della squadra dal parametro GET
$lasquadra = $_GET['squadra'];
if ($lasquadra == "La Compagnia dell Anello") {
    $lasquadra = "La Compagnia dellâ¬ÂÂAnello";
}

libxml_use_internal_errors(true);
$dom = new DOMDocument();
$html = file_get_contents('ultima.html');
$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
$divs = $xpath->query("//div[@data-wrap-uid]");

// Funzione per stampare i giocatori di una squadra
function stampaGiocatori($divs, $nomeSquadra)
{
    $titolari = [];
    $riserve = [];
    $totaleTitolari = 0;

    $nome_giocatore = "Audero";




    // Suppress HTML errors (malformed HTML)
    libxml_use_internal_errors(true);

    // Crea un nuovo DOMDocument
    $dom = new DOMDocument();

    // URL del sito web da cui vogliamo ottenere i dati
    $url = 'https://www.fantacalcio.it/voti-fantacalcio-serie-a';

    // Carica il contenuto della pagina direttamente dal link
    $html = file_get_contents($url);

    if ($html === false) {
        echo "Errore nel caricamento della pagina HTML.";
        exit;
    }

    // Carica l'HTML nel DOMDocument
    $dom->loadHTML($html);

    // Crea un oggetto DOMXPath per eseguire le query XPath
    $xpath = new DOMXPath($dom);

    foreach ($divs as $div) {
        $teamName = $div->getElementsByTagName('h4')->item(0)->textContent;
        if (trim($teamName) == trim($nomeSquadra)) {
            $tables = $div->getElementsByTagName('table');
            foreach ($tables as $table) {
                $rows = $table->getElementsByTagName('tr');
                $team_type = $table->getAttribute('id') == 'formationTable' ? 'titolari' : 'riserve';
                foreach ($rows as $row) {
                    $cols = $row->getElementsByTagName('td');
                    $name = '';
                    foreach ($cols as $col) {
                        if ($col->getAttribute('data-key') == 'name') {
                            // Estrai il nome del giocatore
                            $name = trim($col->textContent);
                            $nome_x_giocatore = trim(substr($name, 8));


                            // Trova il link del giocatore
                            $player_link_element = $col->getElementsByTagName('a')->item(0);
                            if ($player_link_element) {
                                $player_link = $player_link_element->getAttribute('href');
                            }

                            // Crea il link cliccabile del giocatore
                            $player_link_html = "<a href='$player_link' target='_blank'>$name</a>";

                            $voti = estraiVotiGiocatore($xpath, $nome_x_giocatore);



                            // Aggiungi il giocatore alla lista dei titolari o delle riserve
                            // if ($team_type == 'titolari') {
                            //     $titolari[] = $player_link_html . " V: " . $voti['voto'] . " FV: " . $voti['fantavoto'] .
                            //         " <a href='javascript:void(0);' onclick='toggleDetails(\"player-details-$nome_x_giocatore\");'>Dettagli</a>" .
                            //         "<div id='player-details-$nome_x_giocatore' class='player-details' style='display: none;'></div>";
                            // } else {
                            //     $riserve[] = $player_link_html . " V: " . $voti['voto'] . " FV: " . $voti['fantavoto'] .
                            //         " <a href='javascript:void(0);' onclick='toggleDetails(\"player-details-$nome_x_giocatore\");'>Dettagli</a>" .
                            //         "<div id='player-details-$nome_x_giocatore' class='player-details' style='display: none;'></div>";
                            // }
                            //  Aggiungi il giocatore alla lista dei titolari o delle riserve
                            // var_dump($voti);


                            if ($team_type == 'titolari') {
                                $titolari[] = $player_link_html . " V: " . $voti['voto'] . " FV: " . $voti['fantavoto'];
                                //   $totaleTitolari = $voti['voto'] + $totaleTitolari;
                                $totaleTitolari += (float)$voti['fantavoto'];
                            } else {
                                $riserve[] = $player_link_html  . " V: " . $voti['voto'] . " FV: " . $voti['fantavoto'];
                            }
                        }
                    }
                    /* foreach ($cols as $col) {
                        if ($col->getAttribute('data-key') == 'name') {
                            $name = trim($col->textContent);
                            $nome_x_giocatore = trim(substr($name, 8));


                            if ($team_type == 'titolari') {
                                $titolari[] = "." . $name . "-" . "v" . $nome_x_giocatore . " s " . estraiVotiGiocatore($xpath, $nome_x_giocatore);
                            } else {
                                $riserve[] = $name;
                            }
                        }
                    }*/
                }
            }
            break; // Trovata la squadra, esci dal loop
        }
    }

    // Stampa i titolari
    echo "<h2>Titolari</h2>";
    if (!empty($titolari)) {
        foreach ($titolari as $player) {
            echo $player . "<br>";
        }
    } else {
        echo "Nessun titolare trovato.<br>";
    }

    // Stampa le riserve
    echo "<h2>Riserve</h2>";
    if (!empty($riserve)) {
        foreach ($riserve as $player) {
            echo $player . "<br>";
        }
    } else {
        echo "Nessuna riserva trovata.<br>";
    }
    echo "<h3>Tot solo titolari: " . $totaleTitolari . "</h2>";
}

// Chiama la funzione per stampare i giocatori della squadra specificata
stampaGiocatori($divs, $lasquadra);



// Nome del giocatore da cercare


// Funzione per estrarre i voti di un giocatore specifico
function estraiVotiGiocatore($xpath, $nome_giocatore)
{
    // Trova tutte le righe dei giocatori
    $playerRows = $xpath->query("//div[@class='team-table-body']//tbody//tr");

    if ($playerRows->length > 0) {
        foreach ($playerRows as $row) {
            // Cerca il nome del giocatore
            $playerNameNode = $xpath->query(".//a[@class='player-name player-link']/span", $row);
            if ($playerNameNode->length > 0) {
                $nomeTrovato = trim($playerNameNode->item(0)->textContent);

                // Controlla se il nome corrisponde al giocatore cercato
                if ($nomeTrovato === $nome_giocatore) {
                    // Estrai il voto e fantavoto
                    $votoNode = $xpath->query(".//span[contains(@class, 'player-grade')]", $row);
                    $fantavotoNode = $xpath->query(".//span[contains(@class, 'player-fanta-grade')]", $row);

                    if ($votoNode->length > 0 && $fantavotoNode->length > 0) {
                        $voto = $votoNode->item(0)->getAttribute('data-value');
                        $fantavoto = $fantavotoNode->item(0)->getAttribute('data-value');
                        return ['voto' => $voto, 'fantavoto' => $fantavoto];
                    } else {
                        return ['voto' => 'N/A', 'fantavoto' => 'N/A'];
                    }
                }
            }
        }
    }

    // Ritorna N/A se nessun giocatore è trovato
    return ['voto' => 'N/A', 'fantavoto' => 'N/A'];
}
/*
function estraiVotiGiocatore($xpath, $nome_giocatore)
{
    // Trova tutte le righe dei giocatori (tbody > tr)
    $playerRows = $xpath->query("//div[@class='team-table-body']//tbody//tr");

    if ($playerRows->length > 0) {
        foreach ($playerRows as $row) {
            // Cerca il nome del giocatore (assicurandoci di avere il tag corretto)
            $playerNameNode = $xpath->query(".//a[@class='player-name player-link']/span", $row);
            if ($playerNameNode->length > 0) {
                $nomeTrovato = trim($playerNameNode->item(0)->textContent);

                // Controlla se il nome corrisponde a "Audero"
                if ($nomeTrovato === $nome_giocatore) {
                    // echo "Giocatore corrispondente: " . $nome_giocatore . "<br>";

                    // Debug: stampa il contenuto della riga
                    //  echo  $row->C14N();

                    // Estrai il voto e fantavoto dai rispettivi span con data-value
                    $votoNode = $xpath->query(".//span[contains(@class, 'player-grade')]", $row);
                    $fantavotoNode = $xpath->query(".//span[contains(@class, 'player-fanta-grade')]", $row);

                    if ($votoNode->length > 0 && $fantavotoNode->length > 0) {
                        $voto = $votoNode->item(0)->getAttribute('data-value');
                        $fantavoto = $fantavotoNode->item(0)->getAttribute('data-value');

                        // echo "Giocatore: " . $nome_giocatore;
                        echo " V: " . $voto;
                        echo " FV: " . $fantavoto;
                    } else {
                        echo "Voti non trovati per  il giocatore " . $nome_giocatore;
                    }
                    break; // Interrompiamo il ciclo dopo aver trovato Audero
                }
            }
        }
    } else {
        echo "Nessun giocatore trovato.<br>";
    }
}
*/
// Eseguiamo la funzione per cercare il giocatore "Audero"
//estraiVotiGiocatore($xpath, "Audero");
