<?php
/**
 * Peak Autodoc controller for the framework using Reflection
 * 
 * @uses    Peak_Zreflection_Class and Zend_Reflection components
 * @author  Francois Lajoie
 * @version $Id$
 */
class Peak_Controller_Internal_Pkdoc extends Peak_Controller_Action
{
    /**
	 * Peak_Zreflection instance
	 * @var object
	 */
    private $ref;
    
	/**
	 * Setup controller
	 */
    public function preAction()
    {
        $this->view->engine('VirtualLayouts');
        $this->view->cache()->disable();
		
		$this->getFrameworkClasses();
		$this->layout();
    }
	
	/**
	 * Show debugbar. For DEV purpose only
	 */
	public function postRender()
	{
		$this->view->debugbar()->show();
	}
    
	/**
	 * Intro action
	 */
    public function _index()
    {
        $this->layout();
		$this->defaultContent();
    }
	
	/**
	 * Class sheet
	 */
	public function _c()
	{
		if(isset($this->params[0]) && in_array($this->params[0], $this->view->classes)) {
			
			$this->ref = new Peak_Zreflection_Class();

			$this->ref->loadClass($this->params[0]);
			$this->view->class = $this->ref;
			
			$this->classContent();
		}
		else $this->redirectAction('index');
	}
	
	/**
	 * Get framework classes
	 */
	private function getFrameworkClasses()
	{
		$result = array();
		
		$peak_path = str_replace('\\','/',LIBRARY_ABSPATH.DIRECTORY_SEPARATOR.'Peak');
		
		$ignored_paths = array($peak_path.'/vendors');
		$ignored_files = array($peak_path.'/autoload.php');
		
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($peak_path));
		
