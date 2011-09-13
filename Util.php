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
 * Sanitizes a tag
 * @param string $tag
 * @return string 
 */
function fte_sanitize($tag)
{
	return preg_replace('/[{}\s\t]/', '', $tag);
}

/**
 * Makes all variables distinguishable between strings.
 * @param string $tag
 * @return string
 */
function fte_preprocess_variables($tag)
{
	$matches = fte_match_all('/\$[a-zA-Z0-9_\[\]\'\"]*/', $tag);
	foreach($matches as $match)
		$tag = str_replace($match, '('.$match.')', $tag);
	return $tag;
}

/**
 * Replaces all variables withing a tag.
 * @param string $tag
 * @param FluxTE $template
 * @return string 
 */
function fte_process_variables($tag, $variables)
{
	$matches = fte_match_all('/\(\$.*?\)/', $tag);
	foreach($matches as $match)
	{
		$match = substr(trim($match, '()'), 1);
		$match2 = fte_match('/\[.*?\]/', $match);
		if($match2 != '')
		{
			$match2 = trim($match2, '[]\'"');
			$match3 = preg_replace('/\[.*?\]/', '', $match);
			if(!is_array($variables[$match3]) || !isset($variables[$match3][$match2]))
				throw new Exception('FluxTE : Variable is not an array or points to a non-existant element!');
			$assigned = $variables[$match3][$match2];
		}
		else
			$assigned = (isset($variables[$match])) ? $variables[$match] : '';
		$tag = str_replace('($'.$match.')', (string)$assigned, $tag);
	}
	return $tag;
}

/**
 * Simplified preg_match_all()
 * @param string $pattern
 * @param string $subject
 * @return array 
 */
function fte_match_all($pattern, $subject)
{
	$temp = array();
	preg_match_all($pattern, $subject, $matches, PREG_OFFSET_CAPTURE);
	if(!empty($matches))
		foreach($matches as $match)
			$temp[] = $match[0][0];
	return $temp;
}

/**
 * Simplified preg_match()
 * @param string $pattern
 * @param string $subject
 * @return string 
 */
function fte_match($pattern, $subject)
{
	preg_match($pattern, $subject, $match, PREG_OFFSET_CAPTURE);
	return (isset($match[0][0])) ? $match[0][0] : '';
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

// Register autoload.
spl_autoload_register('fte_autoload');

?>
