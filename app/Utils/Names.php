<?php

namespace App\Utils;

use Oefenweb\DamerauLevenshtein\DamerauLevenshtein;

class Names
{
    
    public static function damlev($name1, $name2) {
        $dl = new DamerauLevenshtein($name1, $name2);
        return $dl->getSimilarity();
    }
    
}

