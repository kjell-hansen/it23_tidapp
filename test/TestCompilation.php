<?php

declare (strict_types=1);
require_once __DIR__ . '/../src/compilations.php';

/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function allaCompilationTester(): string {
// Kom ihåg att lägga till alla testfunktioner
    $retur = "<h1>Testar alla sammanställningsfunktioner</h1>";
    $retur .= test_HamtaSammanstallning();
    return $retur;
}

/**
 * Tester för funktionen hämta en sammmanställning av uppgifter mellan två datum
 * @return string html-sträng med alla resultat för testerna
 */
function test_HamtaSammanstallning(): string {
    $retur = "<h2>test_HamtaSammanstallning</h2>";
    try {
        // Misslyckas med tester av felaktiga datum:
        // "1", "2024-13-37", "igår"
        $testDates = ["1", "2024-13-37", "igår"];
        foreach ($testDates as $date) {
            $idag = date('Y-m-d');
            $svar = hamtaSammanstallning($idag, $date);
            if ($svar->getStatus() === 400) {
                $retur .= "<p class='ok'>Hämta sammanställning med från=$idag och till=$date misslyckades, som förväntat</p>";
            } else {
                $retur .= "<p class='error'>Hämta sammanställning med från=$idag och till=$date misslyckades, status= "
                    . $svar->getStatus() . " returnerades</p>";
            }
            $svar = hamtaSammanstallning($date, $idag);
            if ($svar->getStatus() === 400) {
                $retur .= "<p class='ok'>Hämta sammanställning med från=$date och till=$idag misslyckades, som förväntat</p>";
            } else {
                $retur .= "<p class='error'>Hämta sammanställning med från=$date och till=$idag misslyckades, status= "
                    . $svar->getStatus() . " returnerades</p>";
            }
        }

        // Misslyckas med från-datum större än till-datum
        $svar = hamtaSammanstallning('2025-10-10', '2025-01-01');
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Hämta sammanställning med från mindre än till misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Hämta sammanställning med från mindre än till misslyckades, status= "
                . $svar->getStatus() . " returnerades</p>";
        }

        // Lyckas med att hämta poster med korrekta datum
        $svar = hamtaSammanstallning('2025-01-01', '2025-10-10');
        if ($svar->getStatus() === 200) {
            $retur .= "<p class='ok'>Hämta poster med korrekta datum lyckades</p>";
        } else {
            $retur .= "<p class='error'>Hämta poster med korrekta datum misslyckades, status= "
                . $svar->getStatus() . " returnerades</p>";
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}