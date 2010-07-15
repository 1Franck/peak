<?php
include(realpath('./classes/initPeakMVC.php'));
$file_to_test = realpath('./../library/Peak/Controller.php');
include_once($file_to_test);
echo 'Tested file: '.$file_to_test.'<br />';

class TestOfController extends UnitTestCase
{
    public $ctrl;
    
    function peak($parent = false)
    {
    	if(!$parent) return realpath('./../library/Peak');
    	else return realpath('./../library');
    }
    
    function testOfInit()
    {  	
    	
    	$this->ctrl = new testController();
    	$this->assertTrue(is_a($this->ctrl,'Peak_Controller') ,'$ctrl is not an object of Peak_Controller');
    }
    
    function testOfVar()
    {
    	$this->assertTrue(($this->ctrl->name === 'testController') ,'$name is not testController');
    	$this->assertTrue(($this->ctrl->title === 'testController') ,'$title is not testController');
    	$this->assertNull($this->ctrl->file,'$file should be null');
    	
    	$this->assertTrue(is_array($this->ctrl->actions) ,'$c_actions is not an array');
    	$this->assertTrue(empty($this->ctrl->actions) ,'$c_actions is not empty');
    	
    	$this->ctrl->listActions();
    	$this->assertTrue((count($this->ctrl->actions) == 2  ) ,'count $actions should be 2');
    	//print_r($this->ctrl->actions);
    }
    
    
   
}

class testController extends Peak_Controller 
{ 
	
	public function index()	{ }
	
	public function indexAction() { }
	
	public function contactAction()	{ }

}