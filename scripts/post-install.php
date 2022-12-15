<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "\n";
echo basename(__FILE__) . "\n";
echo "\n";

$targetFile = ai_audit_path('config/audit.php');
$sourceFile = "{$targetFile}.template";

if (!is_file($targetFile) && is_file($sourceFile)) {
    $sourceContent = file_get_contents($sourceFile);
    file_put_contents($targetFile, $sourceContent);
    echo "created file: {$targetFile}\n";
    echo "\n";
}
