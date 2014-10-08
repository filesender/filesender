<?php

// Require environment (fatal)
if (!defined('FILESENDER_BASE')) 
    die('Missing environment');

define('DOMPDF_TEMP_DIR', FILESENDER_BASE.'/tmp');
define('DOMPDF_DEFAULT_PAPER_SIZE', 'a4');

//define('DOMPDF_ENABLE_CSS_FLOAT', true);
//define('DOMPDF_ENABLE_JAVASCRIPT', false);

//define('DEBUGPNG', true);
//define('DEBUGKEEPTEMP', true);
//define('DEBUGCSS', true);
//define('DEBUG_LAYOUT', true);
//define('DEBUG_LAYOUT_LINES', false);
//define('DEBUG_LAYOUT_BLOCKS', false);
//define('DEBUG_LAYOUT_INLINE', false);
//define('DEBUG_LAYOUT_PADDINGBOX', false);

define('DOMPDF_LOG_OUTPUT_FILE', FILESENDER_BASE.'/log/dompdf_log.htm');

define('DOMPDF_ENABLE_HTML5PARSER', true);
