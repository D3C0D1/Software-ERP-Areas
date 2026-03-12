<?php
// Force PHP opcode cache clear
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully\n";
}
else {
    echo "OPcache not active\n";
}

if (function_exists('apc_clear_cache')) {
    apc_clear_cache('opcode');
    echo "APC cache cleared\n";
}

echo "Done - try reloading contabilidad now\n";
echo "File at: " . realpath(dirname(__DIR__) . '/views/contabilidad.php') . "\n";
echo "File size: " . filesize(dirname(__DIR__) . '/views/contabilidad.php') . " bytes\n";