		foreach($files as $file) {
			
			$skip = false;
			$path = str_replace('\\','/',$file->getPath());

			//skip non php files
			if(pathinfo($file->getFileName(), PATHINFO_EXTENSION) !== 'php') continue;
			if(in_array($file->getPath().'/'.$file->getFileName(), $ignored_files)) continue;
			
			//skip files from $ignored_paths
			foreach($ignored_paths as $ip) {
				if(stristr($path, $ip) !== false) {
					$skip = true;
					break;
				}
			}
			
			if($skip === true) {
				$skip = false;
				continue;
			}
			
			$class_prefix = 'Peak'.str_replace(array($peak_path,'/'), array('','_'), $path).'_';
			
			$result[] = $class_prefix.str_replace('.php','',$file->getFileName());
		}
		sort($result);
		$this->view->classes = $result;

	}
	
	/**
	 * Controller View Layout
	 */
	private function layout()
	{
		$twitter_bs = file_get_contents(LIBRARY_ABSPATH.'/Peak/Vendors/TwitterBootstrap/1.4.0/bootstrap.min.css');
		$layout = '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Peak Framework Doc</title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le styles -->

	<link href="http://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css" />
    <style type="text/css">
	  /* TWITTER CSS */
	  '.$twitter_bs.'
	  /* --------------------- */
      body {
        /*padding-top: 60px;*/
		font-family: "Open Sans", sans-serif;
      }
      .topbar {
        position:inherit;
        margin-bottom:30px;
      }
	  pre {
		line-height:18px !important;
	  }
      footer {
       text-align:center;
      }
	  .sidebar li a {
		/*font-weight:bold;*/
	  }
	  .hero-unit {
		padding:27px 40px;
	  }
	  .hero-unit h1 {
		font-size:36px;
	  }
	  .hero-unit table td {
		background-color:#f9f9f9;
		padding:6px;
	  }
	  .coda {

	  }
	  .nounder:hover {
		text-decoration:none;
	  }
	  .label {
	    vertical-align:text-top;
	  }
	  .label.parent {
	   background-color:#8604FB;
	   margin-left:-5px;
	   vertical-align:top;
	  }
	  .row .span5, .block {
		background:#f5f5f5;
		padding:15px;
		border-radius:6px;
		margin-bottom:15px;
	  }
	  .span16 h1 {
		color:#ccc;
	  }
      .clear {
       clear:both;
       height:1px;
      }
      .clear_right {
       clear:right;
       height:1px;
      }
	  
    </style>
  </head>

  <body>

    <div class="topbar">
      <div class="topbar-inner">
        <div class="container-fluid">
          <a class="brand" href="'.$this->view->baseUrl('pkdoc',true).'">Peak Framework AutoDoc</a>
		  <span style="color:#888;margin-left:-12px;padding-top:16px !important;">beta</span>
        </div>
      </div>
    </div>

    <div class="container-fluid">
      <div class="sidebar" style="width:245px;">
        <div class="well">
		   <h5>Framework Classes ('.count($this->view->classes).')</h5>
		   <ul class="unstyled">';
		
		foreach($this->view->classes as $c) {
			$layout .= '<li><a href="'.$this->view->baseUrl('pkdoc/c/'.$c,true).'">'.$c.'</a></li>';
		}
		
		$layout .= '
           </ul>
		</div>
      </div>
	  
      <div class="content" style="margin-left:260px;">
        		
		{CONTENT}
        
        <div class="clear"></div>
        <footer>
          <p>Peak Framework '.PK_VERSION.'</p>
        </footer>
      </div>
    </div>

  </body>
</html>';
		
		$this->view->setLayout($layout);
	}
	
	/**
	 * Intro View Content
	 */
	private function defaultContent()
	{
		$content = '
        <div class="hero-unit">
          <h1>Welcome to framework inspector</h1>
          <p>This tool use PHP Reflection to generate documentation about Peak Framework classes.</p>
        </div>';
		
		$this->view->setContent($content);
	}
	
	/**
	 * Class sheet View Content
	 */
	private function classContent()
	{
		$content = '
		<div class="hero-unit">
          <h1>'.$this->ref->class_name.'</h1><p>';
		  
		foreach($this->ref->class_declaration['properties'] as $d) {
			if($d === 'abstract') $class = 'notice';
			elseif($d === 'final') $class = 'important';
			elseif($d === 'class') $class = 'success';
			else $class = '';
			$content .= '<span class="label '.$class.'">'.$d.'</span> ';
		}
				
		if(isset($this->ref->class_declaration['parent'])) {
			$parent = $this->ref->class_declaration['parent'];
			if(stristr($parent,'Peak_')) {
				$content .= '<a class="nounder" href="'.$this->view->baseUrl('pkdoc/c/'.$parent,true).'">';
			}
			$content .= '<span class="label success"><span class="label parent">Parent</span> '.$parent.'</span>';
			if(stristr($parent,'Peak_')) {
				$content .= '</a>';
			}
		}
		
		$content .= '</p>';
		
		if(!empty($this->ref->class_declaration['interfaces'])) {
			$content .= '<p style="margin-top:-12px;">';
			foreach($this->ref->class_declaration['interfaces'] as $i) {
				$content .= '<span class="label warning">'.$i.'</span> ';
			}
			$content .= '</p>';
		}
		
        $content .= '
		  <p>'.$this->ref->class_doc_short.'</p>
		  <p><span style="font-size:12px;line-height:18px;">'.nl2br($this->ref->class_doc_long).'</span></p>
		  <table class="coda">';
		  
	    foreach($this->ref->class_doc_tags as $tag) {
			$content .= '<tr>
			              <td style="width:1px;"><i>'.$tag['name'].'</i></td>
						  <td>'.$tag['description'].'</td>
						</tr>';
	    }
		
		// end of hero-unit. start methods and properties lists
		$content .= '
		</table>
        </div><!-- /hero-unit -->
		
		<!-- CLASS ELEMENTS LIST -->
        <div class="row" style="margin:0 20px;width:1080px;">
          <div class="span5">
            <h2>Methods ('.count($this->ref->self_methods).')</h2><p>';
		
		foreach($this->ref->self_methods as $m) {
			$content .= $m['visibility'].' <a href="#'.$m['name'].'()"><strong>'.$m['name'].'()</strong></a><br />';
		}
		
        $content .=
		   '</p>
          </div>
          <div class="span5">
            <h2>Properties ('.count($this->ref->self_properties).')</h2><p>';
		
		
		foreach($this->ref->self_properties as $p) {
			$content .= $p['visibility'].' <a href="#$'.$p['name'].'"><strong>$'.$p['name'].'</strong></a><br />';
		}
			
        $content .= '</div>';
		
		// constants list
		if(!empty($this->ref->constants)) {
		
			$content .= '
			  <div class="span5">
	
				<h2>Constants ('.count($this->ref->constants).')</h2>';
				
			foreach($this->ref->constants as $v) {
				$content .= '<a href="#'.$v['name'].'"><strong>'.$v['name'].'</strong></a><br />';
			}
			$content .= '
			  </div>';
		}
            
		// spacer line
        $content .= '<div class="span16" style="margin:12px 0;height:10px;">&nbsp;</div>';
		
		// need to be fixed in zreflection to return an array
		if(!empty($this->ref->constants)) {
			$content .= '<div class="span16" style="margin:12px 0;"><h1>Constants</h1></div>';
			$content .= '<div class="span16">';
			foreach($this->ref->constants as $v) {
				$content .= '<h2 id="'.$v['name'].'">'.$v['name'].'</h2>';
				$content .= '<div class="block">value &rarr; '.htmlentities($v['value']).'</div>';
			}
			$content .= '</div>';
		}
		
		//properties details 
		if(!empty($this->ref->self_properties)) {
			$content .= '<div class="span16" style="margin:12px 0;"><h1>Properties</h1></div>';
			$content .= '<div class="span16">';
			foreach($this->ref->self_properties as $v) {
				$content .= '<h2 id="$'.$v['name'].'">$'.$v['name'].'
				                 '.$this->_visibility_label($v['visibility']).'
								 '.(($v['static'] === true) ? $this->_visibility_label('static') : '').'</h2>';
				$content .= '<div class="block"><h5>'.$v['doc']['short'].'</h5>';
				foreach($v['doc']['tags'] as $t) {
					$content .= '@'.$t['name'].' '.htmlentities($t['description']).'<br />';
				}
				$content .= '</div>';
			}
			$content .= '</div>';
		}
		
		// spacer line
        //$content .= '<div class="span16" style="margin:12px 0;">&nbsp;</div>';
		
		//methods details 
		if(!empty($this->ref->self_methods)) {
			$content .= '<div class="span16" style="margin:12px 0;"><h1>Methods</h1></div>';
			$content .= '<div class="span16">';
			foreach($this->ref->self_methods as $v) {
				$content .= '<h2 id="'.$v['name'].'()">'.$v['name'].'( <small style="color:#777;">'.$v['params_string'].'</small> )
				                 '.$this->_visibility_label($v['visibility']).'
								 '.(($v['static'] === true) ? $this->_visibility_label('static') : '').'
								 </h2>';
				$content .= '<div class="block"><h5>'.htmlentities($v['doc']['short']).'</h5><h5>'.htmlentities($v['doc']['long']).'</h5><br />';
				foreach($v['doc']['tags'] as $t) {
					$content .= '@'.$t['name'].' '.$t['type'].' '.$t['variable'].' '.$t['description'].'<br />';
				}
				$content .= '</div>';
			}
			$content .= '</div>';
		}
		
		$content .= '
		</div><!-- /row -->';
		
		$this->view->setContent($content);
	}
	
	/**
	 *
	 */
	private function _visibility_label($v)
	{
		if($v === 'public') $class = 'success';
		elseif($v === 'protected') $class = 'warning';
		elseif($v === 'private') $class = 'important';
		elseif($v === 'static') $class = 'notice';
		else $class = '';
		
		return '<span class="label '.$class.'">'.$v.'</span>';
	}
	
    
}