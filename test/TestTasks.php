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

        $svar = hamtaDatum("2025-01-01", "1");
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
        $svar = hamtaDatum("2025-01-01", "2025-01-37");
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

        $svar = hamtaDatum("2024-12-37", "2025-01-27");
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
        $svar = hamtaDatum("2025-12-27", "2025-01-27");
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
        $svar = hamtaDatum("2024-01-01", "2025-01-01");
        if ($svar->getStatus() === 200) {
            $retur .= "<p class='ok'>Hämta uppgifter med datum (2024-01-01,2025-01-01) lyckades</p>";
        } else {
            $retur .= "<p class='error'>Hämta uppgifter med datum (2024-01-01,2025-01-01) misslyckades"
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

    $db = connectDb();
    $db->beginTransaction();
    try {
        // Misslyckas med att hämta felaktigt id (-1)
        $svar = hamtaEnskildUppgift("-1");
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Hämta post med felaktigt id (-1) misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Hämta post med felatkigt id (-1) misslyckades, status="
                . $svar->getStatus() . " returnerades</p>";
        }

        // Misslyckas med att hämta felaktigt id (sju)
        $svar = hamtaEnskildUppgift("sju");
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Hämta post med felaktigt id (sju) misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Hämta post med felatkigt id (sju) misslyckades, status="
                . $svar->getStatus() . " returnerades</p>";
        }

        // Lyckas med att hämta post som finns => skapa en ny post och hämta posten med det id't
        $post = ["date" => "2025-01-01", "time" => "01:30", "activityId" => 1, "description" => "Beskrivning"];
        $spara = sparaNyUppgift($post);
        if ($spara->getStatus() !== 200) {
            $retur .= "<p class='error'>Kunde inte skapa ny post för att testa läsning, avbryter!</p>";
            return $retur;
        }
        $nyttId = $spara->getContent()->id;
        $svar = hamtaEnskildUppgift((string)$nyttId);
        if ($svar->getStatus() === 200) {
            $retur .= "<p class='ok'>Hämta enskild post lyckades</p>";
        } else {
            $retur .= "<p class='error'>Hämta enskild post som nyss skapats misslyckades, status="
                . $svar->getStatus() . " returnerades</p>";
        }

        // Misslyckas med att hämta post som inte finns
        $svar = hamtaEnskildUppgift((string)($nyttId + 1));
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Hämta uppgift som inte finns misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Hämta uppgift som inte finns misslyckades, status="
                . $svar->getStatus() . " returnerades</p>";
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        $db->rollback();
    }

    return $retur;
}

