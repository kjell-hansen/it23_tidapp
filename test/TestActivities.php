<?php

declare (strict_types=1);
require_once '../src/activities.php';

/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function allaActivityTester(): string {
    // Kom ihåg att lägga till alla funktioner i filen!
    $retur = "";
    $retur .= test_HamtaAllaAktiviteter();
    $retur .= test_HamtaEnAktivitet();
    $retur .= test_SparaNyAktivitet();
    $retur .= test_UppdateraAktivitet();
    $retur .= test_RaderaAktivitet();

    return $retur;
}

/**
 * Tester för funktionen hämta alla aktiviteter
 * @return string html-sträng med alla resultat för testerna
 */
function test_HamtaAllaAktiviteter(): string {
    $retur = "<h2>test_HamtaAllaAktiviteter</h2>";
    try {
        $svar = hamtaAllaAktiviteter();
        if ($svar->getStatus() == 200) {
            $retur .= "<p class='ok'>Testet lyckades. " . count($svar->getContent()) . " poster returnerades</p>";
        } else {
            $retur .= "<p class='error'>Hämta alla aktiviteter misslyckades<br>" . $svar->getStatus() . " returnerades</p>";
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Tester för funktionen hämta enskild aktivitet
 * @return string html-sträng med alla resultat för testerna
 */
function test_HamtaEnAktivitet(): string {
    $retur = "<h2>test_HamtaEnAktivitet</h2>";
    try {
        // Testa felaktiga inparametrar (-1 och sju)
        $svar = hamtaEnskildAktivitet("-1");
        if ($svar->getStatus() == 200) {
            $retur .= "<p class='error'>Misslyckat test. Hämta med -1 gav status 200</p>";
        } else {
            $retur .= "<p class='ok'>Misslyckades hämta post med id=-1 som förväntat</p>";
        }
        $svar = hamtaEnskildAktivitet("sju");
        if ($svar->getStatus() == 200) {
            $retur .= "<p class='error'>Misslyckat test. Hämta med 'sju' gav status 200</p>";
        } else {
            $retur .= "<p class='ok'>Misslyckades hämta post med id='sju' som förväntat</p>";
        }

        // Testa id som saknas
        $alla = hamtaAllaAktiviteter();
        $poster = $alla->getContent();
        $sista = $poster[count($poster) - 1];
        $svar = hamtaEnskildAktivitet((string)($sista->id + 1));
        if ($svar->getStatus() == 200) {
            $retur .= "<p class='error'>Misslyckat test. Hämta post som inte finns (" . $sista->id + 1 . ") gav status 200</p>";
        } else {
            $retur .= "<p class='ok'>Misslyckades hämta post med id=" . $sista->id + 1 . " som förväntat</p>";
        }

        // Testa id som finns
        $svar = hamtaEnskildAktivitet((string)($sista->id));
        if ($svar->getStatus() == 200) {
            $retur .= "<p class='ok'>Lyckades hämta post med id=" . $sista->id . " som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Misslyckades hämta post som finns (" . $sista->id . ")</p>";
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Tester för funktionen spara aktivitet
 * @return string html-sträng med alla resultat för testerna
 */
function test_SparaNyAktivitet(): string {
    $retur = "<h2>test_SparaNyAktivitet</h2>";

    $db = connectDb();
    try {
        // Skapa transaktion!
        $db->beginTransaction();

        // Testa spara tom aktivitet
        $svar = sparaNyAktivitet("");
        if ($svar->getStatus() == 400) {
            $retur .= "<p class='ok'>Testa spara tom aktivitet misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Testa spara tom aktivitet misslyckades, status=" . $svar->getStatus() . " returnerades</p>";
        }

        // Testa spara ny aktivitet och lyckas
        $varde = "test" . strtotime("now");
        $svar = sparaNyAktivitet($varde);
        if ($svar->getStatus() == 200) {
            $retur .= "<p class='ok'>Spara ny aktivitet lyckades</p>";
        } else {
            $retur .= "<p class='error'>Spara ny aktivitet misslyckades, status=" . $svar->getStatus() . " returnerades</p>";
        }

        // Testa spara samma aktivitet och misslyckas
        $svar = sparaNyAktivitet($varde);
        if ($svar->getStatus() == 400) {
            $retur .= "<p class='ok'>Testa spara samma aktivitet misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Testa spara samma aktivitet misslyckades, status=" . $svar->getStatus() . " returnerades</p>";
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        $db->rollback();
    }

    return $retur;
}

/**
 * Tester för uppdatera aktivitet
 * @return string html-sträng med alla resultat för testerna
 */
function test_UppdateraAktivitet(): string {
    $retur = "<h2>test_UppdateraAktivitet</h2>";

    // Skapa transaktion
    $db=connectDb();
    $db->beginTransaction();

    try {
        // Misslyckas med att uppdatera post med id=-1
        $svar=uppdateraAktivitet("-1", "Någon aktivitet");
        if ($svar->getStatus() == 400) {
            $retur .="<p class='ok'>Uppdatera aktivitet med id=-1 misslyckades som förväntat</p>";
        } else {
            $retur .="<p class='error>Uppdatera aktivitet med id=-1 misslyckades, status=" . $svar->getStatus() . " returnerades</p>";
        }

        // Misslyckas med att uppdatera post med id=åtta
        $svar=uppdateraAktivitet("åtta", "Någon aktivitet");
        if ($svar->getStatus() == 400) {
            $retur .="<p class='ok'>Uppdatera aktivitet med id=åtta misslyckades som förväntat</p>";
        } else {
            $retur .="<p class='error>Uppdatera aktivitet med id=åtta misslyckades, status=" . $svar->getStatus() . " returnerades</p>";
        }

        // Misslyckas med att uppdatera post med tom aktivitet
        $svar=uppdateraAktivitet("5", "");
        if ($svar->getStatus() == 400) {
            $retur .="<p class='ok'>Uppdatera aktivitet med tom aktivitet misslyckades som förväntat</p>";
        } else {
            $retur .="<p class='error>Uppdatera aktivitet med tom aktivitet misslyckades, status=" . $svar->getStatus() . " returnerades</p>";
        }

        // Lyckas med att uppdatera befintlig post
        $aktivitet="test" . strtotime("now");
        $nyPost=sparaNyAktivitet($aktivitet);
        $nyttId=$nyPost->getContent()->id;
        $svar=uppdateraAktivitet($nyttId, $aktivitet ."1");
        if($svar->getStatus() == 200){
            if($svar->getContent()->result) {
                $retur .= "<p class='ok'>Uppdatera aktivitet lyckades</p>";
            } else {
                $retur .="<p class='error'>Uppdatera aktivitet misslyckades, result=false</p>";
            }
        } else {
            $retur .="<p class='error'>Uppdatera aktivitet misslyckades, status=" . $svar->getStatus() . " returnerades</p>";
        }

        // Misslyckas med att uppdatera post där aktiviteten inte ändrats
       $svar=uppdateraAktivitet($nyttId, $aktivitet ."1");
        if($svar->getStatus() == 200){
            if($svar->getContent()->result===false) {
                $retur .= "<p class='ok'>Uppdatera aktivitet misslyckades som förväntat</p>";
            } else {
                $retur .="<p class='error'>Uppdatera aktivitet misslyckades, result=true</p>";
            }
        } else {
            $retur .="<p class='error'>Uppdatera aktivitet misslyckades, status=" . $svar->getStatus() . " returnerades</p>";
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        $db->rollback();
    }

    return $retur;
}

/**
 * Tester för funktionen radera aktivitet
 * @return string html-sträng med alla resultat för testerna
 */
function test_RaderaAktivitet(): string {
    $retur = "<h2>test_RaderaAktivitet</h2>";
    try {
        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}