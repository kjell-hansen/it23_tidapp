<?php

declare (strict_types=1);
require_once __DIR__ . '/funktioner.php';

/**
 * Läs av rutt-information och anropa funktion baserat på angiven rutt
 * @param Route $route Rutt-information
 * @param array $postData Indata för behandling i angiven rutt
 * @return Response
 */
function activities(Route $route, array $postData): Response {
//    return new Response("Aktiviteter");
    try {
        if (count($route->getParams()) === 0 && $route->getMethod() === RequestMethod::GET) {
            return hamtaAllaAktiviteter();
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::GET) {
            return hamtaEnskildAktivitet($route->getParams()[0]);
        }
        if (isset($postData["activity"]) && count($route->getParams()) === 0 &&
            $route->getMethod() === RequestMethod::POST) {
            return sparaNyAktivitet((string)$postData["activity"]);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::PUT) {
            return uppdateraAktivitet($route->getParams()[0], $postData["activity"]);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::DELETE) {
            return raderaAktivetet($route->getParams()[0]);
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }

    return new Response("Okänt anrop", 400);
}

/**
 * Returnerar alla aktiviteter som finns i databasen
 * @return Response
 */
function hamtaAllaAktiviteter(): Response {
    // Koppla upp mot databasen
    $db = connectDb();

    // Hämta alla aktiviteter
    $result = $db->query("SELECT id, aktivitet  FROM aktiviteter ORDER BY id");

    // Skapa retur
    $retur = [];
    foreach ($result as $row) {
        $post = new stdClass();
        $post->id = $row["id"];
        $post->activity = $row["aktivitet"];
        $retur[] = $post;
    }

    // Skicka tillbaka svar
    return new Response($retur);
}

/**
 * Returnerar en enskild aktivitet som finns i databasen
 * @param string $id Id för aktiviteten
 * @return Response
 */
function hamtaEnskildAktivitet(string $id): Response {
    // Kontrollera inparameter
    $kontrolleradId = filter_var($id, FILTER_VALIDATE_INT);

    if ($kontrolleradId === false || $kontrolleradId < 1) {
        $retur = new stdClass();
        $retur->error = ["Bad request", "Ogiltigt id"];
        return new Response($retur, 400);
    }

    // Koppla mot databas
    $db = connectDb();

    // Skicka fråga
    $result = $db->query("SELECT id, aktivitet  FROM aktiviteter WHERE id=$kontrolleradId");

    // Kontrollera svar
    if ($result->rowCount() > 0) {
        $row = $result->fetch();
        $svar = new stdClass();
        $svar->id = $row["id"];
        $svar->activity = $row["aktivitet"];
    } else {
        $svar = new stdClass();
        $svar->error = ["Bad request", "Angivet id ($kontrolleradId) finns inte"];
        return new Response($svar, 400);
    }

    // Returnera svar
    return new Response($svar);
}

/**
 * Lagrar en ny aktivitet i databasen
 * @param string $aktivitet Aktivitet som ska sparas
 * @return Response
 */
function sparaNyAktivitet(string $aktivitet): Response {
    // Kontrollera indata
    // Rensa onödiga tecken
    $kontrolleradAktivitet = filter_var($aktivitet, FILTER_SANITIZE_SPECIAL_CHARS);

    // Kontrollera att aktiviteten inte är tom
    if (trim($kontrolleradAktivitet) === '') {
        $retur = new stdClass();
        $retur->error = ["Bad request", "Aktivitet får inte vara tom"];
        return new Response($retur, 400);
    }

    // Koppla mot databas
    $db = connectDb();

    try {
        // Skicka fråga för att spara ny post
        $stmt = $db->prepare("INSERT INTO aktiviteter (aktivitet) VALUES (:aktivitet)");
        $svar = $stmt->execute(["aktivitet" => $kontrolleradAktivitet]);

        // Kontrollera svaret
        if ($svar === false) {
            $retur = new stdClass();
            $retur->error = ['Spara misslyckades', 'Något gick fel vid spara'];
            return new Response($retur, 400);
        }

        // Returnera id och meddelande om att det gick bra
        $retur = new stdClass();
        $retur->id = $db->lastInsertId();
        $retur->meddelande = ['Spara lyckades', '1 post lades till'];

        return new Response($retur);
    } catch (Exception $exc) {
        $retur = new stdClass();
        $retur->error=['Spara misslyckades', $exc->getMessage()];
        return new Response($retur, 400);
    }
}

/**
 * Uppdaterar angivet id med ny text
 * @param string $id Id för posten som ska uppdateras
 * @param string $aktivitet Ny text
 * @return Response
 */
function uppdateraAktivitet(string $id, string $aktivitet): Response {
}

/**
 * Raderar en aktivitet med angivet id
 * @param string $id Id för posten som ska raderas
 * @return Response
 */
function raderaAktivetet(string $id): Response {
}