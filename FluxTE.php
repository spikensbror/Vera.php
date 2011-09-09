<?php

/*
 * Copyright (c) 2011 Kimmy Andersson
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * Permission is granted to anyone to use this software for any purpose,
 * including commercial applications, and to alter it and redistribute it freely,
 * subject to the following restrictions:
 *
 * 1. The origin of this software must not be misrepresented;
 * you must not claim that you wrote the original software.
 * If you use this software in a product, an acknowledgment in the
 * product  documentation would be appreciated but is not required.
 *
 * 2. Altered source versions must be plainly marked as such, and must not be misrepresented as being the original software.
 *
 * 3. This notice may not be removed or altered from any source distribution.
 */

// Node types.
define('FTE_NODE_VAR', 0); // Assigned Var
define('FTE_NODE_IF', 1); // If
define('FTE_NODE_ELSE', 2); // Else
define('FTE_NODE_STRING', 3); // String
define('FTE_NODE_INCLUDE', 4); // Include

// Standard parents.
define('FTE_UNASSIGNED', -2);
define('FTE_ROOT', -1);

/**
 * Flux Template Engine
 * 
 * The FTE main class which will handle all template parsing
 * and output.
 */
class FluxTE
{
	/**
	 * Template root directory.
	 * @var string 
	 */
	private $_TemplateRoot = '';
	
	/**
	 * All currently assigned variables.
	 * @var array 
	 */
	private $_AssignedVars = array();
	
	/**
	 * Assign an array of variables.
	 * @param array $variables
	 * @return bool 
	 */
	public function Assign($variables)
	{
		if(!is_array($variables))
			return false;
		$result = true;
		foreach($variables as $key => $value)
			$result = $this->AssignValue($key, $value);
		return $result;
	}
	
	/**
	 * Assigns a key-value pair.
	 * @param string $key
	 * @param object $value
	 * @return bool 
	 */
	public function AssignValue($key, $value)
	{
		return ($this->_AssignedVars[$key] = $value) ? true : false;
	}
	
	/**
	 * Gets an assigned variable.
	 * @param string $key
	 * @return object 
	 */
	public function GetVar($key)
	{
		return (isset($this->_AssignedVars[$key])) ? $this->_AssignedVars[$key] : false;
	}
	
	/**
	 * Gets all assigned variables.
	 * @return array 
	 */
	public function GetVars()
	{
		return $this->_AssignedVars;
	}
	
	/**
	 * Gets the output of specified file.
	 * @param string $file
	 * @return string 
	 */
	public function GetOutput($file)
	{
		$path = $this->_TemplateRoot.trim($file, '/\\');
		if(!is_file($path))
			throw new Exception('FluxTE : File not found!');
		$tpl = file_get_contents($path);
		$output = $this->_Output($tpl);
		$output = preg_replace('/[\r\n]+[\t\s]+[\r\n]/', "\r\n", $output);
		return $output;
	}
	
	/**
	 * Gets the output, formats and finally outputs it.
	 * @param string $file 
	 */
	public function Display($file)
	{
		echo($this->GetOutput($file));
	}
	
	/**
	 * Parses all nodes present in the template and formats the whole template.
	 * @param string $tpl
	 * @return string 
	 */
	private function _Output($tpl)
	{
		$chunks = preg_split('/({.*?})/', $tpl, -1, PREG_SPLIT_DELIM_CAPTURE);
		$nodes = array();
		$parent_stack = array(FTE_ROOT);
		if(is_array($chunks))
		{
			$i = 0;
			for($j = 0; $j < sizeof($chunks); $j++)
			{
				$chunk = $chunks[$j];
				if($chunk == '')
					continue;
				$pragma = substr($chunk, 0, 3);
				$tag = trim($chunk, '{}');
				switch($pragma)
				{
					case '{if': // If
					{
						$nodes[$i] = new FluxTENode(FTE_NODE_IF, $this, $tag);
						if($this->_Last($parent_stack) != FTE_ROOT)
							$nodes[$this->_Last($parent_stack)]->AddChild($nodes[$i]);
						$nodes[$i]->SetParent($this->_Last($parent_stack));
						array_push($parent_stack, $i);
						$i++;
						break;
					}
					
					case '{el': // Else
					{
						$nodes[$i] = new FluxTENode(FTE_NODE_ELSE, $this, $tag);
						$nodes[$i]->SetParent(FTE_UNASSIGNED);
						$nodes[$this->_Last($parent_stack)]->SetElse($nodes[$i]);
						array_pop($parent_stack);
						array_push($parent_stack, $i);
						$i++;
						break;
					}
					
					case '{/i': // End
					{
						array_pop($parent_stack);
						break;
					}
					
					case '{($': // Var
					{
						$nodes[$i] = new FluxTENode(FTE_NODE_VAR, $this, $tag);
						if($this->_Last($parent_stack) != FTE_ROOT)
							$nodes[$this->_Last($parent_stack)]->AddChild($nodes[$i]);
						$nodes[$i]->SetParent($this->_Last($parent_stack));
						$i++;
						break;
					}
					
					case '{in': // Include
					{
						$nodes[$i] = new FluxTENode(FTE_NODE_INCLUDE, $this, $tag);
						if($this->_Last($parent_stack) != FTE_ROOT)
							$nodes[$this->_Last($parent_stack)]->AddChild($nodes[$i]);
						$nodes[$i]->SetParent($this->_Last($parent_stack));
						$i++;
						break;
					}
					
					default: // Unknown
					{
						$nodes[$i] = new FluxTENode(FTE_NODE_STRING, $this, $chunk);
						if($this->_Last($parent_stack) != FTE_ROOT)
							$nodes[$this->_Last($parent_stack)]->AddChild($nodes[$i]);
						$nodes[$i]->SetParent($this->_Last($parent_stack));
						$i++;
						break;
					}
				}
			}
		}
		
		$output = '';
		foreach($nodes as $node)
			if($node->GetParent() == FTE_ROOT)
				$output .= $node->GetOutput();
		return $output;
	}
	
