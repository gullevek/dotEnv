<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for DotEnv
 */
#[TestDox("\gullevek\DotEnv method tests")]
#[CoversClass(\gullevek\dotEnv\DotEnv::class)]
#[CoversMethod(\gullevek\dotEnv\DotEnv::class, 'loadOutsideGetEnv')]
#[CoversMethod(\gullevek\dotEnv\DotEnv::class, 'getLastReadEnvFileErrors')]
#[CoversMethod(\gullevek\dotEnv\DotEnv::class, 'readEnvFile')]
final class DotEnvTest extends TestCase
{
	/** @var array<string,string> */
	public const OUTSIDE_SET = [
		'DOTENV_PHPUNIT_A' => 'EnvVarPhpUnit_A',
		'DOTENV_PHPUNIT_B' => 'EnvVarPhpUnit_B',
		'DOTENV_OTHER' => 'Other_A',
		'OTHER_PHPUNIT_A' => 'Other_B',
	];

	/**
	 * MARK: setup
	 * setup the .env files before test run
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void
	{
		// create .env files
		$file_content = __DIR__ . DIRECTORY_SEPARATOR
			. 'dotenv' . DIRECTORY_SEPARATOR
			. 'test.env';
		// copy to all folder levels
		$env_files = [
			__DIR__ . DIRECTORY_SEPARATOR
				. 'dotenv' . DIRECTORY_SEPARATOR
				. '.env',
			__DIR__ . DIRECTORY_SEPARATOR
				. '.env',
			__DIR__ . DIRECTORY_SEPARATOR
				. '..' . DIRECTORY_SEPARATOR
				. '.env',
		];
		// if not found, skip -> all will fail
		if (is_file($file_content)) {
			foreach ($env_files as $env_file) {
				copy($file_content, $env_file);
			}
		}
		foreach (self::OUTSIDE_SET as $key => $value) {
			putenv($key . "=" . $value);
		}
	}

	// MARK: loadOutsideGetEnv

	/**
	 * provider for the load outside get env
	 *
	 * @return array
	 */
	public static function providerLoadOutsideGetEnv(): array
	{
		return [
			'all' => [
				'set' => null,
				'env_list' => null,
				'flag' => null,
				'expected' => self::OUTSIDE_SET,
				'contain_only' => true,
			],
			'all, set' => [
				'set' => null,
				'env_list' => array_keys(self::OUTSIDE_SET),
				'flag' => null,
				'expected' => self::OUTSIDE_SET,
				'contain_only' => true,
			],
			'set, default merge flag' => [
				'set' => [
					'DOTENV_PHPUNIT_A' => 'InternalSet_A',
					'NEW_ENTRY' => 'Inside_New',
				],
				'env_list' => null,
				'flag' => null,
				'expected' => self::OUTSIDE_SET,
				'contain_only' => true,
			],
			'set, overwrite existing' => [
				'set' => [
					'DOTENV_PHPUNIT_A' => 'InternalSet_A',
					'NEW_ENTRY' => 'Inside_New',
				],
				'env_list' => null,
				'flag' => \gullevek\dotEnv\DotEnv::MERGE_OVERWRITE_EXISTING,
				'expected' => [
					'DOTENV_PHPUNIT_A' => 'EnvVarPhpUnit_A',
					'DOTENV_PHPUNIT_B' => 'EnvVarPhpUnit_B',
					'DOTENV_OTHER' => 'Other_A',
					'OTHER_PHPUNIT_A' => 'Other_B',
					'NEW_ENTRY' => 'Inside_New',
				],
				'contain_only' => true,
			],
			'set, keep existing' => [
				'set' => [
					'DOTENV_PHPUNIT_A' => 'InternalSet_A',
					'NEW_ENTRY' => 'Inside_New',
				],
				'env_list' => null,
				'flag' => \gullevek\dotEnv\DotEnv::MERGE_KEEP_EXISTING,
				'expected' => [
					'DOTENV_PHPUNIT_A' => 'InternalSet_A',
					'DOTENV_PHPUNIT_B' => 'EnvVarPhpUnit_B',
					'DOTENV_OTHER' => 'Other_A',
					'OTHER_PHPUNIT_A' => 'Other_B',
					'NEW_ENTRY' => 'Inside_New',
				],
				'contain_only' => true,
			],
			// load list
			'one ok, one ignore' => [
				'set' => null,
				'env_list' => ['DOTENV_PHPUNIT_A', 'DOES_NOT_EXIST'],
				'flag' => null,
				'expected' => ['DOTENV_PHPUNIT_A' => 'EnvVarPhpUnit_A'],
				'contain_only' => false,
			],
			'one ok, overwrite' => [
				'set' => [
					'DOTENV_PHPUNIT_A' => 'InternalSet_A',
					'NEW_ENTRY' => 'Inside_New',
				],
				'env_list' => ['DOTENV_PHPUNIT_A', 'DOES_NOT_EXIST'],
				'flag' => null,
				'expected' => [
					'DOTENV_PHPUNIT_A' => 'EnvVarPhpUnit_A',
					'NEW_ENTRY' => 'Inside_New',
				],
				'contain_only' => false,
			],
			'one ok, overwrite' => [
				'set' => [
					'DOTENV_PHPUNIT_A' => 'InternalSet_A',
					'NEW_ENTRY' => 'Inside_New',
				],
				'env_list' => ['DOTENV_PHPUNIT_A', 'DOES_NOT_EXIST'],
				'flag' => \gullevek\dotEnv\DotEnv::MERGE_OVERWRITE_EXISTING,
				'expected' => [
					'DOTENV_PHPUNIT_A' => 'EnvVarPhpUnit_A',
					'NEW_ENTRY' => 'Inside_New',
				],
				'contain_only' => false,
			],
			'one ok, overwrite' => [
				'set' => [
					'DOTENV_PHPUNIT_A' => 'InternalSet_A',
					'NEW_ENTRY' => 'Inside_New',
				],
				'env_list' => ['DOTENV_PHPUNIT_A', 'DOES_NOT_EXIST'],
				'flag' => \gullevek\dotEnv\DotEnv::MERGE_KEEP_EXISTING,
				'expected' => [
					'DOTENV_PHPUNIT_A' => 'InternalSet_A',
					'NEW_ENTRY' => 'Inside_New',
				],
				'contain_only' => false,
			],
			// fnmatch
			'load fnmatch X*' => [
				'set' => null,
				'env_list' => ['DOTENV_*'],
				'flag' => null,
				'expected' => [
					'DOTENV_PHPUNIT_A' => 'EnvVarPhpUnit_A',
					'DOTENV_PHPUNIT_B' => 'EnvVarPhpUnit_B',
					'DOTENV_OTHER' => 'Other_A',
				],
				'contain_only' => false,
			],
			'load fnmatch X*X' => [
				'set' => null,
				'env_list' => ['DOTENV_*_A'],
				'flag' => null,
				'expected' => [
					'DOTENV_PHPUNIT_A' => 'EnvVarPhpUnit_A',
				],
				'contain_only' => false,
			],
			'load fnmatch *X*' => [
				'set' => null,
				'env_list' => ['*_PHPUNIT_*'],
				'flag' => null,
				'expected' => [
					'DOTENV_PHPUNIT_A' => 'EnvVarPhpUnit_A',
					'DOTENV_PHPUNIT_B' => 'EnvVarPhpUnit_B',
					'OTHER_PHPUNIT_A' => 'Other_B',
				],
				'contain_only' => false,
			],
			'load fnmatch *X' => [
				'set' => null,
				'env_list' => ['*_PHPUNIT_A'],
				'flag' => null,
				'expected' => [
					'DOTENV_PHPUNIT_A' => 'EnvVarPhpUnit_A',
					'OTHER_PHPUNIT_A' => 'Other_B',
				],
				'contain_only' => false,
			],
			'load fnmatch X*, merge existing' => [
				'set' => [
					'DOTENV_PHPUNIT_A' => 'InternalSet_A',
				],
				'env_list' => ['DOTENV_*'],
				'flag' => \gullevek\dotEnv\DotEnv::MERGE_OVERWRITE_EXISTING,
				'expected' => [
					'DOTENV_PHPUNIT_A' => 'EnvVarPhpUnit_A',
					'DOTENV_PHPUNIT_B' => 'EnvVarPhpUnit_B',
					'DOTENV_OTHER' => 'Other_A',
				],
				'contain_only' => false,
			],
			'load fnmatch X*, keep existing' => [
				'set' => [
					'DOTENV_PHPUNIT_A' => 'InternalSet_A',
				],
				'env_list' => ['DOTENV_*'],
				'flag' => \gullevek\dotEnv\DotEnv::MERGE_KEEP_EXISTING,
				'expected' => [
					'DOTENV_PHPUNIT_A' => 'InternalSet_A',
					'DOTENV_PHPUNIT_B' => 'EnvVarPhpUnit_B',
					'DOTENV_OTHER' => 'Other_A',
				],
				'contain_only' => false,
			],
		];
	}

