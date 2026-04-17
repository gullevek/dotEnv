<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2026/3/25
 * DESCRIPTION:
 * dotenv Level tests
*/

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[TestDox("\gullevek\dotEnv\Levels\DotEnvLevel method tests")]
#[CoversClass(\gullevek\dotEnv\Levels\DotEnvLevel::class)]
final class DotEnvLevelTest extends TestCase
{
	/**
	 * Test error message generation for each DotEnvLevel case
	 *
	 * @return void
	 */
	#[Test]
	public function testErrorMessage(): void
	{
		foreach (\gullevek\dotEnv\Levels\DotEnvLevel::cases() as $case) {
			$message = \gullevek\dotEnv\Levels\DotEnvLevel::errorMessage($case);
			$this->assertIsString($message);
			$this->assertNotEmpty($message);
		}
	}

	/**
	 * Test that each fixed level has the correct error level number
	 */
	public static function providerDotEnvLevels(): array
	{
		return [
			[-1, \gullevek\dotEnv\Levels\DotEnvLevel::ERROR_FILE_NOT_FOUND],
			[-2, \gullevek\dotEnv\Levels\DotEnvLevel::ERROR_FILE_NOT_READABLE],
			[-3, \gullevek\dotEnv\Levels\DotEnvLevel::ERROR_FILE_OPEN_FAILED],
			[0, \gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS],
			[1, \gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS_DOUBLE_KEY],
			[2, \gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS_ENV_EXIST_SKIP],
			[3, \gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS_DOUBLE_KEY_ENV_EXIST_SKIP],
			[10, \gullevek\dotEnv\Levels\DotEnvLevel::WARNING_FILE_LOADED_NO_DATA],
		];
	}

	/**
	 * Check that each level exists and int matches the expected value
	 *
	 * @param  int                          $expected
	 * @param  \gullevek\dotEnv\DotEnvLevel $level
	 * @return void
	 */
	#[Test]
	#[TestDox(' [$_dataName]')]
	#[DataProvider('providerDotEnvLevels')]
	public function testDotEnvLevels(int $expected, mixed $level): void
	{
		$this->assertSame($expected, $level->value);
		$this->assertSame($level, \gullevek\dotEnv\Levels\DotEnvLevel::fromValue($expected));
	}
}

// __END__
