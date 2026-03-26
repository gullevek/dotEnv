<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2026/3/25
 * DESCRIPTION:
 * ultra simple .env reader
*/

declare(strict_types=1);

namespace gullevek\dotEnv;

use gullevek\dotEnv\Levels\DotEnvLevel;
use gullevek\dotEnv\Exceptions;

class DotEnv
{
	/** @var string constant comment char, set to # */
	public const COMMENT_CHAR = '#';

	/**
	 * parses .env file
	 *
	 * Rules for .env file
	 * variable is any alphanumeric string followed by = on the same line
	 * content starts with the first non space part
	 * strings can be contained in "
	 * strings MUST be contained in " if they are multiline
	 * if string starts with " it will match until another " is found
	 * anything AFTER " is ignored
	 * if there are two variables with the same name only the first is used
	 * variables are case sensitive
	 *
	 * [] Grouping Block Name as prefix until next or end if set,
	 * space replaced by _, all other var rules apply
	 *
	 * @param  string $path     Folder to file, default is __DIR__
	 * @param  string $env_file What file to load, default is .env
	 * @param  bool   $throw_exception [default=false] Whether to throw exceptions or not
	 * @return DotEnvLevel      OTHER_ERROR/-1 other error
	 *                          SUCCESS/0 for success full load
	 *                          FILE_LOADED_NO_DATA_OR_ALREADY_LOADED/1 for file loadable, no data or already loaded
	 *                          FILE_NOT_READABLE_OR_OPEN_FAILED/2 for file not readable or open failed
	 *                          FILE_NOT_FOUND/3 for file not found
	 */
	public static function readEnvFile(
		string $path = __DIR__,
		string $env_file = '.env',
		bool $throw_exception = false,
	): DotEnvLevel {
		// default other error;
		$env_file_target = $path . DIRECTORY_SEPARATOR . $env_file;
		// this is not a file -> abort
		if (!is_file($env_file_target)) {
			if ($throw_exception) {
				throw new Exceptions\DotEnvFileNotFoundException("File not found: " . $env_file_target);
			}
			return DotEnvLevel::ERROR_FILE_NOT_FOUND;
		}
		// cannot open file -> abort
		if (!is_readable($env_file_target)) {
			if ($throw_exception) {
				throw new Exceptions\DotEnvFileNotReadableException("File not readable: " . $env_file_target);
			}
			return DotEnvLevel::ERROR_FILE_NOT_READABLE;
		}
		// open file
		if (($fp = fopen($env_file_target, 'r')) === false) {
			if ($throw_exception) {
				throw new Exceptions\DotEnvFileOpenFailedException("Open failed: " . $env_file_target);
			}
			return DotEnvLevel::ERROR_FILE_OPEN_FAILED;
		}
		// set to readable but not yet any data loaded
		$status = DotEnvLevel::WARNING_FILE_LOADED_NO_DATA;
		$block = false;
		$var = '';
		$prefix_name = '';
		/** @var array<string,mixed> */
		$_LOAD_ENV = [];
		while (($line = fgets($fp)) !== false) {
			// [] block must be a single line, or it will be ignored
			if (preg_match("/^\s*\[([\w_.\s]+)\]/", $line, $matches)) {
				$prefix_name = preg_replace("/\s+/", "_", $matches[1]) . ".";
			} elseif (preg_match("/^\s*([\w_.]+)\s*=\s*((\"?).*)/", $line, $matches)) {
				// main match for variable = value part
				$var = (string)($prefix_name . $matches[1]);
				$value = $matches[2];
				$quotes = $matches[3];
				// write only if env is not set yet, and write only the first time
				if (empty($_LOAD_ENV[$var])) {
					if (!empty($quotes)) {
						// match greedy for first to last so we move any " if there are
						if (preg_match('/^"(.*[^\\\])"/U', $value, $matches)) {
							$value = $matches[1];
						} else {
							// this is a multi line
							$block = true;
							// first " in string remove
							// add removed new line back because this is a multi line
							$value = ltrim($value, '"') . PHP_EOL;
						}
					} else {
						// strip any quotes at end for unquoted single line
						// an right hand spaces are removed too
						$value = false !== ($pos = strpos($value, self::COMMENT_CHAR)) ?
							rtrim(substr($value, 0, $pos)) : $value;
					}
					// if block is set, we strip line of slashes
					$_LOAD_ENV[$var] = $block === true ? stripslashes($value) : $value;
					// set successful load
					if ($status != DotEnvLevel::SUCCESS_DOUBLE_KEY) {
						$status = DotEnvLevel::SUCCESS;
					}
				} else {
					$status = DotEnvLevel::SUCCESS_DOUBLE_KEY;
				}
			} elseif ($block === true) {
				// read line until there is a unescaped "
				// this also strips everything after the last "
				if (preg_match("/(.*[^\\\])\"/", $line, $matches)) {
					$block = false;
					// strip ending " and EVERYTHING that follows after that
					$line = $matches[1];
				}
				// just be sure it is init before we fill
				if (!isset($_LOAD_ENV[$var])) {
					$_LOAD_ENV[$var] = '';
				} elseif (!is_string($_LOAD_ENV[$var])) {
					// if this is not string, skip
					continue;
				}
				// strip line of slashes
				$_LOAD_ENV[$var] .= stripslashes($line);
			}
		}
		if ($_LOAD_ENV) {
			// merge loaded env into $_ENV
			if (array_intersect_key($_LOAD_ENV, $_ENV)) {
				$status = DotEnvLevel::SUCCESS_ENV_EXIST_SKIP;
			}
			$_ENV = $_ENV + $_LOAD_ENV;
		}
		fclose($fp);
		return $status;
	}
}

// __END__
