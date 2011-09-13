<?php

// Include FluxTE.
include_once('../FluxTE.php');

// Instanciate.
$template = new FluxTemplate();
// Set template root to current directory.
$template->SetTemplateRoot(dirname(__FILE__));
// Assign some mock values.
$template->AssignValue('test', true);
$template->AssignValue('out_test', 'BUDDY');
$template->AssignValue('file_test', 'include');
$template->AssignValue
(
	'each_test',
	array
	(
		array('test' => 'Hello Bed'),
		array('test' => 'Hello World'),
		array('test' => 'Hello Friend')
	)
);

// Display.
$template->Display('template.tpl');

?>