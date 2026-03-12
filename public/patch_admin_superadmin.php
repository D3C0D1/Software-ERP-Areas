<?php
$dirs = [
    __DIR__ . '/../app/controllers',
    __DIR__ . '/../app/middlewares',
    __DIR__ . '/../routes',
    __DIR__ . '/../views',
    __DIR__ . '/../views/components',
];

$replacements = [
    '$_SESSION[\'role\'] !== \'Admin\'' => '!in_array($_SESSION[\'role\'] ?? \'\', [\'Admin\', \'SuperAdmin\'])',
    '$_SESSION[\'role\'] != \'Admin\'' => '!in_array($_SESSION[\'role\'] ?? \'\', [\'Admin\', \'SuperAdmin\'])',
    '$_SESSION[\'role\'] === \'Admin\'' => 'in_array($_SESSION[\'role\'] ?? \'\', [\'Admin\', \'SuperAdmin\'])',
    '$_SESSION[\'role\'] == \'Admin\'' => 'in_array($_SESSION[\'role\'] ?? \'\', [\'Admin\', \'SuperAdmin\'])',
    '[\'Admin\']' => '[\'Admin\', \'SuperAdmin\']',
    '$user[\'rol_nombre\'] === \'Admin\'' => 'in_array($user[\'rol_nombre\'], [\'Admin\', \'SuperAdmin\'])',
    '$role !== \'Admin\'' => '!in_array($role, [\'Admin\', \'SuperAdmin\'])',
    '$role === \'Admin\'' => 'in_array($role, [\'Admin\', \'SuperAdmin\'])',
    'USER_ROLE === \'Admin\'' => '(USER_ROLE === \'Admin\' || USER_ROLE === \'SuperAdmin\')',
    'in_array($_SESSION[\'role\'], [\'Admin\', \'Gerente\'])' => 'in_array($_SESSION[\'role\'], [\'Admin\', \'SuperAdmin\', \'Gerente\'])',
    'in_array($role, [\'Admin\', \'Gerente\'])' => 'in_array($role, [\'Admin\', \'SuperAdmin\', \'Gerente\'])',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir))
        continue;
    $files = scandir($dir);
    foreach ($files as $file) {
        if (substr($file, -4) === '.php') {
            $path = $dir . '/' . $file;
            $content = file_get_contents($path);
            $newContent = str_replace(array_keys($replacements), array_values($replacements), $content);
            if ($newContent !== $content) {
                file_put_contents($path, $newContent);
                echo "Updated $file\n";
            }
        }
    }
}
echo "Done replacing Admin checks\n";