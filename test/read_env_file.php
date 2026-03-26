<?php

// composer auto loader
$loader = require '../vendor/autoload.php';
// need to add this or it will not load here
$loader->addPsr4('gullevek\\', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src');
use gullevek\dotEnv\DotEnv;
use gullevek\dotEnv\Levels\DotEnvLevel;
use gullevek\dotEnv\Exceptions;

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

print "<b>BASE</b>: " . __DIR__ . "<br>";
print "<b>ENV</b>: " . $env_file . "<br>";
print "<b>ORIG</b>: <pre>" . file_get_contents($env_file) . "</pre>";

$status = DotEnv::readEnvFile(__DIR__ . DIRECTORY_SEPARATOR . '../env');
print "<b>A STATUS</b>: "
	. $status->name . " | "
	. $status->value . " | "
	. DotEnvLevel::errorMessage($status)
	. "<br>";
try {
	$status = DotEnv::readEnvFile(__DIR__ . DIRECTORY_SEPARATOR . '../env', throw_exception: true);
} catch (Exceptions\DotEnvFileNotFoundException $e) {
	print "<b>EXCEPTION</b>: " . $e->getMessage() . "<br>";
}

$status = DotEnv::readEnvFile(__DIR__ . DIRECTORY_SEPARATOR . 'env');
print "<b>B STATUS</b>: "
	. $status->name . " | "
	. $status->value . " | "
	. DotEnvLevel::errorMessage($status)
	. "<br>";
print "<b>B ENV</b>: <pre>" . print_r($_ENV, true) . "</pre><br>";

$status = gullevek\dotEnv\DotEnv::readEnvFile(__DIR__ . DIRECTORY_SEPARATOR . 'env');
print "<b>C STATUS</b>: "
	. $status->name . " | "
	. $status->value . " | "
	. DotEnvLevel::errorMessage($status)
	. "<br>";
print "<b>C ENV</b>: <pre>" . print_r($_ENV, true) . "</pre><br>";

$_ENV = [];
$status = DotEnv::readEnvFile(__DIR__ . DIRECTORY_SEPARATOR . 'env', 'test_clean.env');
print "<b>D STATUS</b>: "
	. $status->name . " | "
	. $status->value . " | "
	. DotEnvLevel::errorMessage($status)
	. "<br>";
print "<b>D ENV</b>: <pre>" . print_r($_ENV, true) . "</pre><br>";

$status = DotEnv::readEnvFile(__DIR__ . DIRECTORY_SEPARATOR . 'env', 'test_clean.env');
print "<b>E STATUS</b>: "
	. $status->name . " | "
	. $status->value . " | "
	. DotEnvLevel::errorMessage($status)
	. "<br>";
print "<b>E ENV</b>: <pre>" . print_r($_ENV, true) . "</pre><br>";

// __END__
