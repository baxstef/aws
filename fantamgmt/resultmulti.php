<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "simple_html_dom.php";

$lasquadra = $_POST["lasquadra"];

if ($lasquadra == "La Compagnia dell Anello") {
    $lasquadra = "La Compagnia dellâ¬ÂÂAnello";
}

// Load the HTML file
$dom2 = new DOMDocument();
libxml_use_internal_errors(true); // Suppress warnings for malformed HTML

$html2 = file_get_contents('classifica.html');

// Check if the HTML was loaded correctly
if ($html2 === false) {
    echo "Failed to load HTML file.";
    exit;
}

$dom2->loadHTML($html2);

// Create an XPath object
$xpath = new DOMXPath($dom2);

// Query the div elements
$divs = $xpath->query("//div[@data-wrap-uid]");

// Check if any divs were found
if ($divs === false) {
    echo "XPath query failed.";
} else {
    //    echo "Found " . $divs->length . " divs with data-wrap-uid.";
}

//$divs = $xpath->query("//div[@class='ranking']");


$div_content = '';
$calsnome = array();
$calsindex = array();
$calsrank = array();

foreach ($divs as $div) {
    // Get the table from the div
    $tables = $div->getElementsByTagName('table');

    // Check if any tables were found
    if ($tables->length > 0) {
        $table = $tables->item(0); // Access the first table

        $rows = $table->getElementsByTagName('tr');

        foreach ($rows as $row) {
            $cols = $row->getElementsByTagName('td');

            foreach ($cols as $col) {
                // Extract team name
                if ($col->getAttribute('data-key') == 'teamName') {
                    $name = trim($col->textContent);
                    $name = substr($name, 0, -2); // Assuming you want to remove the last 2 characters

                    // Clean the name
                    $name = pulisciStringa($name);

                    // If the name matches specific conditions, handle them
                    if ($name == "La Compagnia dellâ¬ÂÂAnello") {
                        // Your logic here
                    }

                    // Add to name array
                    $calsnome[] = $name;
                }

                // Extract index
                if ($col->getAttribute('data-key') == 'index') {
                    $index = trim($col->textContent);
                    $calsindex[] = $index;
                    // echo " i " . $index;
                }

                // Extract rank-fp
                if ($col->getAttribute('data-key') == 'rank-fp') {
                    $rankfp = trim($col->textContent);
                    $calsrank[] = $rankfp;
                    //  echo $rankfp;
                    // Check if this name matches lasquadra
                    if ($name == $lasquadra) {
                        $rankErmaterasso = $rankfp; // Store rank for "Ermaterasso"
                    }
                }
            }
        }
    } else {
        echo "No tables found in this div.<br>";
    }
}

//var_dump($calsnome);
//var_dump($calsindex);
//var_dump($calsrank);
$icountsquadre = count($calsnome);
// echo "</br>";
//echo $icountsquadre;
//echo "</br>";

$squadresotto = array();
for ($x = 0; $x < $icountsquadre; $x++) {
    if ($rankErmaterasso < $calsrank[$x]) {
        // echo "sopra " . $calsnome[$x];
        $squadresopra[] = $calsnome[$x];
    } else {
        $squadresotto[] = $calsnome[$x];
        // echo " sotto " . $calsnome[$x];
    }
}
$icountsquadresopra = count($squadresopra);
$icountsquadresotto = count($squadresotto);


// echo "----</br>";
// echo "</br>";
for ($x = 0; $x < $icountsquadresopra; $x++) {
    // echo "</br>";
    //          echo $squadresopra[$x];
    //     echo "</br>";
}
//   echo "----</br>";

////////////////
//duplicati
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


$dom = new DOMDocument();
$html = file_get_contents('ultima.html');

$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
$divs = $xpath->query("//div[@data-wrap-uid]");
$div_content = '';