	/**
	 * Tests for reading getenv data into $_ENV
	 *
	 * @return void
	 */
	#[Test]
	#[TestDox('Test loading outside environment variables [$_dataName]')]
	#[DataProvider('providerLoadOutsideGetEnv')]
	public function testLoadOutsideGetEnv(
		?array $set,
		?array $env_list,
		?int $flag,
		array $expected,
		bool $contain_only,
	): void {
		$_ENV = [];
		if ($set != null) {
			$_ENV = $set;
		}
		if ($env_list === null) {
			if ($flag === null) {
				\gullevek\dotEnv\DotEnv::loadOutsideGetEnv();
			} else {
				\gullevek\dotEnv\DotEnv::loadOutsideGetEnv(merge_flag: $flag);
			}
		} else {
			if ($flag === null) {
				\gullevek\dotEnv\DotEnv::loadOutsideGetEnv($env_list);
			} else {
				\gullevek\dotEnv\DotEnv::loadOutsideGetEnv($env_list, merge_flag: $flag);
			}
		}
		if (!$contain_only) {
			$this->assertArraysAreIdenticalIgnoringOrder(
				$expected,
				$_ENV
			);
		} else {
			// assert sub array doest not exist anymore
			foreach ($expected as $key => $value) {
				$this->assertArrayHasKey($key, $_ENV);
				$this->assertEquals($value, $_ENV[$key]);
			}
		}
	}

