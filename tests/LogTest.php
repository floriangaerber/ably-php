<?php
namespace tests;
use Ably\AblyRest;
use Ably\Log;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/factories/TestApp.php';

class LogTest extends TestCase {

    public static function tearDownAfterClass(): void {
        // ensure the logger is reset to default
        $ably = new AblyRest( [
            'key' => 'fake.key:totallyFake'
        ] );
    }

    private function logMessages() {
        Log::v('This is a test verbose message.');
        Log::d('This is a test debug message.');
        Log::w('This is a test warning.');
        Log::e('This is a test error.');
    }

    /**
     * Test if logger uses warning level as default
     */
    public function testLogDefault() {
        $out = '';

        $opts = [
            'key' => 'fake.key:veryFake',
            'logHandler' => function( $level, $args ) use ( &$out ) {
                $out .= $args[0] . "\n";
            },
        ];
        $ably = new AblyRest( $opts );

        $this->logMessages();

        $this->assertStringContainsString('This is a test warning.', $out, 'Expected warning level to be logged.');
        $this->assertStringContainsString('This is a test error.', $out, 'Expected error level to be logged.');
        $this->assertStringNotContainsString('This is a test verbose message.', $out, 'Expected verbose level NOT to be logged.');
        $this->assertStringNotContainsString('This is a test debug message.', $out, 'Expected debug level NOT to be logged.');
    }

    /**
     * Test verbose log level with a handler
     */
    public function testLogVerbose() {
        $out = '';

        $opts = [
            'key' => 'fake.key:veryFake',
            'logLevel' => Log::VERBOSE,
            'logHandler' => function( $level, $args ) use ( &$out ) {
                $out .= $args[0] . "\n";
            },
        ];

        $ably = new AblyRest( $opts );
        $this->logMessages();

        $this->assertStringContainsString('This is a test warning.', $out, 'Expected warning level to be logged.');
        $this->assertStringContainsString('This is a test error.', $out, 'Expected error level to be logged.');
        $this->assertStringContainsString('This is a test verbose message.', $out, 'Expected verbose level to be logged.');
        $this->assertStringContainsString('This is a test debug message.', $out, 'Expected debug level to be logged.');
    }

    /**
     * Test log level == NONE
     */
    public function testLogNone() {
        $called = false;
        $opts = [
            'key' => 'fake.key:veryFake',
            'logLevel' => Log::NONE,
            'logHandler' => function( $level, $args ) use ( &$called ) {
                $called = true;
            },
        ];

        $ably = new AblyRest( $opts );
        $this->logMessages();
        $this->assertFalse( $called, 'Log handler incorrectly called' );
    }
}