	/**
	 * Returns the last item of an array.
	 * @param array $array
	 * @return object 
	 */
	private function _Last($array)
	{
		return $array[sizeof($array) - 1];
	}
	
	/**
	 * Sets the template root directory.
	 * @param string $path
	 * @return bool 
	 */
	public function SetTemplateRoot($path)
	{
		$path = trim($path, '/\\').'/';
		return (is_dir($path) && ($this->_TemplateRoot = $path));
	}
	
	/**
	 * Gets the template root directory.
	 * @return string 
	 */
	public function GetTemplateRoot()
	{
		return $this->_TemplateRoot;
	}
}

/**
 * FluxTE Node Object
 * 
 * Handles node ouput and input.
 */
class FluxTENode
{
	/**
	 * Type of the node.
	 * @var int 
	 */
	private $_Type = null;
	
	/**
	 * A reference to the parent template engine.
	 * @var FluxTE 
	 */
	private $_Template = null;
	
	/**
	 * Contents of the node.
	 * @var string 
	 */
	private $_Data = null;
	
	/**
	 * Child nodes of the node.
	 * @var array 
	 */
	private $_ChildNodes = array();
	
	/**
	 * Parent id of the node.
	 * @var int 
	 */
	private $_Parent = FTE_ROOT;
	
	/**
	 * Else node for if statements.
	 * @var FluxTENode 
	 */
	private $_Else = null;
	
	/**
	 * Construct.
	 */
	function __construct($type, &$template, $data)
	{
		$this->_Type = $type;
		$this->_Template =& $template;
		$this->_Data = $data;
	}
	
	/**
	 * Parses and returns node output.
	 * @return string 
	 */
	public function GetOutput()
	{
		$output = '';
		$parse_child = false;
		$tag = '';
		if($this->_Type != FTE_NODE_STRING)
		{
			$tag = $this->_Data;
			$tag = $this->_ProcessInternalVars($tag);
			$tag = $this->_SanitizeTag($tag);
		}
		
		switch($this->_Type)
		{
			case FTE_NODE_VAR:
			{
				$output = trim($tag, '{()}');
				break;
			}
			
			case FTE_NODE_IF:
			{
				$tag = substr($tag, strpos($tag, '(')+1, strpos($tag, ')')-strpos($tag, '(')-1);
				if($tag != '')
					$parse_child = true;
				else
					$output = ($this->_Else != null) ? $this->_Else->GetOutput() : '';
				break;
			}
			
			case FTE_NODE_ELSE:
			{
				$parse_child = true;
				break;
			}
			
			case FTE_NODE_STRING:
			{
				$output = $this->_Data;
				break;
			}
			
			case FTE_NODE_INCLUDE:
			{
				$tag = substr($tag, strpos($tag, '(')+1, strpos($tag, ')')-strpos($tag, '(')-1);
				if($tag == '')
					break;
				$fte = new FluxTE();
				$fte->SetTemplateRoot($this->_Template->GetTemplateRoot());
				$fte->Assign($this->_Template->GetVars());
				$output = $fte->GetOutput($tag);
				break;
			}
		}
		if($parse_child)
		{
			foreach($this->_ChildNodes as $node)
				$output .= $node->GetOutput();
		}
		return $output;
	}
	
	/**
	 * Sanitizes a tag.
	 * @param string $tag
	 * @return string 
	 */
	private function _SanitizeTag($tag)
	{
		return preg_replace('/[\s\t]/', '', $tag);
	}
	
	/**
	 * Processes and replaces all internal variables in tag.
	 * @param string $tag
	 * @return string 
	 */
	private function _ProcessInternalVars($tag)
	{
		$vars = array();
		preg_match_all('/[\$][a-zA-Z0-9_]*/', $tag, $vars, PREG_OFFSET_CAPTURE);
		foreach($vars as $var)
		{
			if(is_array($var) && !empty($var))
			{
				$tag = str_replace($var[0][0], ($this->_Template->GetVar(substr($var[0][0], 1))) ? $this->_Template->GetVar(substr($var[0][0], 1)) : '' , $tag);
			}
		}
		return $tag;
	}
	
	/**
	 * Sets the parent id.
	 * @param int $parent 
	 */
	public function SetParent($parent)
	{
		$this->_Parent = $parent;
	}
	
	/**
	 * Gets the parent id.
	 * @return int 
	 */
	public function GetParent()
	{
		return $this->_Parent;
	}
	
	/**
	 * Sets the else node for if nodes.
	 * @param FluxTENode $node 
	 */
	public function SetElse(&$node)
	{
		$this->_Else =& $node;
	}
	
	/**
	 * Adds a child node to the node.
	 * @param FluxTENode $node 
	 */
	public function AddChild(&$node)
	{
		$this->_ChildNodes[] =& $node;
	}
}

?>