	/**
	 * make sure a null read returns null for last errors
	 * run before any other check, so we get a clean return
	 *
	 * @return void
	 */
	#[TEST]
	#[TestDox('Test for reading empty getLastReadEnvFileErrors()')]
	public function testGetLastReadEnvFileErrors(): void
	{
		$content = \gullevek\dotEnv\DotEnv::getLastReadEnvFileErrors();
		$this->assertEquals(
			[],
			$content
		);
	}

	// MARK: readEnvFile

	/**
	 * env file read provider
	 *
	 * @return array
	 */
	public static function providerEnvFile(): array
	{
		$dot_env_content = [
			'SOMETHING' => 'A',
			'OTHER' => 'B IS B',
			'Complex' => 'A B \"D is F',
			'HAS_SPACE' => 'ABC',
			'HAS_COMMENT_QUOTES_SPACE' => 'Comment at end with quotes and space',
			'HAS_COMMENT_QUOTES_NO_SPACE' => 'Comment at end with quotes no space',
			'HAS_COMMENT_NO_QUOTES_SPACE' => 'Comment at end no quotes and space',
			'HAS_COMMENT_NO_QUOTES_NO_SPACE' => 'Comment at end no quotes no space',
			'COMMENT_IN_TEXT_QUOTES' => 'Foo bar # comment in here',
			'HAS_EQUAL_NO_QUITES' => 'Is This = Valid',
			'HAS_EQUAL_QUITES' => 'Is This = Valid',
			'FAILURE' => 'ABC',
			'SIMPLEBOX' => 'A B  C',
			'TITLE' => '1',
			'FOO' => '1.2',
			'SOME.TEST' => 'Test Var',
			'SOME.LIVE' => 'Live Var',
			'A_TEST1' => 'foo',
			'A_TEST2' => '${TEST1:-bar}',
			'A_TEST3' => '${TEST4:-bar}',
			'A_TEST5' => 'null',
			'A_TEST6' => '${TEST5-bar}',
			'A_TEST7' => '${TEST6:-bar}',
			'B_TEST1' => 'foo',
			'B_TEST2' => '${TEST1:=bar}',
			'B_TEST3' => '${TEST4:=bar}',
			'B_TEST5' => 'null',
			'B_TEST6' => '${TEST5=bar}',
			'B_TEST7' => '${TEST6=bar}',
			'Test' => 'A',
			'TEST' => 'B',
			'LINE' => "ABC\nDEF",
			'OTHERLINE' => "ABC\nAF\"ASFASDF\nMORESHIT",
			'SUPERLINE' => '',
			'__FOO_BAR_1' => 'b',
			'__FOOFOO' => 'f     ',
			123123 => 'number',
			'EMPTY' => '',
			'Var_Test.TEST' => 'Block 1 D',
			'OtherSet.TEST' => 'Block 2 D',
		];
		// 0: folder relative to test folder, if unset __DIR__
		// 1: file, if unset .env
		// 2: status to be returned
		// 3: _ENV file content to be set
		// 4: override chmod as octect in string
		return [
			'default' => [
				'folder' => null,
				'file' => null,
				'expected_status' => \gullevek\dotEnv\Levels\DotEnvLevel::ERROR_FILE_NOT_FOUND->value,
				'expected_status_level' => \gullevek\dotEnv\Levels\DotEnvLevel::ERROR_FILE_NOT_FOUND,
				'expected_env' => [],
				'chmod' => null,
			],
			'cannot open file' => [
				'folder' => __DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
				'file' => 'cannot_read.env',
				'expected_status' => \gullevek\dotEnv\Levels\DotEnvLevel::ERROR_FILE_NOT_READABLE->value,
				'expected_status_level' => \gullevek\dotEnv\Levels\DotEnvLevel::ERROR_FILE_NOT_READABLE,
				'expected_env' => [],
				// 0000
				'chmod' => '100000',
			],
			'empty file' => [
				'folder' => __DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
				'file' => 'empty.env',
				'expected_status' => \gullevek\dotEnv\Levels\DotEnvLevel::WARNING_FILE_LOADED_NO_DATA->value,
				'expected_status_level' => \gullevek\dotEnv\Levels\DotEnvLevel::WARNING_FILE_LOADED_NO_DATA,
				'expected_env' => [],
				// 0664
				'chmod' => '100664',
			],
			'override all' => [
				'folder' => __DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
				'file' => 'test.env',
				'expected_status' => \gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS_DOUBLE_KEY->value,
				'expected_status_level' => \gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS_DOUBLE_KEY,
				'expected_env' => $dot_env_content,
				// 0664
				'chmod' => '100664',
			],
			'override directory' => [
				'folder' => __DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
				'file' => null,
				'expected_status' => \gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS_DOUBLE_KEY->value,
				'expected_status_level' => \gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS_DOUBLE_KEY,
				'expected_env' => $dot_env_content,
				'chmod' => null,
			],
		];
	}

