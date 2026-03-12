<?php
// Show raw bytes around line 1007
$file = dirname(__DIR__) . '/views/contabilidad.php';
$lines = file($file, FILE_IGNORE_NEW_LINES);
$total = count($lines);
echo "Total lines: $total\n\n";

// Show lines 1002 to 1015 (0-indexed so 1001 to 1014)
for ($i = 1001; $i <= min(1015, $total - 1); $i++) {
    $line = $lines[$i];
    $display = str_replace(['<?=', '?>'], ['[PHP_ECHO]', '[/PHP_ECHO]'], $line);
    echo ($i + 1) . ": " . $display . "\n";
}