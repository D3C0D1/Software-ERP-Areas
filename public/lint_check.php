<?php
// Quick PHP syntax check of contabilidad.php
$output = shell_exec('php -l ' . escapeshellarg(dirname(__DIR__) . '/views/contabilidad.php') . ' 2>&1');
echo htmlspecialchars($output ?? 'No output');
echo "\n\n--- Line 1006-1009 content ---\n";
$lines = file(dirname(__DIR__) . '/views/contabilidad.php');
for ($i = 1003; $i <= 1010; $i++) {
    echo ($i + 1) . ": " . htmlspecialchars($lines[$i] ?? '(empty)') . "\n";
}