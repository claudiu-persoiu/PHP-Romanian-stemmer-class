<?php

/**
 * Usage exemple for RomanianStemmer
 */


// the output must be UTF-8 for diacritics
header ('Content-type: text/html; charset=utf-8');

require 'RomanianStemmer.php';

$words = <<<HERE
anomalie
anomalii
anonim
anonimă
ansamblu
ansamblul
anselm
antantist
ante
antebraţul
antepresupoziţiuni
anterioară
anterioare
anterior
antică
antice
antici
anticipat
anticipată
anticipaţie
anticipaţiilor
anticipez
antiintervenţionist
antilopă
antinomie
antipatic
HERE;

$words = explode("\r\n", $words);

foreach($words as $word) {
    echo $word . ' - ' . RomanianStemmer::Stem($word) . '<br />';
}

?>