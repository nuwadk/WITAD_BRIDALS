<?php
$testFile = __DIR__ . '/test.json';
$result = file_put_contents($testFile, '["test"]');
echo "Write result: " . ($result ? "SUCCESS" : "FAILED") . "<br>";
echo "File exists: " . (file_exists($testFile) ? "YES" : "NO") . "<br>";
echo "Directory: " . __DIR__ . "<br>";
echo "Writable: " . (is_writable(__DIR__) ? "YES" : "NO");
?>