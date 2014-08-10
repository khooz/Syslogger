<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'ExtendedDateTime.php';
require_once 'SyslogHelper.php';

/**
 * Description of SyslogHeader
 *
 * Header of Syslog message as described in <a href="http://tools.ietf.org/html/rfc5424#section-6.2">RFC 5424 Headers</a>
 * 
 * @author Talaeezadeh <your.brother.t@hotmail.com>
 */
class SyslogHeader {
    
    // <editor-fold defaultstate="collapsed" desc="Facilities">
    
    const fac_kernel_messages = 0;
    const fac_user_level_messages = 1;
    const fac_mail_system = 2;
    const fac_system_daemons = 3;
    const fac_security_or_authorization_messages = 4;
    const fac_messages_generated_internally_by_syslogd = 5;
    const fac_line_printer_subsystem = 6;
    const fac_network_news_subsystem = 7;
    const fac_UUCP_subsystem = 8;
    const fac_clock_daemon = 9;
    const fac_security_or_authorization_messages_1 = 10;
    const fac_FTP_daemon = 11;
    const fac_NTP_subsystem = 12;
    const fac_log_audit = 13;
    const fac_log_alert = 14;
    const fac_clock_daemon_1 = 15;
    const fac_local_use_0 = 16;
    const fac_local_use_1 = 17;
    const fac_local_use_2 = 18;
    const fac_local_use_3 = 19;
    const fac_local_use_4 = 20;
    const fac_local_use_5 = 21;
    const fac_local_use_6 = 22;
    const fac_local_use_7 = 23;
    
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Severities">
    
    const emergency = 0;
    const alert = 1;
    const critical = 2;
    const error = 3;
    const warning = 4;
    const notice = 5;
    const informational = 6;
    const debug = 7;
    
    // </editor-fold>
    
    public $UUID;
    protected $PRI;
    protected $VERSION;
    protected $TIMESTAMP;
    protected $HOSTNAME;
    protected $APP_NAME;
    protected $PROCID;
    protected $MSGID;
    
    /**
     * Calculates and sets the PRI based on severity and facility
     * 
     * (see <a href="http://tools.ietf.org/html/rfc5424#section-6.2.1">PRI</a>)
     * 
     * @param int $severity Severity number based on RFC5424:
     *      <ol start="0">
     *          <li>Emergency</li>
     *          <li>Alert</li>
     *          <li>Critical</li>
     *          <li>Error</li>
     *          <li>Warning</li>
     *          <li>Notice</li>
     *          <li>Informational</li>
     *          <li>Debug</li>
     *      </ol> 
     * @param int $facility Facility number based on OFC5424:
     *      <ol start="0">
     *          <li>fac_kernel_messages</li>
     *          <li>fac_user_level_messages</li>
     *          <li>fac_mail_system</li>
     *          <li>fac_system_daemons</li>
     *          <li>fac_security_or_authorization_messages</li>
     *          <li>fac_messages_generated_internally_by_syslogd</li>
     *          <li>fac_line_printer_subsystem</li>
     *          <li>fac_network_news_subsystem</li>
     *          <li>fac_UUCP_subsystem</li>
     *          <li>fac_clock_daemon</li>
     *          <li>fac_security_or_authorization_messages_1</li>
     *          <li>fac_FTP_daemon</li>
     *          <li>fac_NTP_subsystem</li>
     *          <li>fac_log_audit</li>
     *          <li>fac_log_alert</li>
     *          <li>fac_clock_daemon_1</li>
     *          <li>fac_local_use_0</li>
     *          <li>fac_local_use_1</li>
     *          <li>fac_local_use_2</li>
     *          <li>fac_local_use_3</li>
     *          <li>fac_local_use_4</li>
     *          <li>fac_local_use_5</li>
     *          <li>fac_local_use_6</li>
     *          <li>fac_local_use_7</li>
     *      </ol>
     * @return \SyslogHeader mutable
     */
    public function &setPRI($severity, $facility)
    // <editor-fold defaultstate="collapsed" desc="Sets Syslog message's PRI">
    {
        $this->PRI = $severity | ($facility << 3);
        if ($this->PRI > 191)
        {
            $this->PRI = 191;
        }
        elseif($this->PRI < 0)
        {
            $this->PRI = 0;
        }
        return $this;
    }
    // </editor-fold>
    
