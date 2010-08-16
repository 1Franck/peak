<?php

/**
 * Peak abstract action controller
 * 
 * @author   Francois Lajoie
 * @version  $Id$
 */
abstract class Peak_Controller_Action
{
    public $name;                 //child class name
    public $title;                //controller("module") title from wyncore

    public $file;                 //view script file to render
    public $path;                 //absolute view scripts controller path
       
    public $actions = array();    //actions methods list

    protected $view;              //instance of view
    
    protected $helpers;           //controller helpers objects
    
    protected $params;            //request params array
    protected $params_assoc;      //request params associative array
    protected $action;            //action called by handleRequest()
    
        
    public function __construct()
    {   
        //initialize ctrl
        $this->initController();
    }
    
    /**
     * Try to return a helper object based the method name.
     *
     * @param  string $helper
     * @param  null   $args not used
     * @return object
     */
    public function __call($helper, $args = null)
    {
    	if((isset($this->helper()->$helper)) || ($this->helper()->exists($helper))) {
        	return $this->helper()->$helper;
        }
        elseif((defined('DEV_MODE')) && (DEV_MODE)) {
            trigger_error('DEV_MODE: Controller method '.$helper.'() doesn\'t exists');
        }
    }

    /**
     * Initialize controller $name, $title, $path, $url_path and $type
     * 
     */
    final private function initController()
    {       
        $this->view = Peak_Registry::o()->view;
                               
        $this->name = get_class($this);              
        $this->title = $this->name;      
        
        $script_folder = str_ireplace('controller', '', $this->name);
        //$this->title = $script_folder;
        $this->path = Peak_Core::getPath('theme_scripts').'/'.$script_folder;

        //retreive requests param from router and remove 'mod' request witch it's used only by router
        $this->params = Peak_Registry::o()->router->params;
        $this->params_assoc = Peak_Registry::o()->router->params_assoc;
    }
    
    /**
     * Create a list of "actions"(methods) 
     * Support methods with _ suffix(_dashboard)  and method like zend (dashboardAction)
     * 
     */
    public function listActions()
    {
        $c_methods = get_class_methods($this->name);
     
        $regexp = '/^([_]{1}[a-zA-Z]{1})/';
              
        foreach($c_methods as $method) {            
            if(preg_match($regexp,$method)) $this->actions[] = $method;
        }
    }
    
    /**
     * Check if action exists. Support zend controller action method name
     *
     * @param  string $name
     * @return bool
     */
    public function isAction($name)
    {
    	return (method_exists($this->name,$name)) ? true : false;
    }
           
    /**
     * Analyse router and lauch associated action method
     *
     * @param string $action_by_default   default method name if no request match to module actions
     */   
    public function handleAction($action_by_default = '_index')
    {
        $this->preAction();
        
        $action = Peak_Registry::o()->router->action;
        
        if((isset($action)) && ($this->isAction($action))) $this->action = $action;
        elseif((isset($action_by_default)) && ($this->isAction($action_by_default)))
        {
            $action = $action_by_default;
            $this->action = $action_by_default;
        }
        else throw new Peak_Exception('ERR_CTRL_DEFAULT_ACTION_NOT_FOUND');       

        //set action filename
        $this->file = substr($this->action,1).'.php';
        
        //call requested action
        $this->$action();    
        
        $this->postAction();
    }
    
    /**
     * Load/access to controllers helpers objects
     * 
     * @return object Peak_Controller_Helpers
     */
    public function helper()
    {
        if(!is_object($this->helpers)) $this->helpers = new Peak_Controller_Helpers();
    	return $this->helpers;
    }
    
    
    /**
     * Point to view object. Usefull for controller helpers
     *
     * @return object
     */
    public function view()
    {
    	return $this->view;
    }
    
        
    /**
     * Get current action method name
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
           
    /**
     * Call view render with controller $file and $path
     *
     * @return string
     */    
    public function render()
    {                
        $this->view->render($this->file,$this->path);     
        $this->postRender();
    }
    
    
    public function redirect($ctrl, $action, $params = null)
    {
    	if($ctrl === $this->name) {
    		$this->action = $action;
    		$this->handleAction();
    	}
    }
    
    /**
     * Action before controller requested action
     */
    public function preAction() { }
    
    /**
     * Action after controller requested action
     */
    public function postAction() { }
    
    /**
     * Action after view rendering
     */
    public function postRender() { }
    
}