<?php

declare (strict_types=1);

/**
 * Läs av rutt-information och anropa funktion baserat på angiven rutt
 * @param Route $route Rutt-information
 * @param array $postData Indata för behandling i angiven rutt
 * @return Response
 */
function compilations(Route $route): Response {
    try {
        if (count($route->getParams()) === 2 && $route->getMethod() === RequestMethod::GET) {
            return hamtaSammanstallning($route->getParams()[0], $route->getParams()[1]);
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }

    return new Response("Okänt anrop", 400);
}

/**
 * Hämtar en sammanställning av uppgiftsposter i ett angivet datumintervall
 * @param string $from
 * @param string $tom
 * @return Response
 */
function hamtaSammanstallning(string $from, string $tom): Response {
    // Kontrollera indata
    $fromDate = Datetime::createFromFormat('Y-m-d', $from);
    $tomDate = Datetime::createFromFormat('Y-m-d', $tom);

    $datumKoll = [];
    if ($fromDate === false) {
        $datumKoll[] = "Felaktigt angivet från-datum";
    } elseif ($fromDate->format('Y-m-d') !== $from) {
        $datumKoll[] = "Felaktigt formaterat från-datum";
    }
    if ($tomDate === false) {
        $datumKoll[] = "Felaktigt angivet till-datum";
    } elseif ($tomDate->format('Y-m-d') !== $tom) {
        $datumKoll[] = "Felaktigt formaterat till-datum";
    }
    if ($fromDate && $tomDate && $fromDate->format('Y-m-d') > $tomDate->format('Y-m-d')) {
        $datumKoll[] = "Från-datum ska vara mindre än till-datum";
    }
    if (count($datumKoll) > 0) {
        array_unshift($datumKoll, "Bad request");
        $retur = new StdClass();
        $retur->error = $datumKoll;
        return new Response($retur, 400);
    }

    // Koppla databas
    $db = connectDb();

    // Skicka fråga
    $stmt = $db->prepare("SELECT aktivitetsid, aktivitet, SEC_TO_TIME(SUM(TIME_TO_SEC (tid))) as summa
            FROM aktiviteter a 
            INNER JOIN uppgifter u ON aktivitetsid=a.id
            WHERE datum BETWEEN :from AND :to
            GROUP BY aktivitetsid");
    $stmt->execute(['from' => $fromDate->format('Y-m-d'), 'to' => $tomDate->format('Y-m-d')]);
    $allaRader = $stmt->fetchAll();

    // Returnera svar
    $retur = [];
    foreach ($allaRader as $rad) {
        $post = new StdClass();
        $post->activityId = $rad['aktivitetsid'];
        $post->activity = $rad['aktivitet'];
        $post->time = substr($rad['summa'], 0, -3);
        $retur[] = $post;
    }
    $svar = new stdClass();
    $svar->tasks = $retur;
    return new Response($svar, 200);
}