    /**
     * Sets the VERSION of Syslog message based on RFC5424 (see <a href="http://tools.ietf.org/html/rfc5424#section-6.2.2">VERSION</a>). If the value exceeds the standard, it will be set to boundry values.
     * 
     * @param int $input The desired version value.
     * @return \SyslogHeader mutable
     */
    public function &setVersion($input)
    // <editor-fold defaultstate="collapsed" desc="Sets Syslog message's version">
    {
        if (is_numeric($input))
        {
            if ($input < 1000 && $input >= 1)
            {
                $this->VERSION = (int) $input;
            }
            elseif ($input > 999)
            {
                $this->VERSION = 999;
            }
            elseif($input < 1)
            {
                $this->VERSION = 1;
            }
        }
        return $this;
    }
    // </editor-fold>
    
    /**
     * Sets the TIMESTAMP to a specified value or now as default.
     * 
     * (see <a href="http://tools.ietf.org/html/rfc5424#section-6.2.3">TIMESTAMP</a>)
     * 
     * @param string $sTime The desired time.
     * @param DateTimeZone $oTimeZone
     * @return \SyslogHeader mutable
     */
    public function &setTime($sTime = 'now', DateTimeZone $oTimeZone = NULL)
    // <editor-fold defaultstate="collapsed" desc="Sets Syslog message's timestamp">
    {
        $this->TIMESTAMP = new ExtendedDateTime($sTime);
        return $this;
    }
    // </editor-fold>
    
    /**
     * Sets the HOSTNAME based on RFC5424 values (see <a href="http://tools.ietf.org/html/rfc5424#section-6.2.4">HOSTNAME</a>) MUST be one of the following based on availability:
     *      <ol>
     *          <li>FQDN</li>
     *          <li>Static IP</li>
     *          <li>Hostname</li>
     *          <li>Dynamic IP</li>
     *          <li>NILVALUE (NULL)</li>
     *      </ol>
     * 
     * @param string $input The desired qualified hostname
     * @return \SyslogHeader mutable
     */
    public function &setHostname($input)
    // <editor-fold defaultstate="collapsed" desc="Sets Syslog message's hostname">
    {
        $this->HOSTNAME = SyslogHelper::standardize($input, $encoding = 'ASCII', 255, '\x00-\x20\x7F-\xFF\s');
        return $this;
    }
    // </editor-fold>
    
    /**
     * Sets the APP-NAME based on RFC5424 specifications on <a href="http://tools.ietf.org/html/rfc5424#section-6.2.5">App-NAME</a>.
     * 
     * @param string $input The desired appname
     * @return \SyslogHeader mutable
     */
    public function &setAppname($input)
    // <editor-fold defaultstate="collapsed" desc="Sets Syslog message's appname">
    {
        $this->APP_NAME = SyslogHelper::standardize($input, $encoding = 'ASCII', 48, '\x00-\x20\x7F-\xFF\s');
        return $this;
    }
    // </editor-fold>
    
    /**
     * Sets the PROCID based on RFC5424 specifications on <a href="http://tools.ietf.org/html/rfc5424#section-6.2.6">PROCID</a>.
     * 
     * @param string $input The desired ProcID
     * @return \SyslogHeader mutable
     */
    public function &setProcID($input)
    // <editor-fold defaultstate="collapsed" desc="Sets Syslog message's ProcID">
    {
        $this->PROCID = SyslogHelper::standardize($input, $encoding = 'ASCII', 128, '\x00-\x20\x7F-\xFF\s');
        return $this;
    }
    // </editor-fold>
    
