<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SyslogElement
 *
 * Element of Syslog message as described in <a href="http://tools.ietf.org/html/rfc5424#section-6.3.1">RFC 5424 SD-Element</a>
 * 
 * @author Talaeezadeh <your.brother.t@hotmail.com>
 */
class SyslogElement {
    
    protected $SD_ID;
    protected $SD_PARAM;
    
    /**
     * Sets the SD-ID parameter
     * 
     *  mutable
     * 
     * @param String $IETF_Name
     * @param String $IANA_PEN
     * @return \SyslogElement
     */
    public function &setID(array $SD_ID = array())
    {
        if (isset($SD_ID['IETF']))
        {
            $IETF_Name = $SD_ID['IETF'];
        }
        else
        {
            return $this;
        }
        if (isset($SD_ID['IANA']))
        {
            $IANA_PEN = $SD_ID['IANA'];
        }
        else
        {
            $IANA_PEN = NULL;
        }
        if (!empty($IANA_PEN))
        {
            if (is_numeric($IANA_PEN))
            {
                $IANA_PEN = (int)$IANA_PEN;
            }
            elseif (is_string($IANA_PEN))
            {
                $tmp = ord($IANA_PEN[0]);
                for($i = 1; $i < strlen($IANA_PEN); $i++)
                {
                    $tmp .= ord($IANA_PEN[$i]);
                }
            }
            $tmp = $IETF_Name . '@' . $tmp;
            $tmp = SyslogHelper::standardize($tmp, 'ASCII',32,'=\s\]\"');
            preg_match_all('/(.*?)@(.*?)$/', $tmp, $tmp);
            $this->SD_ID['IETF'] = $tmp[1][0];
            $this->SD_ID['IANA'] = $tmp[2][0];
            
        }
        else
        {
            $tmp = $IETF_Name;
            $tmp = SyslogHelper::standardize($tmp, 'ASCII',32,'=\s\]\"');
            $this->SD_ID['IETF'] = $tmp;
            $this->SD_ID['IANA'] = NULL;
        }
        return $this;
    }
    
    public function getID()
    {
        return $this->SD_ID;
    }
    
    /**
     * Add a new or rewite a SD-PARAM parameter
     * 
     *  mutable
     * 
     * @param String $name
     * @param Mixed $value
     * @return \SyslogElement
     */
    public function &add($name, $value)
    {
        $name = SyslogHelper::standardize($name, 'ASCII',32,'=\s\]\"');
        $value = SyslogHelper::standardize($value, 'UTF-8',-1,'\\\]\"');
        $input = array
            (
                'NAME'  =>  $name,
                'VALUE' =>  $value
            );
        
        foreach ($this->SD_PARAM as $key => $param)
        {
            if ($param['NAME'] === $name)
            {
                $this->SD_PARAM[$key] = $input;
                return $this;
            }
        }
        array_push
        (
            $this->SD_PARAM, 
            $input
        );
        return $this;
    }
    
    /**
     * Removes an SD-PARAM based on its name
     * @param type $name
     * @return \SyslogElement
     */
    public function &remove($name)
    {
        $name = SyslogHelper::standardize($name, 'ASCII',32,'=\s\]\"');
        
        foreach ($this->SD_PARAM as $key => $param)
        {
            if ($param['NAME'] === $name)
            {
                unset($this->SD_PARAM[$key]);
            }
        }
        return $this;
    }

    public function __sleep()
    {
        return array('SD_ID','SD_PARAM');
    }
    
    public function __wakeup()
    {
    }

    /**
     * Makes an array of SD-ELEMENTs based on an input string or array.
     * 
     * @param mixed $input
     * @return array
     */
    public static function fromString($input)
    {
        $result = array();
        if (is_string($input))
        {
            preg_match_all('/\[(.*?)\]/', $input, $input);
        }
        foreach ($input[1] as $key => $value)
            {
                $result[$key] = new static;
                $current = explode(" ", $value);
                ///'(\w*)[\@]?(\d*)?$';
                preg_match_all('/^(.*)(?:@)(.*?)$|^(.*)$/', $current[0], $matches);
                
                if (empty($matches[3]))
                {
                    $result[$key]->SD_ID['IETF'] = $matches[1];
                    $result[$key]->SD_ID['IANA'] = $matches[2];
                }
                else
                {
                    $result[$key]->SD_ID['IETF'] = $matches[3];
                    $result[$key]->SD_ID['IANA'] = NULL;
                }
                preg_match_all('/(?:.*?\s)?(.*?)="(.*?)?"|(?<=\s)\G\s(.*?)="(.*?)?"(?:\s|$)/', $value, $matches);
                unset($matches[3]);
                unset($matches[4]);
                for ($i = 0; $i < count($matches[0]); $i++)
                {
                    $result[$key]->add($matches[1][$i], $matches[2][$i]);
                }
                
            }
        return $result;
    }
    
    public function __toString()
    {
        $serialized = "[" . $this->SD_ID['IETF'];
        
        if (!empty($this->SD_ID['IANA']))
        {
            $serialized .= "@" . $this->SD_ID['IANA'];
        }
        
        $tmplength = count($this->SD_PARAM) - 1;
        if ($tmplength + 1 > 0)
        {
            $serialized .= " ";
        }
        $i = 0;
        for (; $i < $tmplength; $i++)
        {
            if (isset($this->SD_PARAM[$i]))
            {
                if(is_a($this->SD_PARAM[$i]['VALUE'], 'object'))
                {
                    if (!method_exists($this->SD_PARAM[$i]['VALUE'], '__toString'))
                    {
                        $serialized .= $this->SD_PARAM[$i]['NAME'] . "=\"\" ";
                        continue;
                    }
                }
                $serialized .= $this->SD_PARAM[$i]['NAME'] . "=\"" . $this->SD_PARAM[$i]['VALUE'] . "\" ";
            }
        }
        if (isset($this->SD_PARAM[$i]))
        {
            if(is_a($this->SD_PARAM[$i]['VALUE'], 'object'))
            {
                if (!method_exists($this->SD_PARAM[$i]['VALUE'], '__toString'))
                {
                    $serialized .= $this->SD_PARAM[$i]['NAME'] . "=\"\"";
                }
            }
            $serialized .= $this->SD_PARAM[$i]['NAME'] . "=\"" . $this->SD_PARAM[$i]['VALUE'] . "\"";
        }
        
        return $serialized . "]";
    }
    
    public function __construct()
    {
        $this->SD_ID['IETF'] = '';
        $this->SD_PARAM = array();
    }
    
}
