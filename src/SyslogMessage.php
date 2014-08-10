<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SyslogMessage
 *
 * Message of Syslog message as described in <a href="http://tools.ietf.org/html/rfc5424#section-6.4">RFC 5424 Msg</a>
 * 
 * @author Talaeezadeh <your.brother.t@hotmail.com>
 */
class SyslogMessage {
    
    protected static $BOM = "\xEF\xBB\xBF";
    
    protected $MESSAGE;
    
    /**
     * Normalizes the input to utf8 if possible, then sets the message to this value; without BOM.
     * 
     * @param string $message
     * @return \SyslogMessage
     */
    public function setMessage($message)
    {
        $message = iconv(mb_detect_encoding($message), 'UTF-8', $message);
        if (substr($message, 0, 3) == "\xEF\xBB\xBF")
        {
            $message = substr($message, 3);
        }
        $this->MESSAGE = $message;
        return $this;
    }
    
    /**
     * Returns the utf8 string of the message without BOM
     * 
     * @return string
     */
    public function getMessage()
    {
        return $this->MESSAGE;
    }
    
    /**
     * Returns the BOM.
     * 
     * It is statically always set to utf8 BOM (\xEF\xBB\xBF).
     * 
     * @return type
     */
    public static function getBOM()
    {
        return self::$BOM;
    }

    /**
     * Creates a message based on input string.
     * @param string $input
     * @return \static
     */
    public static function fromString($input)
    {
        $result = new static;
        $result->setMessage($input);
        return $result;
    }
    
    public function __sleep()
    {
        return array('MESSAGE');
    }
    
    public function __wakeup()
    {
    }
}