    /**
     * Sets the MSGID based on RFC5424 specifications on <a href="http://tools.ietf.org/html/rfc5424#section-6.2.7">MSGID</a>.
     * 
     * @param type $input The desired MsgID
     * @return \SyslogHeader mutable
     */
    public function &setMsgID($input)
    // <editor-fold defaultstate="collapsed" desc="Sets Syslog message's MsgID">
    {
        $this->MSGID = SyslogHelper::standardize($input, $encoding = 'ASCII', 32, '\x00-\x20\x7F-\xFF\s');
        return $this;
    }
    // </editor-fold>
    
    /**
     * Returns an array consisting of FACILITY and SEVERITY of log.
     * 
     * {@see SyslogHeader::setPRI()}
     * 
     * @return array array of PRI 'SEVERITY' and 'FACILITY'
     */
    public function getPRI()
    // <editor-fold defaultstate="collapsed" desc="Gets the PRI">
    {
        return array(
            'SEVERITY' => $this->PRI % 8,
            'FACILITY' => $this->PRI >> 3
            );
    }
    // </editor-fold>

    /**
     * Returns the messages version
     * 
     * {@see SyslogHeader::setVersion()}
     * 
     * @return int Message's version
     */
    public function getVersion()
    // <editor-fold defaultstate="collapsed" desc="Gets message's version">
    {
        return $this->VERSION;
    }
    // </editor-fold>
    
    /**
     * Returns the TIMESTAMP of log
     * 
     * {@see SyslogHeader::setTimestamp()}
     * 
     * @return \ExtendedDateTime The timestamp
     */
    public function getTime()
    // <editor-fold defaultstate="collapsed" desc="Gets the message's timestamp">
    {
        return $this->TIMESTAMP;
    }
    // </editor-fold>
    
    /**
     * Returns a the message's HOSTNAME.
     * 
     * {@see SyslogHeader::setHostname()}
     * 
     * @return string Hostname
     */
    public function getHostname()
    // <editor-fold defaultstate="collapsed" desc="Gets message's hostname">
    {
        return $this->HOSTNAME;
    }
    // </editor-fold>
    
    /**
     * Returns a the message's APP-NAME.
     * 
     * {@see SyslogHeader::setAppname()}
     * 
     * @return string The appname
     */
    public function getAppname()
    // <editor-fold defaultstate="collapsed" desc="">
    {
        return $this->APP_NAME;
    }
    // </editor-fold>
    
    /**
     * Returns a the message's PROCID.
     * 
     * {@see SyslogHeader::setProcID()}
     * 
     * @return string The ProcID
     */
    public function getProcID()
    // <editor-fold defaultstate="collapsed" desc="">
    {
        return $this->PROCID;
    }
    // </editor-fold>
    
    /**
     * Returns a the message's MSGID.
     * 
     * {@see SyslogHeader::setMsgID()}
     * 
     * @return string The MsgID
     */
    public function getMsgID()
    // <editor-fold defaultstate="collapsed" desc="">
    {
        return $this->MSGID;
    }
    // </editor-fold>
    
    public function logHeader()
    {
        $serialized = '';
        
        // <editor-fold defaultstate="collapsed" desc="PRI">
        if (empty($this->PRI))
        {
            if ($this->PRI == 0)
            {
                $serialized .= "<" . $this->PRI . ">";
            }
            else
            {
                $serialized .= "-";
            }
        }
        else
        {
            $serialized .= "<" . $this->PRI . ">";
        }
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="VERSION">
        if (empty($this->VERSION))
        {
            $serialized .= "- ";
        }
        else
        {
            $serialized .= $this->VERSION . " ";
        }
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="TIMESTAMP">
        if (empty($this->TIMESTAMP))
        {
            $serialized .= "- ";
        }
        else
        {
            $serialized .= $this->TIMESTAMP->format("Y-m-d\TH:i:s.uP ");
        }
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="HOSTNAME">
        if (empty($this->HOSTNAME))
        {
            $serialized .= "- ";
        }
        else
        {
            $serialized .= $this->HOSTNAME . " ";
        }
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="APP-NAME">
        if (empty($this->APP_NAME))
        {
            $serialized .= "- ";
        }
        else
        {
            $serialized .= $this->APP_NAME . " ";
        }
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="PROCID">
        if (empty($this->PROCID))
        {
            $serialized .= "- ";
        }
        else
        {
            $serialized .= $this->PROCID . " ";
        }
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="MSGID">
        if (empty($this->MSGID))
        {
            $serialized .= "-";
        }
        else
        {
            $serialized .= $this->MSGID;
        }
        // </editor-fold>
        
        return $serialized;
    }


