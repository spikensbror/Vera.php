<?php

/**
 * Flux Template
 * 
 * The main class which will handle all template parsing
 * and output.
 */
class FluxTemplate
{
	/**
	 * Template root directory.
	 * @var string 
	 */
	private $_TemplateRoot = FTE_DIR;
	
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
				$instruction = preg_replace('/\((.*?)\)/', '', $chunk);
				$instruction = preg_replace('/[\s\t]/', '', $instruction);
				switch($instruction)
				{
					case '{if}': // If
					{
						$nodes[$i] = new FluxNode(FTE_NODE_IF, $this, $chunk);
						if(get_last_element($parent_stack) != FTE_ROOT)
							$nodes[get_last_element($parent_stack)]->AddChild($nodes[$i]);
						$nodes[$i]->SetParent(get_last_element($parent_stack));
						array_push($parent_stack, $i);
						$i++;
						break;
					}
					
					case '{else}': // Else
					{
						$nodes[$i] = new FluxNode(FTE_NODE_ELSE, $this, $chunk);
						$nodes[$i]->SetParent(FTE_UNASSIGNED);
						$nodes[get_last_element($parent_stack)]->SetElse($nodes[$i]);
						array_pop($parent_stack);
						array_push($parent_stack, $i);
						$i++;
						break;
					}
					
					case '{/if}': // End
					{
						array_pop($parent_stack);
						break;
					}
					
					case '{include}': // Include
					{
						$nodes[$i] = new FluxNode(FTE_NODE_INCLUDE, $this, $chunk);
						if(get_last_element($parent_stack) != FTE_ROOT)
							$nodes[get_last_element($parent_stack)]->AddChild($nodes[$i]);
						$nodes[$i]->SetParent(get_last_element($parent_stack));
						$i++;
						break;
					}
					
					default: // Unknown
					{
						if(substr($instruction, 0, 2) == '{$')
							$nodes[$i] = new FluxNode(FTE_NODE_VAR, $this, $chunk);
						else
							$nodes[$i] = new FluxNode(FTE_NODE_STRING, $this, $chunk);
						if(get_last_element($parent_stack) != FTE_ROOT)
							$nodes[get_last_element($parent_stack)]->AddChild($nodes[$i]);
						$nodes[$i]->SetParent(get_last_element($parent_stack));
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

?>