/**
 * Test för funktionen spara uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_SparaUppgift(): string {
    $retur = "<h2>test_SparaUppgift</h2>";

    $db = connectDb();
    $db->beginTransaction();
    try {
        $post = ["date" => "2025-01-01", "time" => "01:30", "activityId" => 1, "description" => "Beskrivning"];
        // Lyckas med att spara komplett post
        $svar = sparaNyUppgift($post);
        if ($svar->getStatus() === 200) {
            $retur .= "<p class='ok'>Spara ny uppgift lyckades</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift misslyckades, 
                    status=" . $svar->getStatus() . " returnerades </p>";
        }

        // Misslyckas med att spara fel datum (1)
        $post['date'] = "1";
        $svar = sparaNyUppgift($post);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift med datum=1 misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift med datum=1 misslyckades, status="
                . $svar->getStatus() . " returnerades </p>";
        }

        // Misslyckas med att spara fel datum ("igår")
        $post['date'] = "igår";
        $svar = sparaNyUppgift($post);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift med datum='igår' misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift med datum='igår' misslyckades, status="
                . $svar->getStatus() . " returnerades </p>";
        }

        // Misslyckas med att spara ogiltigt (2023-13-37)
        $post['date'] = "2023-13-37";
        $svar = sparaNyUppgift($post);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift med datum=2023-13-37 misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift med datum=2023-13-37 misslyckades, status="
                . $svar->getStatus() . " returnerades </p>";
        }

        // Misslyckas med att spara datum i framtiden (i morgon)
        $post['date'] = date('Y-m-d', strtotime("tomorrow"));
        $svar = sparaNyUppgift($post);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift med datum={$post['date']} misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift med datum={$post['date']} misslyckades, status="
                . $svar->getStatus() . " returnerades </p>";
        }

        // Återställ datum
        $post['date'] = date('Y-m-d', strtotime("yesterday"));

        // Testa spara med tid=1
        $post['time'] = "1";
        $svar = sparaNyUppgift($post);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift med tid={$post['time']} misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift med tid={$post['time']} misslyckades, status="
                . $svar->getStatus() . " returnerades </p>";
        }

        // Testa spara med tid="en kvart"
        $post['time'] = "en kvart";
        $svar = sparaNyUppgift($post);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift med tid={$post['time']} misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift med tid={$post['time']} misslyckades, status="
                . $svar->getStatus() . " returnerades </p>";
        }

        // Testa spara med felaktigt formatterad tid (04:75)
        $post['time'] = "04:75";
        $svar = sparaNyUppgift($post);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift med tid={$post['time']} misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift med tid={$post['time']} misslyckades, status="
                . $svar->getStatus() . " returnerades </p>";
        }

        // Testa spara för lång tid (10:00)
        $post['time'] = "10:00";
        $svar = sparaNyUppgift($post);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift med tid={$post['time']} misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift med tid={$post['time']} misslyckades, status="
                . $svar->getStatus() . " returnerades </p>";
        }
        // Återställ tid
        $post['time'] = "02:00";

        // Testa spara med aktivitetId=0
        $post['activityId'] = "0";
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift med aktivitetsId={$post['activityId']} misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift med aktivitetsId={$post['activityId']} misslyckades, status="
                . $svar->getStatus() . " returnerades </p>";
        }

        // Testa spara med aktivitetId="tre"
        $post['activityId'] = "tre";
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift med aktivitetsId={$post['activityId']} misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift med aktivitetsId={$post['activityId']} misslyckades, status="
                . $svar->getStatus() . " returnerades </p>";
        }

        // Testa spara med aktivitetId=30000 (som troligen inte finns)
        $post['activityId'] = "30000";
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift med aktivitetsId={$post['activityId']} misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift med aktivitetsId={$post['activityId']} misslyckades, status="
                . $svar->getStatus() . " returnerades </p>";
        }

        // Återställ aktivitetId
        $post['activityId'] = "1";

        // Spara post utan beskrivning
        unset($post['description']);
        $svar = sparaNyUppgift($post);
        if ($svar->getStatus() === 200) {
            $retur .= "<p class='ok'>Spara post utan beskrivning lyckades";
        } else {
            $retur .= "<p class='error'>Spara post utan beskrivning misslyckades, status="
                . $svar->getStatus() . " returnerades </p>";
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        $db->rollback();
    }

    return $retur;
}

/**
 * Test för funktionen uppdatera befintlig uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_UppdateraUppgifter(): string {
    $retur = "<h2>test_UppdateraUppgifter</h2>";

    $db = connectDB();
    $db->beginTransaction();
    try {
        // Skapa ny post som används för uppdateringstesterna
        $post = ["date" => date('Y-m-d'), "time" => "01:30", "activityId" => 1, "description" => "Beskrivning"];
        $testPost = sparaNyUppgift($post);
        if ($testPost->getStatus() !== 200) {
            $retur .= "<p class='error'>Misslyckades med att skapa test-post i uppgifter, avbryter</p>";
            return $retur;
        }

        // Datum-tester som ska misslyckas:
        // "1", "igår", "2023-13-37", imorgon
        $testDates = ["1", "igår", "2023-13-37", date('Y-m-d', strtotime("tomorrow"))];
        foreach ($testDates as $date) {
            $post["date"] = $date;
            $svar = uppdateraUppgift((string)$testPost->getContent()->id, $post);
            if ($svar->getStatus() === 400) {
                $retur .= "<p class='ok'>Uppdatera uppgift med datum={$post['date']} misslyckades, som förväntat</p>";
            } else {
                $retur .= "<p class='error'>Uppdatera uppgift med datum={$post['date']} misslyckades, status="
                    . $svar->getStatus() . " returnerades </p>";
            }
        }
        // Återställ datum
        $post["date"] = date("Y-m-d");

        // Tidstester som ska misslyckas:
        // "1", "en kvart", "01:75", "10:30"
        $testTimes = ["1", "en kvart", "01:75", "10:30"];
        foreach ($testTimes as $time) {
            $post["time"] = $time;
            $svar = uppdateraUppgift((string)$testPost->getContent()->id, $post);
            if ($svar->getStatus() === 400) {
                $retur .= "<p class='ok'>Uppdatera uppgift med tid={$post['time']} misslyckades, som förväntat</p>";
            } else {
                $retur .= "<p class='error'>Uppdatera uppgift med tid={$post['time']} misslyckades, status="
                    . $svar->getStatus() . " returnerades </p>";
            }
        }
        // Återställ tid
        $post["time"] = "01:30";

        // Tester för aktivitetsid som ska misslyckas
        // "0", "tre", "123456"
        $testActivities = ["0", "tre", "123456"];
        foreach ($testActivities as $act) {
            $post["activityId"] = $act;
            $svar = uppdateraUppgift((string)$testPost->getContent()->id, $post);
            if ($svar->getStatus() === 400) {
                $retur .= "<p class='ok'>Uppdatera uppgift med aktivitet={$post['activityId']} misslyckades, som förväntat</p>";
            } else {
                $retur .= "<p class='error'>Uppdatera uppgift med aktivitet={$post['activityId']} misslyckades, status="
                    . $svar->getStatus() . " returnerades </p>";
            }
        }
        // Återställ aktivitetsid
        $post["activityId"] = "1";

        // Testa att uppdatera en uppgift som finns funkar
        unset($post['description']);
        $svar=uppdateraUppgift((string)$testPost->getContent()->id, $post);
        if($svar->getStatus() === 200){
            if($svar->getContent()->result){
                $retur .="<p class='ok'>Uppdatera en uppgift med borttagen beskrivning funkar</p>";
            } else{
                $retur .="<p class='error'>Uppdatera en uppgift med borttagen beskrivning misslyckades, result=false returnerades </p>";
            }
        } else {
            $retur .="<p class='error'>Uppdatera en uppgift med borttagen beskrivning misslyckades, status="
                . $svar->getStatus() . " returnerades </p>";
        }

        // Testa att uppdatera samma aktivitet igen ger result=false
        $svar=uppdateraUppgift((string)$testPost->getContent()->id, $post);
        if($svar->getStatus() === 200){
            if($svar->getContent()->result===false){
                $retur .="<p class='ok'>Uppdatera en uppgift med samma innehåll funkar</p>";
            } else{
                $retur .="<p class='error'>Uppdatera en uppgift med samma innehåll misslyckades, result=true returnerades </p>";
            }
        } else {
            $retur .="<p class='error'>Uppdatera en uppgift med samma innehåll misslyckades, status="
                . $svar->getStatus() . " returnerades </p>";
        }


        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        $db->rollback();
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