$arraytitolari = array();
$arrayriserve = array();
//titolari
foreach ($divs as $div) {
    $div_content = $dom->saveHTML($div);
    //echo $div_content;

    $table = $div->getElementsByTagName('table')->item(0);
    $rows = $table->getElementsByTagName('tr');

    $team_type = '';
    if ($table->getAttribute('id') == 'formationTable') {
        $team_type = 'titolari';
    } elseif ($table->getAttribute('id') == 'releaseTable') {
        $team_type = 'riserve';
    }

    foreach ($rows as $row) {
        $cols = $row->getElementsByTagName('td');

        $name = '';
        $is_titolare = false;

        foreach ($cols as $col) {
            if ($col->getAttribute('data-key') == 'name') {
                $name = trim($col->textContent);
                $role_span = $col->getElementsByTagName('span')->item(0);
                if ($role_span) {
                    $roles = $role_span->getElementsByTagName('span');
                    foreach ($roles as $role) {
                        if ($role->getAttribute('class') == 'role role-p') {
                            $is_titolare = true;
                            break;
                        }
                    }
                }
            }
        }

        if (!empty($name)) {
            $team[] = array(
                'name' => $name,
                'type' => $team_type,
                'is_titolare' => $is_titolare
            );
        }
    }
    $arraytitolari[] = $team;
    $team = array();
}
//riserve
foreach ($divs as $div) {
    $div_content = $dom->saveHTML($div);
    //echo $div_content;

    $table = $div->getElementsByTagName('table')->item(1);
    $rows = $table->getElementsByTagName('tr');

    $team_type = '';
    if ($table->getAttribute('id') == 'formationTable') {
        $team_type = 'titolari';
    } elseif ($table->getAttribute('id') == 'releaseTable') {
        $team_type = 'riserve';
    }


    foreach ($rows as $row) {
        $cols = $row->getElementsByTagName('td');

        $name = '';
        $is_titolare = false;

        foreach ($cols as $col) {

            if ($col->getAttribute('data-key') == 'name') {
                $name = trim($col->textContent);
                $role_span = $col->getElementsByTagName('span')->item(0);
                if ($role_span) {
                    $roles = $role_span->getElementsByTagName('span');
                    foreach ($roles as $role) {
                        if ($role->getAttribute('class') == 'role role-p') {
                            $is_titolare = false;
                            break;
                        }
                    }
                }
            }
        }

        if (!empty($name)) {
            $team[] = array(
                'name' => $name,
                'type' => $team_type,
                'non_titolare' => $is_titolare
            );
        }
    }
    $arrayriserve[] = $team;
    $team = array();
}

//   $array1[]=$teamriserve;

//print_r($team);
//echo "</br>";
//print_r($arraytitolari);  
//echo "</br>";
//print_r($arrayriserve);






//nome squadre
$valuesnomesquadra = [];
$nomesquadre = $dom->getElementsByTagName('div');

foreach ($nomesquadre as $nomesquadra) {
    if ($nomesquadra->getAttribute('class') == 'media-body') {

        $nomesquadra =  $nomesquadra->textContent;
        $div_content =  $div->nodeValue;
        // echo "</br>";
        // echo $nomesquadra;
        // echo $div_content;
        // echo "</br>";

        $valuesnomesquadra[] = $nomesquadra;
    }
}
//print_r($valuesnomesquadra);
$nome = $_POST["nome"];

//echo $nome."</br>";
$squadra = substr($nome, -3);
$nome = substr($nome, 0, -4);
/*echo "----</br>";
echo $nome;
echo "----</br>";
echo "hgfht ";
echo $squadra." hgfht";
*/

$ind1 = 1;
$ind2 = 1;

$arraysquadrecontitolari = array();

