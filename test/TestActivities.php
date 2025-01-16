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

    try {
        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Tester för uppdatera aktivitet
 * @return string html-sträng med alla resultat för testerna
 */
function test_UppdateraAktivitet(): string {
    $retur = "<h2>test_UppdateraAktivitet</h2>";

    try {
        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
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