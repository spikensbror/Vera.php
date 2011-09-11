<?php

/**
 * Returns the last element of an array.
 * @param array $array
 * @return object 
 */
function get_last_element($array)
{
	return $array[sizeof($array) - 1];
}

/**
 * FluxTE autoload function.
 * @param string $class
 * @return boolean 
 */
function fte_autoload($class)
{
	$path = FTE_DIR.'class/'.$class.'.php';
	$result = is_file($path);
	if($result)
		include_once($path);
	return $result;
}
spl_autoload_register('fte_autoload');

?>
