<?php

declare (strict_types=1);
require_once __DIR__ . '/activities.php';

const antalPosterPerSida = 5;

/**
 * Hämtar en lista med alla uppgifter och tillhörande aktiviteter
 * Beroende på indata returneras en sida eller ett datumintervall
 * @param Route $route indata med information om vad som ska hämtas
 * @return Response
 */
function tasklists(Route $route): Response {
    try {
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::GET) {
            return hamtaSida($route->getParams()[0]);
        }
        if (count($route->getParams()) === 2 && $route->getMethod() === RequestMethod::GET) {
            return hamtaDatum($route->getParams()[0], $route->getParams()[1]);
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }

    return new Response("Okänt anrop", 400);
}

/**
 * Läs av rutt-information och anropa funktion baserat på angiven rutt
 * @param Route $route Rutt-information
 * @param array $postData Indata för behandling i angiven rutt
 * @return Response
 */
function tasks(Route $route, array $postData): Response {
    try {
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::GET) {
            return hamtaEnskildUppgift($route->getParams()[0]);
        }
        if (count($route->getParams()) === 0 && $route->getMethod() === RequestMethod::POST) {
            return sparaNyUppgift($postData);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::PUT) {
            return uppdateraUppgift($route->getParams()[0], $postData);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::DELETE) {
            return raderaUppgift($route->getParams()[0]);
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }
}

/**
 * Hämtar alla uppgifter för en angiven sida
 * @param string $sida
 * @return Response
 */