// echo "squadre che hanno " . $nome . " titolare=</br>";
foreach ($arraytitolari as $arraytitolari1) {

    foreach ($arraytitolari1 as $arraytitolari2) {
        //echo  $arraytitolari2[0];
        //echo  $arraytitolari2["name"];
        $nomegiocatore = $arraytitolari2["name"];
        //$nomegiocatore=substr($nomegiocatore,3);


        $str1 = substr($nomegiocatore, 2);

        $str1 = trim($str1);
        //echo "-".$str1."-</br>";
        if ($str1 == $nome) {
            // echo $valuesnomesquadra[$ind1];
            //    echo " .</br>";
            $nomesquadrai = $valuesnomesquadra[$ind1];
            // echo $nomesquadrai;
            // echo " ?</br>";

            $squadrafanta = substr($nomesquadrai, 0, -350);
            // echo $squadrafanta . "-";
            // echo " .</br>";
            $nameb = pulisciStringa($nomesquadrai);
            $arraysquadrecontitolari[] = $nameb;
        }
    }


    // foreach($calsnome as $calsnomes){




    //             if (in_array($nameb, $squadresopra, true)) {
    //                     //if($squadrafanta==$calsnomes){
    //                         echo "msdmskdk ";
    //                     echo "</br>";
    //                 echo $calsnomes[$ind1];
    //                     echo "</br>";
    //                 echo $calsrank[$ind1];
    //                     echo "</br>";
    //                     }
    //             if (in_array($nameb, $squadresotto, true)) {
    //                     //if($squadrafanta==$calsnomes){
    //                         echo "msdmskdk ";
    //                     echo "</br>";
    //                 echo $calsnomes[$ind1];
    //                     echo "</br>";
    //                 echo $calsrank[$ind1];
    //                     echo "</br>";
    //                     }

    //                 }

    //  echo "</br>";

    $ind1++;
}
$arraysquadreconriserve = array();
//echo "squadre che hanno ".$nome." in panchina=</br>";
foreach ($arrayriserve as $arrayriserve1) {
    foreach ($arrayriserve1 as $arrayriserve2) {

        $nomegiocatore = $arrayriserve2["name"];

        $str2 = substr($nomegiocatore, 2);

        $str2 = trim($str2);
        //echo "-".$str1."-</br>";
        if ($str2 == $nome) {
            //      echo $valuesnomesquadra[$ind2];
            //echo "</br>";
            $nameb = pulisciStringa($valuesnomesquadra[$ind2]);
            $arraysquadreconriserve[] = $nameb;
        }
    }
    $ind2++;
}




function pulisciStringa($stringa)
{
    $stringa_pulita = rtrim($stringa);
    return $stringa_pulita;
}


function checksoprasotto($squadra, $squadresopra, $squadresotto, $calsnome, $calsrank, $calsindex, $lasquadra)
{
    $squadra = substr($squadra, 0, -6);
    $squadra = pulisciStringa($squadra);
    $squadra = trim($squadra);
    $calsnome = array_map('trim', $calsnome);
    $key = array_search($squadra, $calsnome);
    $keylasquadra = array_search($lasquadra, $calsnome);

    if ($key !== false && $keylasquadra !== false) {
        $puntis = (float)$calsrank[$key];
        $puntilasquadra = (float)$calsrank[$keylasquadra];

        echo $squadra;

        if (in_array($squadra, $squadresopra, true)) {
            echo " + ";
            $sopra = $puntis - $puntilasquadra;
            echo $sopra;
        }
        if (in_array($squadra, $squadresotto, true)) {
            echo " - ";
            $sotto = $puntilasquadra - $puntis;
            echo $sotto;
        }
    } else {
        echo $squadra;
    }
}

//if (count($playerlist) === 0) {
if (count($arraysquadrecontitolari) != 0) {
    echo "squadre che hanno " . $nome . " titolare=</br>";
    foreach ($arraysquadrecontitolari as $arraysquadrecontitolaris) {

        //  echo $arraysquadrecontitolaris;
        echo checksoprasotto($arraysquadrecontitolaris, $squadresopra, $squadresotto, $calsnome, $calsrank, $calsindex, $lasquadra);

        echo "</br> ";
    }
}
echo "</br>";

if (count($arraysquadreconriserve) != 0) {
    echo "squadre che hanno " . $nome . " in panchina=</br>";

    foreach ($arraysquadreconriserve as $arraysquadreconriserves) {
        //echo $arraysquadreconriserves."</br>";
        echo checksoprasotto($arraysquadreconriserves, $squadresopra, $squadresotto, $calsnome, $calsrank, $calsindex, $lasquadra);
        echo "</br> ";
    }
}