	/**
	 * MARK: general readEnvFile
	 * test read .env file
	 *
	 * @param  string|null $folder
	 * @param  string|null $file
	 * @param  int         $expected_status
	 * @param  int         $expected_status_level
	 * @param  array       $expected_env
	 * @param  string|null $chmod
	 * @return void
	 */
	#[Test]
	#[TestDox('Read _ENV file from $folder / $file with expected status: $expected_status [$_dataName]')]
	#[DataProvider('providerEnvFile')]
	public function testReadEnvFile(
		?string $folder,
		?string $file,
		int $expected_status,
		mixed $expected_status_level,
		array $expected_env,
		?string $chmod
	): void {
		// skip if chmod is set to 10000 (000 no rights) if we are root
		// as root there is no stop reading a file
		if (
			!empty($chmod) &&
			$chmod == '100000' &&
			getmyuid() == 0
		) {
			$this->markTestSkipped(
				"Skip cannot read file test because run user is root"
			);
			return;
		}
		// reset $_ENV for clean compare
		$_ENV = [];
		// previous file perm
		$old_chmod = null;
		// if we have change permission for file
		if (
			is_file($folder . DIRECTORY_SEPARATOR . $file) &&
			!empty($chmod)
		) {
			// get the old permissions
			$old_chmod = fileperms($folder . DIRECTORY_SEPARATOR . $file);
			chmod($folder . DIRECTORY_SEPARATOR . $file, octdec($chmod));
		}
		if ($folder !== null && $file !== null) {
			$status = \gullevek\dotEnv\DotEnv::readEnvFile($folder, $file);
		} elseif ($folder !== null) {
			$status = \gullevek\dotEnv\DotEnv::readEnvFile($folder);
		} else {
			$status = \gullevek\dotEnv\DotEnv::readEnvFile();
		}
		$this->assertEquals(
			$expected_status,
			$status->value,
			'Assert returned status equal'
		);
		$this->assertEquals(
			$expected_status_level,
			$status,
			'Assert returned status level equal'
		);
		// now assert read data
		$this->assertEquals(
			$expected_env,
			$_ENV,
			'Assert _ENV correct'
		);
		if ($status == \gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS_DOUBLE_KEY) {
			$this->assertArrayHasKey(
				\gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS_DOUBLE_KEY->name,
				\gullevek\dotEnv\DotEnv::getLastReadEnvFileErrors()
			);
		}
		// if we have file and chmod unset
		if ($old_chmod !== null) {
			chmod($folder . DIRECTORY_SEPARATOR . $file, $old_chmod);
		}
	}