function hamtaSida(string $sida): Response {
    // Kontrollera indata
    $kontrolleradSida = filter_var($sida, FILTER_VALIDATE_INT);
    if ($kontrolleradSida === false || $kontrolleradSida < 1) {
        $tasks = new stdClass();
        $tasks->error = ['Bad request', 'Felaktigt sidnummer'];
        return new Response($tasks, 400);
    }

    // Koppla databas
    $db = connectDb();

    // Räkna antal poster
    $rakneQuery = $db->query("SELECT COUNT(*) FROM uppgifter");
    $antalPoster = $rakneQuery->fetchColumn();

    $antalSidor = ceil($antalPoster / antalPosterPerSida);
    if ($kontrolleradSida > $antalSidor) {
        $tasks = new stdClass();
        $tasks->error = ['Bad request', "Felaktigt sidnummer. Det finns bara $antalSidor sidor."];
        return new Response($tasks, 400);
    }

    // Returnera alla poster för önskad sida
    $forstaPost = ($kontrolleradSida - 1) * antalPosterPerSida;
    $uppgiftQuery = $db->query("SELECT uppgifter.id, aktivitetsid, datum, tid, beskrivning, aktivitet 
                FROM uppgifter INNER JOIN aktiviteter ON uppgifter.aktivitetsid = aktiviteter.id 
                ORDER BY datum ASC LIMIT $forstaPost," . antalPosterPerSida);
    $allaPoster = $uppgiftQuery->fetchAll();

    $tasks = [];
    foreach ($allaPoster as $rad) {
        $post = new stdClass();
        $post->id = $rad['id'];
        $post->activityId = $rad['aktivitetsid'];
        $post->date = $rad['datum'];
        $post->time = substr($rad['tid'], 0, -3);
        $post->activity = $rad['aktivitet'];
        $post->description = $rad['beskrivning'] ?? '';
        $tasks[] = $post;
    }

    $retur = new stdClass();
    $retur->pages = $antalSidor;
    $retur->tasks = $tasks;

    return new Response($retur, 200);
}


/**
 * Hämtar alla poster mellan angivna datum
 * @param string $from
 * @param string $tom
 * @return Response
 */
function hamtaDatum(string $from, string $tom): Response {
    // Kontrollera indata
    $kontrolleradFrom = DateTimeImmutable::createFromFormat('Y-m-d', $from);
    $kontrolleradTom = DateTimeImmutable::createFromFormat('Y-m-d', $tom);

    // Skicka fel om from eller tom är false, eller felaktiga
    $datumFel = [];
    if ($kontrolleradFrom === false) {
        $datumFel[] = "Felaktigt angivet från-datum";
    }
    if ($kontrolleradTom === false) {
        $datumFel[] = "Felaktigt angivet till-datum";
    }
    if ($kontrolleradFrom && $kontrolleradFrom->format("Y-m-d") !== $from) {
        $datumFel[] = "$from är inget giltigt datum";
    }
    if ($kontrolleradTom && $kontrolleradTom->format("Y-m-d") !== $tom) {
        $datumFel[] = "$tom är inget giltigt datum";
    }
    // Skicka fel om from>tom
    if ($kontrolleradFrom && $kontrolleradTom &&
        $kontrolleradFrom->format("Y-m-d") > $kontrolleradTom->format("Y-m-d")) {
        $datumFel[] = "Fråndatum ($from) ska vara mindre än tilldatum ($tom)";
    }

    if (count($datumFel) > 0) {
        $retur = new stdClass();
        array_unshift($datumFel, "Bad request");
        $retur->error = $datumFel;
        return new Response($retur, 400);
    }

    // Koppla mot databas
    $db = connectDb();

    // Hämta poster
    $stmt = $db->prepare("SELECT uppgifter.id, aktivitetsid, datum, tid, beskrivning, aktivitet 
    FROM uppgifter INNER JOIN aktiviteter ON uppgifter.aktivitetsid = aktiviteter.id
    WHERE datum BETWEEN :from AND :tom
    ORDER BY datum, uppgifter.id ASC");

    $stmt->execute(['from' => $kontrolleradFrom->format('Y-m-d'), 'tom' => $kontrolleradTom->format('Y-m-d')]);
    $allaRader = $stmt->fetchAll();

    $tasks = [];
    foreach ($allaRader as $post) {
        $task = new stdClass();
        $task->id = $post['id'];
        $task->activityId = $post['aktivitetsid'];
        $task->date = $post['datum'];
        $task->time = substr($post['tid'], 0, -3);
        $task->activity = $post['aktivitet'];
        $task->description = $post['beskrivning'] ?? '';
        $tasks[] = $task;
    }

    // Returnera svar
    return new Response($tasks, 200);
}

/**
 * Hämtar en enskild uppgiftspost
 * @param string $id Id för post som ska hämtas
 * @return Response
 */
function hamtaEnskildUppgift(string $id): Response {
    // Kontrollera indata
    $kontrolleradId = filter_var($id, FILTER_VALIDATE_INT);
    if ($kontrolleradId === false || $kontrolleradId < 1) {
        $retur = new stdClass();
        $retur->error = ["Bad request", "Felaktigt angivet id"];
        return new Response($retur, 400);
    }

    // Koppla databas
    $db = connectDb();

    // Skicka fråga
    $stmt = $db->prepare("SELECT uppgifter.id, aktivitetsid, datum, tid, beskrivning, aktivitet
    FROM uppgifter INNER JOIN aktiviteter ON uppgifter.aktivitetsid = aktiviteter.id 
    WHERE uppgifter.id=:id");
    $stmt->execute(['id' => $kontrolleradId]);

    // Ta emot svar
    if ($stmt->rowCount() === 0) {
        $retur = new stdClass();
        $retur->error = ["Bad request", "Post saknas"];
        return new Response($retur, 400);
    } else {
        $post = $stmt->fetch();
        $retur = new stdClass();
        $retur->id = $post['id'];
        $retur->activityId = $post['aktivitetsid'];
        $retur->date = $post['datum'];
        $retur->time = substr($post['tid'], 0, -3);
        $retur->activity = $post['aktivitet'];
        $retur->description = $post['beskrivning'] ?? '';
    }

    // Returnera svar
    return new Response($retur, 200);

}

/**
 * Sparar en ny uppgiftspost
 * @param array $postData indata för uppgiften
 * @return Response
 */
function sparaNyUppgift(array $postData): Response {
    // Kontrollera indata
    $koll = kontrolleraPostdata($postData);
    if ($koll) {
        $retur = new stdClass();
        array_unshift($koll, "Bad request");
        $retur->error = $koll;
        return new Response($retur, 400);
    }

    try {
        // Koppla databas
        $db = connectDb();

        // Spara post
        $stmt = $db->prepare("INSERT INTO uppgifter (datum, tid, aktivitetsid, beskrivning) 
                    VALUES (:datum, :tid, :aktivitetsid, :beskrivning)");
        if (isset($postData['description'])) {
            $stmt->execute(["datum" => $postData['date'],
                "tid" => $postData['time'],
                "aktivitetsid" => $postData['activityId'],
                "beskrivning" => filter_var($postData['description'], FILTER_SANITIZE_SPECIAL_CHARS)
            ]);
        } else {
            $stmt->execute(["datum" => $postData['date'],
                "tid" => $postData['time'],
                "aktivitetsid" => $postData['activityId'],
                "beskrivning" => null
            ]);
        }

        // Returnera svar
        if ($stmt->rowCount() === 1) {
            $id = $db->lastInsertId();
            $retur = new stdClass();
            $retur->id = $id;
            $retur->message = ["Spara lyckades", "1 post lades till"];
            return new Response($retur, 200);
        } else {
            $retur = new stdClass();
            $retur->error = ["Spara misslyckades"];
            return new Response($retur, 400);
        }
    } catch (Exception $e) {
        $retur = new stdClass();
        $retur->error = ["Spara misslyckades", $e->getMessage()];
        return new Response($retur, 400);
    }
}

/**
 * Uppdaterar en angiven uppgiftspost med ny information
 * @param string $id id för posten som ska uppdateras
 * @param array $postData ny data att sparas
 * @return Response
 */
function uppdateraUppgift(string $id, array $postData): Response {
    // Kontrollera indata
    $kontrolleradId = filter_var($id, FILTER_VALIDATE_INT);
    if ($kontrolleradId === false || $kontrolleradId < 1) {
        $retur = new stdClass();
        $retur->error = ["Bad request", "Felaktigt angivet id"];
        return new Response($retur, 400);
    }

    $koll = kontrolleraPostdata($postData);
    if ($koll) {
        $retur = new stdClass();
        array_unshift($koll, "Bad request");
        $retur->error = $koll;
        return new Response($retur, 400);
    }

    // Koppla databas
    $db = connectDb();

    try {
        // Skicka fråga
        $stmt = $db->prepare("UPDATE uppgifter SET 
                     datum=:datum, 
                     tid=:tid,
                     aktivitetsid=:aktivitetsid,
                     beskrivning=:beskrivning
                     WHERE id=:id");

        if (isset($postData['description'])) {
            $stmt->execute(["datum" => $postData['date'],
                "tid" => $postData['time'],
                "aktivitetsid" => $postData['activityId'],
                "beskrivning" => filter_var($postData['description'], FILTER_SANITIZE_SPECIAL_CHARS),
                "id" => $id
            ]);
        } else {
            $stmt->execute(["datum" => $postData['date'],
                "tid" => $postData['time'],
                "aktivitetsid" => $postData['activityId'],
                "beskrivning" => null,
                "id" => $id
            ]);
        }

        // Kontrollera svar
        $retur = new stdClass();
        if ($stmt->rowCount() === 1) {
            $retur->result = true;
            $retur->message = ["Uppdatera lyckades", "1 post uppdaterades"];
        } else {
            $retur->result = false;
            $retur->message = ["Uppdatera misslyckades", "Ingen post uppdaterades"];
        }

        return new Response($retur, 200);
    } catch (Exception $e) {
        $retur = new stdClass();
        $retur->error = ["Något gick fel", $e->getMessage()];
        return new Response($retur, 400);
    }
}

/**
 * Raderar en uppgiftspost
 * @param string $id Id för posten som ska raderas
 * @return Response
 */
function raderaUppgift(string $id): Response {
    // Kontrollera indata
    $kontrolleradId = filter_var($id, FILTER_VALIDATE_INT);
    if ($kontrolleradId === false || $kontrolleradId < 1) {
        $retur = new stdClass();
        $retur->error = ["Bad request", "Felaktigt angivet id"];
        return new Response($retur, 400);
    }

    // Koppla databas
    $db = connectDb();

    // Skicka fråga
    $stmt = $db->prepare("DELETE FROM uppgifter WHERE id=:id");
    $stmt->execute(["id" => $kontrolleradId]);

    // Returnera svar
    $retur = new stdClass();
    if ($stmt->rowCount() === 1) {
        $retur->result = true;
        $retur->message = ["Radera lyckades", "1 post raderades"];
    } else {
        $retur->result = false;
        $retur->message = ["Radera misslyckades", "Ingen post raderades"];
    }
    return new Response($retur, 200);
}

function kontrolleraPostdata(array $postData): array {
    $returArray = [];

    // Kontrollera datum
    if (!isset($postData['date'])) {
        $returArray[] = "Datum saknas";
    } else {
        // Kontrollera att det är ett datum
        $datum = DatetimeImmutable::createFromFormat('Y-m-d', $postData['date']);
        if ($datum === false) {
            $returArray[] = "Felaktigt angivet datum";
        } else {
            // Kontrollera att datum är giltigt
            if ($datum->format('Y-m-d') !== $postData['date']) {
                $returArray[] = "Ogiltigt angivet datum";
            } else {
                // Kontrollera att datum inte är i framtiden
                if ($datum->format('Y-m-d') > date("Y-m-d")) {
                    $returArray[] = "Datum får inte vara i framtiden";
                }
            }
        }

    }

    // Kontrollera tid
    if (!isset($postData['time'])) {
        $returArray[] = "Tid saknas";
    } else {
        // Kontrollera att tiden är i rätt format
        $tid = DatetimeImmutable::createFromFormat('H:i', $postData['time']);
        if ($tid === false) {
            $returArray[] = "Felaktigt angiven tid";
        } else {
            // Kontrollera att tiden är giltig
            if ($tid->format('H:i') !== $postData['time']) {
                $returArray[] = "Ogiltigt angiven tid";
            } else {
                // Kontrollera att inte tiden är för stor (>8h)
                if ($tid->format('H:i') > "08:00") {
                    $returArray[] = "Tiden får vara högst 8 timmar";
                }
            }
        }
    }


    // Kontrollera aktivitetid
    if (!isset($postData['activityId'])) {
        $returArray[] = "AktivitetsId saknas";
    } else {
        $kontrolleradId = filter_var($postData['activityId'], FILTER_VALIDATE_INT);
        if ($kontrolleradId === false || $kontrolleradId < 1) {
            $returArray[] = "Ogiltigt angivet aktivitetsId";
        }
    }

    return $returArray;
}