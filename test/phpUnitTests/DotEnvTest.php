<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for DotEnv
 * @coversDefaultClass \gullevek\DotEnv
 * @testdox \gullevek\DotEnv method tests
 */
final class DotEnvTest extends TestCase
{
    /**
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
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function envFileProvider(): array
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
                'status' => 3,
                'content' => [],
                'chmod' => null,
            ],
            'cannot open file' => [
                'folder' => __DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
                'file' => 'cannot_read.env',
                'status' => 2,
                'content' => [],
                // 0000
                'chmod' => '100000',
            ],
            'empty file' => [
                'folder' => __DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
                'file' => 'empty.env',
                'status' => 1,
                'content' => [],
                // 0664
                'chmod' => '100664',
            ],
            'override all' => [
                'folder' => __DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
                'file' => 'test.env',
                'status' => 0,
                'content' => $dot_env_content,
                // 0664
                'chmod' => '100664',
            ],
            'override directory' => [
                'folder' => __DIR__ . DIRECTORY_SEPARATOR . 'dotenv',
                'file' => null,
                'status' => 0,
                'content' => $dot_env_content,
                'chmod' => null,
            ],
        ];
    }

    /**
     * test read .env file
     *
     * @covers ::readEnvFile
     * @dataProvider envFileProvider
     * @testdox Read _ENV file from $folder / $file with expected status: $expected_status [$_dataName]
     *
     * @param  string|null $folder
     * @param  string|null $file
     * @param  int         $expected_status
     * @param  array       $expected_env
     * @param  string|null $chmod
     * @return void
     */
    public function testReadEnvFile(
        ?string $folder,
        ?string $file,
        int $expected_status,
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
            $status,
            $expected_status,
            'Assert returned status equal'
        );
        // now assert read data
        $this->assertEquals(
            $_ENV,
            $expected_env,
            'Assert _ENV correct'
        );
        // if we have file and chmod unset
        if ($old_chmod !== null) {
            chmod($folder . DIRECTORY_SEPARATOR . $file, $old_chmod);
        }
    }
}

// __END__
