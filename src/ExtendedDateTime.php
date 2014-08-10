<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExtendedDateTime
 *
 * An extended DataTime class to support the microseconds and FRACSEC as described in <a href="http://tools.ietf.org/html/rfc5424#section-6.2.3">RFC 5424 Timestamp</a>
 * 
 *      Special Thanks to:
 *          <a href="http://stackoverflow.com/users/14651/enobrev">enobrev</a> for this <a href="http://stackoverflow.com/questions/169428/php-datetime-microseconds-always-returns-0">Article</a>
 * @author Talaeezadeh <your.brother.t@hotmail.com>
 */
class ExtendedDateTime extends DateTime {
    
    protected $fuckingPHP;
    
    /**
     * Returns new DateTime object.  Adds microtime for "now" dates
     * @param string $sTime
     * @param DateTimeZone $oTimeZone 
     */
    public function __construct($sTime = 'now', DateTimeZone $oTimeZone = NULL) {
        // check that constructor is called as current date/time
        if (strtotime($sTime) == time()) {
            $aMicrotime = explode(' ', microtime());
            $sTime = date('Y-m-d\TH:i:s.' . $aMicrotime[0] * 1000000 . 'P', $aMicrotime[1]);
            $this->fuckingPHP = $sTime;
        }

        // DateTime throws an Exception with a null TimeZone
        if ($oTimeZone instanceof DateTimeZone) {
            parent::__construct($sTime, $oTimeZone);
        } else {
            parent::__construct($sTime);
        }
    }
    
    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        return array('fuckingPHP');
    }
    
    /**
     * 
     */
    public function __wakeup() {
        //parent::__wakeup();
        parent::__construct($this->fuckingPHP);
    }

    /**
     * 
     * @return type
     */
    public function __toString()
    {
        return $this->format('Y-m-d\TH:i:s.uP');
    }
    
}