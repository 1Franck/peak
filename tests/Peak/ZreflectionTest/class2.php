<?php
/**
 * Example Class2
 *
 * Long description text...
 *
 * @author  BarFoo
 * @version 2.5
 */
class class2 extends class1
{
    /**
     * Name
     * @var string
     */ 
    protected $_name;
    
    /**
     * Nick name
     * @var string
     */
    public $nickname = 'Unknown';
    
    /**
     * Number of messages
     * @var integer
     */
    public static $nb_messages = 0;
    
    /**
     * Set name
     * 
     * Long description text...
     *
     * @param string $name
     */ 
    public function setName($name)
    {
        $this->_name = $name;
    }
    
    /**
     * Get name
     *
     * @return string
     */ 
    public function getName()
    {
        return $this->_sanitizeName();
    }
    
    /**
     * Get name with a prefix
     *
     * @param  string $prefix Set a prefix string to name
     * @return string
     */
    public function getNameWithPrefix($prefix = 'Mr')
    {
        return $prefix.' '.$this->_name;
    }
    
    /**
     * Sanitize name
     *
     * @return string
     */
    protected function _sanitizeName($name)
    {
        return strip_tags($name);
    }
    
    
}