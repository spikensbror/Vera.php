<?php

// FluxTE root directory.
define('FTE_DIR', dirname(__FILE__).'/');

// Node types.
define('FTE_NODE_VAR', 0); // Assigned Var
define('FTE_NODE_IF', 1); // If
define('FTE_NODE_ELSE', 2); // Else
define('FTE_NODE_STRING', 3); // String
define('FTE_NODE_INCLUDE', 4); // Include

// Standard parents.
define('FTE_UNASSIGNED', -2);
define('FTE_ROOT', -1);

?>
