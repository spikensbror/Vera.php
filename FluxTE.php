<?php

class FluxTE extends DOMDocument
{
	private $_VarBuffer = array();
	
	public function AssignArray($vars)
	{
		if(!is_array($vars))
			return false;
		
		foreach($vars as $key => $value)
			$this->Assign($key, $value);
		
		return true;
	}
	
	public function Assign($key, $value)
	{
		$this->_VarBuffer[$key] = $value;
	}
	
	public function Display(DOMNode $child = null)
	{
		
	}
}

?>
