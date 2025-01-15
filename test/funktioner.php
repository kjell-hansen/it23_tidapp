<?php
declare(strict_types=1);

foreach (glob("./*.php") as $filename) {
    require_once $filename;
}

function ingenRutt(string $param): string {
    return "<p class'error'>Det finns ingen rutt med angivna parametrar ($param)</p>";
}

/**
 * Funktion för att testa en enskild funktion
 * @param string $funktion namnet (utan test_) på funktionen som ska testas
 * @return string html-sträng med information om resultatet av testen eller att testet inte fanns
 */
function testFunction(string $funktion): string {
    if (function_exists("test_$funktion")) {
        return call_user_func("test_$funktion");
    } else {
        return "<p class='error'>Funktionen test_$funktion finns inte.</p>";
    }
}