	/**
	 * MARK: external load readEnvFile
	 * set env load with pre-set entries and overwrite
	 *
	 * @return void
	 */
	#[Test]
	#[TestDox('readEnvFile tests with outside enviornment variable load')]
	public function testReadEnvFileOutsideEnvironmentVars(): void
	{
		$expected_env = [
			'TEST' => 'Abc',
			'FOOBAR' => '123',
		];
		// plain empty test
		$_ENV = [];
		$status = \gullevek\dotEnv\DotEnv::readEnvFile(
			__DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
			'test_clean.env'
		);
		if ($status !== \gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS) {
			$this->markTestSkipped('Cannot read test_clean.env');
		}
		$this->assertArraysAreIdenticalIgnoringOrder(
			$expected_env,
			$_ENV
		);
		// env set in $_ENV
		$_ENV = [
			'TEST' => 'Other'
		];
		$status = \gullevek\dotEnv\DotEnv::readEnvFile(
			__DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
			'test_clean.env'
		);
		$this->assertEquals(
			\gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS_ENV_EXIST_SKIP,
			$status
		);
		$this->assertArrayHasKey(
			\gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS_ENV_EXIST_SKIP->name,
			\gullevek\dotEnv\DotEnv::getLastReadEnvFileErrors()
		);
		$this->assertEquals(
			$expected_env['FOOBAR'],
			$_ENV['FOOBAR']
		);
		$this->assertEquals(
			'Other',
			$_ENV['TEST']
		);
		// put env preloaded but no pre load is ok
		putenv('TEST=Outside');
		// plain empty test
		$_ENV = [];
		$status = \gullevek\dotEnv\DotEnv::readEnvFile(
			__DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
			'test_clean.env',
		);
		if ($status !== \gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS) {
			$this->markTestSkipped('Cannot read test_clean.env');
		}
		$this->assertArraysAreIdenticalIgnoringOrder(
			$expected_env,
			$_ENV
		);
		// expect TEST to be outside
		$_ENV = [];
		$status = \gullevek\dotEnv\DotEnv::readEnvFile(
			__DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
			'test_clean.env',
			load_outside_env: true,
		);
		$this->assertEquals(
			\gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS_ENV_EXIST_SKIP,
			$status
		);
		$this->assertArrayHasKey(
			\gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS_ENV_EXIST_SKIP->name,
			\gullevek\dotEnv\DotEnv::getLastReadEnvFileErrors()
		);
		$this->assertEquals(
			$expected_env['FOOBAR'],
			$_ENV['FOOBAR']
		);
		$this->assertEquals(
			'Outside',
			$_ENV['TEST']
		);
	}

	// MARK: exceptions

