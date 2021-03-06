<?php

/**
 * Test script for RomanianStemmer
 */

require 'RomanianStemmer.php';

$words = <<<HERE
abruptă
absent
absentă
absente
absenţa
absenţă
absenţi
absolut
absoluta
absolută
absolute
absolutul
absolutului
absoluţi
absolve
absolvenţi
absolvenţii
absolvi
absolvire
absolvit
absolvită
absolviţi
absorbant
absorbantă
absorbi
absorbit
absorbite
absorbiţi
absorbţia
abstinent
abstract
abstractă
abstracte
abstractiza
abstractizare
abstractizat
abstractizăm
abstracto
abstracţia
abstracţii
ocol
ocolea
ocolesc
ocoleşte
ocoleşti
ocoli
ocolim
ocolind
ocolire
ocolişuri
ocolit
ocolită
ocoliţi
ocolul
ocoluri
ocolurile
ocrotit
ocrotitoare
ocrotitor
ocrotiţi
octavă
octavian
octet
octeţi
octogenarul
octombrie
ocular
ocult
ocultarea
ocultat
ocultă
ocultării
oculţi
ocup
ocupa
ocupai
ocupanţi
ocupanţii
ocupase
ocupat
HERE;

$expectedResults = <<<HERE
abrupt
absent
absent
absent
absenţ
absenţ
absenţ
absol
absol
absol
absol
absol
absol
absoluţ
absolv
absolvenţ
absolvenţ
absolv
absolv
absolv
absolv
absolv
absorb
absorb
absorb
absorb
absorb
absorb
absorbţ
abstinent
abstract
abstract
abstract
abstractiz
abstractiz
abstractiz
abstractiz
abstracto
abstracţ
abstracţ
ocol
ocol
ocol
ocol
ocol
ocol
ocol
ocol
ocol
ocolişur
ocol
ocol
ocol
ocol
ocolur
ocolur
ocrot
ocrot
ocrot
ocrot
octav
octavian
octet
octeţ
octogenar
octombr
ocular
ocult
ocult
ocult
ocult
ocultăr
oculţ
ocup
ocup
ocup
ocupanţ
ocupanţ
ocup
ocup
HERE;


$words = explode("\n", $words);
$expectedResults = explode("\n", $expectedResults);

for($i = 0; $i < count($words); $i++) {
    $word = $words[$i];
    $expected = $expectedResults[$i];
    $stem = RomanianStemmer::Stem($words[$i]);

    if (strcmp($stem, $expected) !== 0) {
        echo 'Word: ' . $words[$i] . ' - Stem ' . $stem . ' - Expected: ' . $expected . PHP_EOL;
        exit(1);
    }
}

echo 'Success';