    /**
     * 
     * @return array
     */
    public function __sleep() {
        return array('UUID','PRI','VERSION','TIMESTAMP','HOSTNAME','APP_NAME','PROCID','MSGID');
    }
    
    /**
     * 
     */
    public function __wakeup()
    {
    }
    
    /**
     * Makes a header based on an input string or an array.
     * 
     * @param mixed $input
     * @return \static
     */
    public static function fromString($input)
    // <editor-fold defaultstate="collapsed" desc="">
    {
        $result = new static;
        if (is_string($input))
        {
            $input = explode(" ", $input);
            $input = array_slice($input, 0, 7);
        }
        preg_match_all('/<(.*?)>(.*?)$/', $input[1], $matches);
        if (isset($matches[1][0]))
        {
            $result->PRI = (int)$matches[1][0];
        }
        if (isset($matches[2][0]))
        {
            $result->VERSION = (int)$matches[2][0];
        }
        $result->UUID = $input[0];
        $result->setTime($input[2]);
        $result->HOSTNAME = $input[3];
        $result->APP_NAME = $input[4];
        $result->PROCID = $input[5];
        $result->MSGID = $input[6];
        return $result;
    }
    // </editor-fold>

    /**
     * 
     * @return string
     */
    public function __toString() 
    // <editor-fold defaultstate="collapsed" desc="">
    {
        $serialized = $this->UUID . " ";
        
        // <editor-fold defaultstate="collapsed" desc="PRI">
        if (empty($this->PRI))
        {
            if ($this->PRI == 0)
            {
                $serialized .= "<" . $this->PRI . ">";
            }
            else
            {
                $serialized .= "-";
            }
        }
        else
        {
            $serialized .= "<" . $this->PRI . ">";
        }
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="VERSION">
        if (empty($this->VERSION))
        {
            $serialized .= "- ";
        }
        else
        {
            $serialized .= $this->VERSION . " ";
        }
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="TIMESTAMP">
        if (empty($this->TIMESTAMP))
        {
            $serialized .= "- ";
        }
        else
        {
            $serialized .= $this->TIMESTAMP->format("Y-m-d\TH:i:s.uP ");
        }
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="HOSTNAME">
        if (empty($this->HOSTNAME))
        {
            $serialized .= "- ";
        }
        else
        {
            $serialized .= $this->HOSTNAME . " ";
        }
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="APP-NAME">
        if (empty($this->APP_NAME))
        {
            $serialized .= "- ";
        }
        else
        {
            $serialized .= $this->APP_NAME . " ";
        }
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="PROCID">
        if (empty($this->PROCID))
        {
            $serialized .= "- ";
        }
        else
        {
            $serialized .= $this->PROCID . " ";
        }
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="MSGID">
        if (empty($this->MSGID))
        {
            $serialized .= "-";
        }
        else
        {
            $serialized .= $this->MSGID;
        }
        // </editor-fold>
        
        return $serialized;
    }
    // </editor-fold>
    
    /**
     * 
     * @param string $guid
     */
    public function __construct($guid = NULL)
    {
        if (!empty($guid))
        {
            $this->UUID = $guid;
        }
        $this->UUID = SyslogHelper::GUID();
    }
}
