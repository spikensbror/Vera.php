<?php

// FluxTE root directory.
define('FTE_DIR', dirname(__FILE__).'/');

// Node types.
define('FTE_NODE_VAR', 10); // Assigned Var
define('FTE_NODE_IF', 11); // If
define('FTE_NODE_ELSE', 12); // Else
define('FTE_NODE_STRING', 13); // String
define('FTE_NODE_INCLUDE', 14); // Include
define('FTE_NODE_EACH', 15); // Each

// Instruction types.
define('FTE_INSTR_EIF', 20); // EndIf
define('FTE_INSTR_EEACH', 21); // EndEach

// Standard parents.
define('FTE_UNASSIGNED', -2);
define('FTE_ROOT', -1);

?>
