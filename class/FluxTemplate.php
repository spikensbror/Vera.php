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
	 * Holds all instructions and ids.
	 * @var array 
	 */
	private $_Instructions = array
	(
		'{if}' => FTE_NODE_IF,
		'{else}' => FTE_NODE_ELSE,
		'{/if}' => FTE_INSTR_EIF,
		'{include}' => FTE_NODE_INCLUDE,
		'{each}' => FTE_NODE_EACH,
		'{/each}' => FTE_INSTR_EEACH
	);
	
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
				
				$instruction_id = -1;
				if(isset($this->_Instructions[$instruction]))
					$instruction_id = $this->_Instructions[$instruction];
				else
					if(fte_match('/{\$.*?}/', $instruction) != '')
						$instruction_id = FTE_NODE_VAR;
					else
						$instruction_id = FTE_NODE_STRING;
				
				// If instruction id is of node type.
				if($instruction_id < 20)
				{
					$nodes[$i] = new FluxNode($instruction_id, $this, $chunk);
					$nodes[$i]->SetParentId(get_last_element($parent_stack));
					if(get_last_element($parent_stack) != FTE_ROOT)
						$nodes[$i]->SetParent($nodes[get_last_element($parent_stack)]);
				}
				
				switch($instruction_id)
				{
					case FTE_NODE_IF:
					case FTE_NODE_INCLUDE:
					case FTE_NODE_STRING:
					case FTE_NODE_VAR:
					case FTE_NODE_EACH:
					{
						if(get_last_element($parent_stack) != FTE_ROOT)
							$nodes[get_last_element($parent_stack)]->AddChild($nodes[$i]);
						break;
					}
				}
				
				switch($instruction_id)
				{
					case FTE_NODE_IF:
					{
						array_push($parent_stack, $i);
						break;
					}
					
					case FTE_NODE_ELSE:
					{
						if($nodes[get_last_element($parent_stack)]->GetType() != FTE_NODE_IF)
							throw new Exception('FluxTE : Else found without If!');
						$nodes[$i]->SetParentId(FTE_UNASSIGNED);
						$nodes[get_last_element($parent_stack)]->SetElse($nodes[$i]);
						array_pop($parent_stack);
						array_push($parent_stack, $i);
						break;
					}
					
					case FTE_INSTR_EIF:
					{
						if($nodes[get_last_element($parent_stack)]->GetType() != FTE_NODE_IF
							&& $nodes[get_last_element($parent_stack)]->GetType() != FTE_NODE_ELSE)
							throw new Exception('FluxTE : EndIf found without If or Else!');
						array_pop($parent_stack);
						break;
					}
					
					case FTE_NODE_EACH:
					{
						array_push($parent_stack, $i);
						break;
					}
					
					case FTE_INSTR_EEACH:
					{
						if($nodes[get_last_element($parent_stack)]->GetType() != FTE_NODE_EACH)
							throw new Exception('FluxTE : EndEach found without Each!');
						array_pop($parent_stack);
						break;
					}
				}
				
				// If instruction id is of node type.
				if($instruction_id < 20)
					$i++;
			}
		}
		
		$output = '';
		foreach($nodes as $node)
			if($node->GetParentId() == FTE_ROOT)
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
