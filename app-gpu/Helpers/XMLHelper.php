<?php

namespace App\Helpers;

class XMLHelper
{
    /**
     * Converte um objeto SimpleXMLElement em um array associativo.
     * 
     * @param SimpleXMLElement $xmlObject
     * @return array
     */
    public static function xmlToArray($xmlObject)
    {
        $json = json_encode($xmlObject);
        return json_decode($json, true);
    }
    
}