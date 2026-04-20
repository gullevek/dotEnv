<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2026/3/25
 * DESCRIPTION:
 * Enum for error levels
*/

declare(strict_types=1);

namespace gullevek\dotEnv\Levels;

enum DotEnvLevel: int
{
	case ERROR_FILE_OPEN_FAILED = -1;
	case SUCCESS = 0;
	case SUCCESS_DOUBLE_KEY = 1;
	case SUCCESS_ENV_EXIST_SKIP = 2;
	// case SUCCESS_DOUBLE_KEY_ENV_EXIST_SKIP = 3;
	case WARNING_FILE_LOADED_NO_DATA = 10;

	/**
	 * @param int $value
	 * @#param int $value
	 * @return static
	 */
	public static function fromValue(int $value): self
	{
		return self::from($value);
	}

	/**
	 * Get error message for a given DotEnvLevel
	 *
	 * @param self $level
	 * @return string Error message corresponding to the given DotEnvLevel
	 */
	public static function errorMessage(self $level): string
	{
		return match ($level) {
			// errors
			self::ERROR_FILE_OPEN_FAILED => 'File not readable/not found/open failed',
			// success
			self::SUCCESS => 'Success',
			self::SUCCESS_DOUBLE_KEY => 'Partial loaded',
			self::SUCCESS_ENV_EXIST_SKIP => 'Environment variable exists, skipped',
			// self::SUCCESS_DOUBLE_KEY_ENV_EXIST_SKIP => 'Partial loaded, environment variable exists, skipped',
			// success but no data loaded
			self::WARNING_FILE_LOADED_NO_DATA => 'File loaded, no data or already loaded',
		};
	}
}

// __END__
