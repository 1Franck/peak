<?php
/**
 * Test Helper
 */
require_once dirname(__FILE__).'/../../TestHelper.php';

/**
 * Component(s)
 * @see Peak_Controller_Action, Peak_Config, Peak_Registry, Peak_Core, Peak_Router, Peak_View, Peak_Exception, Peak_Controller_Exception
 */
require_once 'Peak/Controller/Action.php';
require_once 'Peak/Config.php';
require_once 'Peak/Registry.php';
require_once 'Peak/Core.php';
require_once 'Peak/Router.php';
require_once 'Peak/View.php';
require_once 'Peak/Exception.php';
require_once 'Peak/Controller/Exception.php';

/**
 * Fixture(s)
 */
require_once dirname(__FILE__).'/ActionTest/testController.php';

/**
 * @category   Peak
 * @package    Peak_Controller_Action
 * @subpackage UnitTests
 * @version    $Id$
 */
class Peak_Controller_ActionTest extends PHPUnit_Framework_TestCase
{
	
	public function setUp()
	{
		Peak_Registry::set('router', new Peak_Router());
		Peak_Registry::set('view', new Peak_View());
		$this->peakcontroller = new testController();
	}
	
	public function testControllerInstance()
    {
        $this->assertInstanceOf('Peak_Controller_Action', $this->peakcontroller);
    }
    
    public function testProperties()
    {
    	$this->assertTrue($this->peakcontroller->getTitle() === 'test');
    	$this->assertTrue($this->peakcontroller->getName() === 'testController');
    }
    
    public function testIsAction()
    {
    	$this->assertTrue($this->peakcontroller->isAction('_index'));
    	$this->assertTrue($this->peakcontroller->isAction('_contact'));
    	$this->assertFalse($this->peakcontroller->isAction('_send'));
    }
    
    public function testGetActions()
    {
    	$actions = $this->peakcontroller->getActions();
    	
    	$this->assertTrue(is_array($actions));
    	$this->assertTrue(count($actions) == 2);
    	$this->assertTrue($actions[0] === '_index');
    	$this->assertTrue($actions[1] === '_contact');
    }
    
    function testDispatch()
    {
    	//handle default action (_index)
    	$this->peakcontroller->dispatch();
    	
    	$this->assertTrue(Peak_Registry::o()->view->actiontest === 'default value');
    	$this->assertTrue(Peak_Registry::o()->view->preactiontest === 'value');
    	$this->assertTrue(Peak_Registry::o()->view->postactiontest === 'value');
    	$this->assertFalse(isset(Peak_Registry::o()->view->test));
    }
    
    function testdispatchAction2()
    {
    	//handle contact action (_contact)
    	Peak_Registry::o()->router->action = 'contact';
    	$this->peakcontroller->getRoute();
    	$this->peakcontroller->dispatch();
    	
    	$this->assertTrue(Peak_Registry::o()->view->actiontest === 'contact value');
    	$this->assertTrue(Peak_Registry::o()->view->preactiontest === 'value');
    	$this->assertTrue(Peak_Registry::o()->view->postactiontest === 'value');
    	$this->assertFalse(isset(Peak_Registry::o()->view->test));
    }
    
    /**
	 * @expectedException Peak_Controller_Exception
	 */
    function testdispatchException()
    {
    	//handle a default action that don't exists
    	Peak_Registry::o()->router->action = 'test';
    	$this->peakcontroller->getRoute();
		try {
			$this->peakcontroller->dispatch();
		}
		catch (InvalidArgumentException $expected) {
            return;
        }
 
        $this->fail('An expected exception has not been raised.');
        
        //handle action from router that don't exists
        Peak_Registry::o()->router->action = 'test2';
        $this->peakcontroller->getRoute();
		try {
			$this->peakcontroller->dispatch();
		}
		catch (InvalidArgumentException $expected) {
            return;
        }
 
        $this->fail('An expected exception has not been raised.');
    }

}