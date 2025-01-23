<?php

declare (strict_types=1);
require_once __DIR__ . '/../src/tasks.php';

/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function allaTaskTester(): string {
// Kom ihåg att lägga till alla testfunktioner
    $retur = "<h1>Testar alla uppgiftsfunktioner</h1>";
    $retur .= test_HamtaEnUppgift();
    $retur .= test_HamtaUppgifterSida();
    $retur .= test_RaderaUppgift();
    $retur .= test_SparaUppgift();
    $retur .= test_UppdateraUppgifter();
    return $retur;
}

/**
 * Tester för funktionen hämta uppgifter för ett angivet sidnummer
 * @return string html-sträng med alla resultat för testerna
 */
function test_HamtaUppgifterSida(): string {
    $retur = "<h2>test_HamtaUppgifterSida</h2>";
    try {
        // Testa felaktigt sidnummer -1
        $svar = hamtaSida("-1");
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Hämta sida som inte finns (-1) misslyckades som förväntat</p>";
        } else {
            $retur .= "<p class=error'>Hämta sida som inte finns misslyckades, status: " . $svar->getStatus() . " returnerades</p>";
        }

        // Testa felaktigt sidnummer sju
        $svar = hamtaSida("sju");
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Hämta sida som inte finns (sju) misslyckades som förväntat</p>";
        } else {
            $retur .= "<p class=error'>Hämta sida som inte finns misslyckades, status: " . $svar->getStatus() . " returnerades</p>";
        }

        // Testa sida som finns (1)
        $svar = hamtaSida("1");
        $maxSidaNr = 0;
        if ($svar->getStatus() === 200) {
            $retur .= "<p class='ok'>Hämta sida 1 lyckades</p>";
            $maxSidaNr = $svar->getContent()->pages + 1;
        } else {
            $retur .= "<p class='error'>Hämta sida 1 misslyckades, status=" . $svar->getStatus() . " returnerades </p>";
            return $retur;
        }

        // Testa sida som inte finns
        $svar = hamtaSida((string)$maxSidaNr);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Hämta sida som inte finns ($maxSidaNr) misslyckades som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Hämta sida som inte ($maxSidaNr) finns misslyckades, status: " . $svar->getStatus() . " returnerades</p>";
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Test för funktionen hämta uppgifter mellan angivna datum
 * @return string html-sträng med alla resultat för testerna
 */
