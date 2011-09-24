<?php

// Include Vera.
include_once('../Vera.php');

// Instanciate.
$template = new VeraTemplate();

// Set template root to current directory.
$template->SetTemplateRoot(dirname(__FILE__));

// Assign some mock values.
$template->AssignValue('test', 'Hello People!');
$template->AssignValue('include_test', 'include');

$template->AssignValue
(
	'each_test',
	array
	(
		array('var' => 'Hello Bed', 'var2' => 'I am tired!'),
		array('var' => 'Hello World!'),
		array('var' => 'Hello Friend!')
	)
);

$template->AssignValue
(
	'array',
	array
	(
		0 => 'Nothing much.',
		'test' => 'What is up?'
	)
);

$template->AssignValue('html_test', '<b>Hello</b>');

// Display.
$template->Display('template.tpl');

?>