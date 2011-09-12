<?php

/**
 * FluxTE Node Object
 * 
 * Handles node ouput and input.
 */
class FluxNode
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
	 * @var FluxNode 
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
		if($this->_Type != FTE_NODE_STRING)
		{
			$tag = $this->_Data;
			$tag = fte_process_variables($tag, $this->_Template);
			$tag = fte_sanitize($tag);
		}
		
		switch($this->_Type)
		{
			case FTE_NODE_VAR:
			{
				$output = $tag;
				break;
			}
			
			case FTE_NODE_IF:
			{
				$tag = trim(fte_match('/\((.*?)\)/', $tag), '()');
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
				$tag = trim(fte_match('/\((.*?)\)/', $tag), '()');
				if($tag == '')
					break;
				$output = $this->_Template->GetOutput($tag);
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
	 * Gets the node type.
	 * @return int 
	 */
	public function GetType()
	{
		return $this->_Type;
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
	 * @param FluxNode $node 
	 */
	public function SetElse(&$node)
	{
		$this->_Else =& $node;
	}
	
	/**
	 * Adds a child node to the node.
	 * @param FluxNode $node 
	 */
	public function AddChild(&$node)
	{
		$this->_ChildNodes[] =& $node;
	}
}

?>