function test_HamtaAllaUppgifterDatum(): string {
    $retur = "<h2>test_HamtaAllaUppgifterDatum</h2>";
    try {
        // Testa felaktiga indatum
        $svar = hamtaDatum("1", "2");
        if ($svar->getStatus() === 400) {
            if (count($svar->getContent()->error) === 3) {
                $retur .= "<p class='ok'>Hämta uppgifter med felaktiga datum (1,2) misslyckades som förväntat</p>";
            } else {
                $retur .= "<p class='error'>Hämta uppgifter med felaktiga datum (1,2) returnerade "
                    . count($svar->getContent()->error) . " fel istället för de förväntade 3</p>";
            }
        } else {
            $retur .= "<p class='error'>Hämta uppgifter med felaktiga datum (1,2) misslyckades, status=" . $svar->getStatus() . " returnerades</p>";
        }

        $svar = hamtaDatum("1", "2025-01-01");
        if ($svar->getStatus() === 400) {
            if (count($svar->getContent()->error) === 2) {
                $retur .= "<p class='ok'>Hämta uppgifter med felaktiga datum (1,2025-01-01) misslyckades som förväntat</p>";
            } else {
                $retur .= "<p class='error'>Hämta uppgifter med felaktiga datum (1,2025-01-01) returnerade "
                    . count($svar->getContent()->error) . " fel istället för de förväntade 2</p>";
            }
        } else {
            $retur .= "<p class='error'>Hämta uppgifter med felaktiga datum (1,2025-01-01) misslyckades, status=" . $svar->getStatus() . " returnerades</p>";
        }

    $svar = hamtaDatum("2025-01-01","1");
        if ($svar->getStatus() === 400) {
            if (count($svar->getContent()->error) === 2) {
                $retur .= "<p class='ok'>Hämta uppgifter med felaktiga datum (2025-01-01,1) misslyckades som förväntat</p>";
            } else {
                $retur .= "<p class='error'>Hämta uppgifter med felaktiga datum (2025-01-01,1) returnerade "
                    . count($svar->getContent()->error) . " fel istället för de förväntade 2</p>";
            }
        } else {
            $retur .= "<p class='error'>Hämta uppgifter med felaktiga datum (2025-01-01,1) misslyckades, status=" . $svar->getStatus() . " returnerades</p>";
        }

        // Testa ogiltiga datum
    $svar = hamtaDatum("2025-01-01","2025-01-37");
        if ($svar->getStatus() === 400) {
            if (count($svar->getContent()->error) === 2) {
                $retur .= "<p class='ok'>Hämta uppgifter med felaktiga datum (2025-01-01,2025-01-37) misslyckades som förväntat</p>";
            } else {
                $retur .= "<p class='error'>Hämta uppgifter med felaktiga datum (2025-01-01,2025-01-37) returnerade "
                    . count($svar->getContent()->error) . " fel istället för de förväntade 2</p>";
            }
        } else {
            $retur .= "<p class='error'>Hämta uppgifter med felaktiga datum (2025-01-01,2025-01-37) misslyckades, status=" . $svar->getStatus() . " returnerades</p>";
        }

    $svar = hamtaDatum("2024-12-37","2025-01-27");
        if ($svar->getStatus() === 400) {
            if (count($svar->getContent()->error) === 2) {
                $retur .= "<p class='ok'>Hämta uppgifter med felaktiga datum (2024-12-37,2025-01-27) misslyckades som förväntat</p>";
            } else {
                $retur .= "<p class='error'>Hämta uppgifter med felaktiga datum (2024-12-37,2025-01-27) returnerade "
                    . count($svar->getContent()->error) . " fel istället för de förväntade 2</p>";
            }
        } else {
            $retur .= "<p class='error'>Hämta uppgifter med felaktiga datum (2024-12-37,2025-01-27) misslyckades, status=" . $svar->getStatus() . " returnerades</p>";
        }

        // Testa från större än till
    $svar = hamtaDatum("2025-12-27","2025-01-27");
        if ($svar->getStatus() === 400) {
            if (count($svar->getContent()->error) === 2) {
                $retur .= "<p class='ok'>Hämta uppgifter med felaktiga datum (2025-12-27,2025-01-27) misslyckades som förväntat</p>";
            } else {
                $retur .= "<p class='error'>Hämta uppgifter med felaktiga datum (2025-12-27,2025-01-27) returnerade "
                    . count($svar->getContent()->error) . " fel istället för de förväntade 2</p>";
            }
        } else {
            $retur .= "<p class='error'>Hämta uppgifter med felaktiga datum (2025-12-27,2025-01-27) misslyckades, status=" . $svar->getStatus() . " returnerades</p>";
        }

        // Testa korrekta datum
        $svar=hamtaDatum("2024-01-01","2025-01-01");
        if($svar->getStatus() === 200){
            $retur .="<p class='ok'>Hämta uppgifter med datum (2024-01-01,2025-01-01) lyckades</p>";
        } else {
            $retur .="<p class='error'>Hämta uppgifter med datum (2024-01-01,2025-01-01) misslyckades"
                . ", status=" . $svar->getStatus() . " returnerades</p>";
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Test av funktionen hämta enskild uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_HamtaEnUppgift(): string {
    $retur = "<h2>test_HamtaEnUppgift</h2>";

    try {
        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Test för funktionen spara uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_SparaUppgift(): string {
    $retur = "<h2>test_SparaUppgift</h2>";

    try {
        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Test för funktionen uppdatera befintlig uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_UppdateraUppgifter(): string {
    $retur = "<h2>test_UppdateraUppgifter</h2>";

    try {
        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

function test_KontrolleraIndata(): string {
    $retur = "<h2>test_KontrolleraIndata</h2>";

    try {
        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Test för funktionen radera uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_RaderaUppgift(): string {
    $retur = "<h2>test_RaderaUppgift</h2>";

    try {
        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}