	/**
	 * test no double entries exception
	 *
	 * @return void
	 */
	#[Test]
	#[TestDox('Test for successful load with no double entries in readEnvFile')]
	public function testReadEnvFileNoDoubleEntries(): void
	{
		// reset $_ENV for clean compare
		$_ENV = [];
		// read file first time
		$status1 = \gullevek\dotEnv\DotEnv::readEnvFile(
			__DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
			'test_clean.env'
		);
		// read file second time
		$status2 = \gullevek\dotEnv\DotEnv::readEnvFile(
			__DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
			'test_clean.env'
		);
		$this->assertEquals(
			\gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS,
			$status1,
			'Assert first load status level equal'
		);
		$this->assertEquals(
			\gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS_ENV_EXIST_SKIP,
			$status2,
			'Assert second load status level equal'
		);
		// check that last erros match
		$this->assertArrayHasKey(
			\gullevek\dotEnv\Levels\DotEnvLevel::SUCCESS_ENV_EXIST_SKIP->name,
			\gullevek\dotEnv\DotEnv::getLastReadEnvFileErrors()
		);
		$this->assertArraysAreIdenticalIgnoringOrder(
			[
				'SUCCESS_ENV_EXIST_SKIP' => [
					'TEST',
					'FOOBAR'
				]
			],
			\gullevek\dotEnv\DotEnv::getLastReadEnvFileErrors()
		);
	}

	/**
	 * mark test file read exceptions
	 *
	 * @return void
	 */
	#[Test]
	#[TestDox('Test for exceptions thrown in readEnvFile')]
	public function testReadEnvFileExceptions(): void
	{
		// file not found
		try {
			\gullevek\dotEnv\DotEnv::readEnvFile(
				__DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
				'not_existing.env',
				throw_exception: true
			);
			$this->fail('Expected DotEnvFileNotFoundException was not thrown');
		} catch (\gullevek\dotEnv\Exceptions\DotEnvFileNotFoundException $e) {
			$this->assertStringContainsString(
				'File not found',
				$e->getMessage(),
				'Assert exception message does not contains "File not found"'
			);
		}
		try {
			chmod(__DIR__ . DIRECTORY_SEPARATOR . 'dotenv' . DIRECTORY_SEPARATOR . 'cannot_read.env', octdec('100000'));
			\gullevek\dotEnv\DotEnv::readEnvFile(
				__DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
				'cannot_read.env',
				throw_exception: true
			);
			$this->fail('Expected DotEnvFileNotReadableException was not thrown');
		} catch (\gullevek\dotEnv\Exceptions\DotEnvFileNotReadableException $e) {
			$this->assertStringContainsString(
				'File not readable',
				$e->getMessage(),
				'Assert exception message does not contains "File not readable"'
			);
		} catch (\gullevek\dotEnv\Exceptions\DotEnvFileOpenFailedException $e) {
			$this->assertStringContainsString(
				'Open failed',
				$e->getMessage(),
				'Assert exception message does not contains "Open failed"'
			);
		}
		chmod(__DIR__ . DIRECTORY_SEPARATOR . 'dotenv' . DIRECTORY_SEPARATOR . 'cannot_read.env', octdec('100664'));
		// TODO: Catch fopen false error
		// DotEnvLevel::ERROR_FILE_OPEN_FAILED
		// throw new Exceptions\DotEnvFileOpenFailedException
	}

	/**
	 * test for exception with bad merge flag
	 */
	#[Test]
	#[TestDox('Test for exceptions thrown in loadOutsideGetEnv')]
	public function testLoadOutsideGetEnvExceptions(): void
	{
		try {
			\gullevek\dotEnv\DotEnv::loadOutsideGetEnv(merge_flag: 9);
			$this->fail('Expected InvalidArgumentException was not thrown');
		} catch (\InvalidArgumentException $e) {
			$this->assertStringContainsString(
				'Merge flag is not valid:',
				$e->getMessage(),
				'Assert exception message doest not contain "Merge flag is not valid:"'
			);
		}
	}

	/**
	 * MARK: test comment
	 * Test comment char
	 *
	 * @return void
	 */
	#[Test]
	#[TestDox('Test that comment char is #')]
	public function testDotEnvCommentChar(): void
	{
		$this->assertEquals('#', \gullevek\dotEnv\DotEnv::COMMENT_CHAR, 'Comment character should be #');
	}
}

// __END__
