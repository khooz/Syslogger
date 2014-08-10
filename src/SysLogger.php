<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'SyslogBlock.php';

/**
 * Description of Logger
 *
 * @author Talaeedeh
 */
class SysLogger
{
    
    protected $server;
    protected $pool;
    
    /**
     * 
     * @param type $IP
     * @param type $Port
     * @return boolean
     */
    public function setServer($IP, $PORT)
    {
        $ipver = 4;
        $badip = false;
        preg_match('/(\d+).(\d+).(\d+).(\d+)/', $IP, $matches);
        foreach ($matches as $match)
        {
            if (count($match)<1)
            {
                $badip = true;
                $ipver = 6;
                break;
            }
        }
        if($badip)
        {
            preg_match('/(\x+):(\x+):(\x+):(\x+):/', $IP, $matches);
            foreach ($matches as $match)
            {
                if (count($match)<1)
                {
                    $badip = true;
                    break;
                }
            }
            $badip = false;
        }
        if($badip)
        {
            return false;
        }
        $this->server["IP"] = $IP;
        $this->server["PORT"] = (int)$PORT;
        return true;
    }
    
    public function &add(SyslogBlock &$message)
    {
        if (!is_array($this->pool))
        {
            $this->pool[0] = $message;
        }
        else
        {
            $this->pool[count($this->pool)] = $message;
        }
        return $this;
    }
    
    /**
     * 
     * @return null
     */
    public function &getLast()
    {
        if (is_array($this->pool))
        {
            return $this->pool[count($this->pool)-1];
        }
        else
        {
            return NULL;
        }
    }
    
    /**
     * 
     * @param type $index
     * @return null
     */
    public function &getByIndex($index = 0)
    {
        if (isset($this->pool[$index]))
        {
            return $this->pool[$index];
        }
        else
        {
            return NULL;
        }
    }
    
    /**
     * 
     * @return int
     */
    public function getPoolSize()
    {
        if (is_array($this->pool))
        {
            return count($this->pool);
        }
        else
        {
            return 0;
        }
    }
    
    

        /**
     * 
     * @return boolean
     */
    public function flush()
    {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        for ($i = 0; $i < count($this->pool); $i++)
        {
            if (isset($this->pool[$i]))
            {
                $messageContent = $this->pool[$i]->logBlock();
                if (!socket_sendto($socket, $messageContent, strlen($messageContent), 0, $this->server["IP"], $this->server["PORT"]))
                {
                    return FALSE;
                }
                else
                {
                    unset($this->pool[$i]);
                }
            }
        }
        $this->pool = NULL;
        return TRUE;
    }
    
    /**
     * 
     * @param type $IP
     * @param type $PORT
     */
    public function __construct($IP = '127.0.0.1', $PORT = 514)
    {
        $this->setServer($IP, $PORT);
    }
}
