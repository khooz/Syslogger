<?php

    require_once 'SyslogHeader.php';
    require_once 'SyslogElement.php';
    require_once 'SyslogMessage.php';
    
    /**
     *  Class SyslogBlock
     * 
     *      Block of Syslog based on <a href="http://tools.ietf.org/html/rfc5424">RFC 5424</a>
     *      
     *      @author Talaeezadeh <your.brother.t@hotmail.com>
     */
    class SyslogBlock {
        
        public $HEADER;
        
        protected $STRUCTURED_DATA;
        
        public $MSG;
        
        /**
         * Gets an element with given ID, NULL otherwise.
         * @param array $SD_ID an associative array for SD_ID
         *              <ul>
         *                  <li>'IETF'     =>      string: The name specified in IETF RFC5424</li>
         *                  <li>'IANA'     =>      int: The PEN number assigned by IANA</li>
         *              </ul>
         * @return null|\SyslogElement returns the reference to the element, NULL otherwise.
         */
        public function &getElem(array $SD_ID)
        {
            $result = NULL;
            if (isset($SD_ID['IETF']) && isset($SD_ID['IANA']))
            {
                $tmp = new SyslogElement();
                $tmp->setID($SD_ID);
                if (!empty($this->STRUCTURED_DATA))
                {
                    foreach ($this->STRUCTURED_DATA as $key => $value)
                    {
                        if ($value->getID() == $tmp->getID())
                        {
                            return $value;
                        }
                    }
                }
            }
            return $result;
        }
        
        /**
         * Adds new element to the STRUCTURED-DATA
         * Adds new elemet by the $SD_ID into the STRUCTURED_DATA array, or returns the existing one.
         * @param array $SD_ID
         * @return array The element by ref
         */
        public function &addElem(array $SD_ID = array())
        {
            $result = $this->getElem($SD_ID);
            if ($result == NULL)
            {
                array_push($this->STRUCTURED_DATA, new SyslogElement());
                $result = $this->STRUCTURED_DATA[count($this->STRUCTURED_DATA)-1]->setID($SD_ID);
            }
            return $result;
        }
        
        /**
         * Merges an array of elements into the STRUCTURED-DATA
         * 
         * @param array $array
         * @return \SyslogBlock mutable
         */
        public function &mergeElem(array $array)
        {
            foreach ($array as $key => $value)
            {
                $current = & $this->addElem($value->getID());
                foreach ($value->SD_PARAM as $param)
                {
                    $current->add($param['NAME'],$param['VALUE']);
                }
            }
            return $this;
        }
        
        public function logBlock()
        {
            $serialized = $this->HEADER->logHeader() . " ";
            if (!empty($this->STRUCTURED_DATA))
            {
                foreach ($this->STRUCTURED_DATA as $element)
                {
                    $serialized .= $element;
                }
            }
            else
            {
                $serialized .= "-";
            }
            // Compatibility Problem < php 5.5
		$tmp = $this->MSG->getMessage();
            if (!empty($tmp))
                $serialized .= " ". SyslogMessage::getBOM () . $this->MSG->getMessage() ;
            
            return $serialized;
        }


        // <editor-fold defaultstate="collapsed" desc="Magic functions">
        
        /**
         * 
         * @return array
         */
        public function __sleep()
        {
            return array('HEADER','STRUCTURED_DATA','MSG');
        }
        
        /**
         * Creates a new Syslog block from an input string.
         * 
         * @param string $input
         * @return \static
         */
        public static function fromString($input)
        {
            $result = new static;
            $exploded = explode(" ", $input);
            preg_match_all('/\[(.*?)\]/', $input, $elements);
            preg_match_all('/\] (.*?)$/', $input, $message);
            $header = array_slice($exploded, 0, 7);
            if (!empty($header))
            {
                $result->HEADER = SyslogHeader::fromString($header);
            }
            if (!empty($elements[1]))
            {
                $result->STRUCTURED_DATA = SyslogElement::fromString($elements);
            }
            else
            {
                if ($exploded[7] == '-')
                {
                    $body = $header = array_slice($exploded, 8);
                    $message[1][0] = '';
                    foreach ($body as $elem)
                    {
                        $message[1][0] .= $elem . " ";
                    }
                    $message[1][0] = trim($message[1][0]," ");
                }
            }
            if (!empty($message[1]))
            {
                $result->MSG = SyslogMessage::fromString($message[1][0]);
            }
            return $result;
        }
        
        /**
         * 
         * @return string
         */
        public function __toString()
        {
            $serialized = $this->HEADER . " ";
            if (!empty($this->STRUCTURED_DATA))
            {
                foreach ($this->STRUCTURED_DATA as $element)
                {
                    $serialized .= $element;
                }
            }
            else
            {
                $serialized .= "-";
            }
            $tmp = $this->MSG->getMessage();
            if (!empty($tmp))
                $serialized .= " ". SyslogMessage::getBOM () . $this->MSG->getMessage();
            
            return $serialized;
        }
        
        /**
         * 
         * @param ePayMessage $message
         * @param SyslogHeader $HEADER
         * @param array $STRUCTURED_DATA
         * @param SyslogMessage $MSG
         * @return type
         */
        public function __construct(ePayMessage $message = NULL, SyslogHeader $HEADER = NULL, array $STRUCTURED_DATA = NULL, SyslogMessage $MSG = NULL)
        {
            // <editor-fold defaultstate="collapsed" desc="By object">
            
            if (!empty($message))
            {
                $this->HEADER = $message->HEADER;
                $this->STRUCTURED_DATA = $message->STRUCTURED_DATA;
                $this->MSG = $message->MSG;
                return;
            }
            
            // </editor-fold>
            
            // <editor-fold defaultstate="collapsed" desc="By attributes">
            
            else
            {
                // <editor-fold defaultstate="collapsed" desc="Setting HEADER">

                // If HEADER is not provided:
                if (empty($HEADER))
                {
                    $this->HEADER = new SyslogHeader();
                }
                // Header is Partially or fully provided.
                else
                {
                    $this->HEADER = $HEADER;
                }

                // </editor-fold>

                // <editor-fold defaultstate="collapsed" desc="Setting STRUCTURED_DATA">

                // If STRUCTURED_DATA is not provided:
                if (empty($STRUCTURED_DATA))
                {
                    $this->STRUCTURED_DATA = array();
                }
                // STRUCTURED_DATA is Partially or fully provided.
                else
                {
                    $this->STRUCTURED_DATA = $STRUCTURED_DATA;
                }

                // </editor-fold>

                // <editor-fold defaultstate="collapsed" desc="Setting MSG">
            
            if (empty($MSG))
            {
                $this->MSG = new SyslogMessage();
            }
            else
            {
                $this->MSG = $MSG;
            }
            
            // </editor-fold>
            }
            
            // </editor-fold>
        }
        
        // </editor-fold>
    }
?>
