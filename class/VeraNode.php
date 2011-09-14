<?php

/**
 * Vera Node Object
 * 
 * Handles node ouput and input.
 */
class VeraNode
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
	private $_ParentId = VERA_ROOT;
	
	/**
	 * Parent node of the node.
	 * @var VeraNode 
	 */
	private $_Parent = null;
	
	/**
	 * Else node for if statements.
	 * @var VeraNode 
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
	public function GetOutput($variables = null)
	{
		if($variables == null)
			$variables = $this->_Template->GetVars();
		
		$output = '';
		$parse_child = false;
		if($this->_Type != VERA_NODE_STRING)
		{
			$tag = $this->_Data;
			if($this->_Type != VERA_NODE_EACH)
				$tag = vera_process_variables($tag, $variables);
		}
		
		switch($this->_Type)
		{
			case VERA_NODE_VAR:
			{
				$output = $tag;
				break;
			}
			
			case VERA_NODE_IF:
			{
				$tag = trim(vera_match('/\((.*?)\)/', $tag), '()');
				if($tag != '')
					$parse_child = true;
				else
					$output = ($this->_Else != null) ? $this->_Else->GetOutput() : '';
				break;
			}
			
			case VERA_NODE_ELSE:
			{
				$parse_child = true;
				break;
			}
			
			case VERA_NODE_STRING:
			{
				$output = $this->_Data;
				break;
			}
			
			case VERA_NODE_INCLUDE:
			{
				$tag = trim(vera_match('/\((.*?)\)/', $tag), '()');
				if($tag == '')
					break;
				$output = $this->_Template->GetOutput($tag);
				break;
			}
			
			case VERA_NODE_EACH:
			{
				$tag = trim(vera_match('/\((.*?)\)/', $tag), '()');
				$tag = substr($tag, 1);
				$each_array = $this->_Template->GetVar($tag);
				
				if(!is_array($each_array) || empty($each_array) || !is_array($each_array[0]))
					break;
				
				foreach($each_array as $array)
				{
					$variables = $array;
					foreach($this->_ChildNodes as $node)
						$output .= $node->GetOutput($variables);
				}
				break;
			}
		}
		if($parse_child)
		{
			foreach($this->_ChildNodes as $node)
				$output .= $node->GetOutput($variables);
		}
		return $output;
	}
	
	/**
	 * Gets the node type.
	 * @return int 
	 */
	public function GetType()
	{
		return $this->_Type;
	}
	
	/**
	 * Adds a child node to the node.
	 * @param VeraNode $node 
	 */
	public function AddChild(&$node)
	{
		$this->_ChildNodes[] =& $node;
	}
	
	/**
	 * Sets the parent id.
	 * @param int $parent 
	 */
	public function SetParentId($parent)
	{
		$this->_ParentId = $parent;
	}
	
	/**
	 * Gets the parent id.
	 * @return int 
	 */
	public function GetParentId()
	{
		return $this->_ParentId;
	}
	
	/**
	 * Sets the nodes parent node.
	 * @param VeraNode $node 
	 */
	public function SetParent(&$node)
	{
		$this->_Parent =& $node;
	}
	
	/**
	 * Sets the else node for if nodes.
	 * @param VeraNode $node 
	 */
	public function SetElse(&$node)
	{
		$this->_Else =& $node;
	}
}

?>
