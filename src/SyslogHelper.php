<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Definitions
 * 
 * @package SyslogDefinitions
 * @author Talaeezadeh <your.brother.t@hotmail.com>
 */

class SyslogHelper
{

    /**
     * Generates a MS-GUID style UUID.
     * @return string GUID
     */   
    public static function GUID()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    /**
     * Standardizes string inputs to the desired encoding
     * 
     * Default is targetted for PRINTUSASCII defined in <a href="http://tools.ietf.org/html/rfc5424#section-6">Syslog Message Format</a>
     * 
     * @param string $input Input string
     * @param string $encoding Destination encoding
     * @param int $length The length of the output
     * @param string $notAllowed List of characters not allowed in output, pcre regex compatible.
     * @return string Output
     */
    public static function standardize($input, $encoding = 'ASCII', $length = -1, $notAllowed = NULL)
    {
        $input = preg_replace('/['. $notAllowed .']+/', '', $input);
        try 
        {
            $input = iconv(mb_detect_encoding($input), $encoding."//TRANSLIT", $input);
        }
        catch (Exception $ex)
        {
            $input = iconv(mb_detect_encoding($input), $encoding."//IGNORE", $input);
        }
        //$input = preg_replace('/[\x00-\x20\x7F-\dFF]/', '', $input);
        if (strlen($input) > $length && $length != -1)
        {
            $input = substr($input, 0, $length);
        }
        return $input;
    }
}