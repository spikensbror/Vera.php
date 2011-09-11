<?php

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
				$tag = str_replace($var[0][0],
					($this->_Template->GetVar(substr($var[0][0], 1))) ? $this->_Template->GetVar(substr($var[0][0], 1)) : '',
					$tag);
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
