<?php

// composer auto loader
$loader = require '../vendor/autoload.php';
// need to add this or it will not load here
$loader->addPsr4('gullevek\\', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src');
use gullevek\dotEnv\DotEnv;

// copy test file to .env file in env folder
$file_content = __DIR__ . DIRECTORY_SEPARATOR
    . 'phpUnitTests' . DIRECTORY_SEPARATOR
    . 'dotenv' . DIRECTORY_SEPARATOR
    . 'test.env';
// env folder
$env_file = __DIR__ . DIRECTORY_SEPARATOR
    . 'env' . DIRECTORY_SEPARATOR
    . '.env';
if (!is_file($file_content)) {
    die("Cannot read $file_content");
}
if (copy($file_content, $env_file) === false) {
    die("Cannot copy $file_content to $env_file");
}

print "BASE: " . __DIR__ . "<br>";
print "ENV: " . $env_file . "<br>";
print "ORIG: <pre>" . file_get_contents($env_file) . "</pre>";

$status = DotEnv::readEnvFile(__DIR__ . DIRECTORY_SEPARATOR . 'env');

print "STATUS: " . (string)$status . "<br>";
print "ENV: <pre>" . print_r($_ENV, true) . "</pre><br>";

$status = gullevek\dotenv\DotEnv::readEnvFile(__DIR__ . DIRECTORY_SEPARATOR . 'env');
print "STATUS B: " . (string)$status . "<br>";
print "ENV B: <pre>" . print_r($_ENV, true) . "</pre><br>";

// __END__
