<?php // phpcs:ignore PSR1.Files.SideEffects

// test read .env file

require '../src/read_env_file.php';

print "BASE: " . __DIR__ . "<br>";
print "ORIG: <pre>" . file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '.env') . "</pre>";

$status = readEnvFile(__DIR__);

print "STATUS: " . ($status ? 'OK' : 'FAIL') . "<br>";
print "ENV: <pre>" . print_r($_ENV, true) . "</pre><br>";

// __END__
