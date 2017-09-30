<?php

namespace Engelsystem\Test\Logger;

use Engelsystem\Logger\EngelsystemLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class EngelsystemLoggerTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        require_once __DIR__ . '/../../../includes/engelsystem_provider.php';
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return new EngelsystemLogger();
    }

    public function testImplements()
    {
        $this->assertInstanceOf('Psr\Log\LoggerInterface', $this->getLogger());
    }

    /**
     * @return string[]
     */
    public function provideLogLevels()
    {
        return [
            [LogLevel::ALERT],
            [LogLevel::CRITICAL],
            [LogLevel::DEBUG],
            [LogLevel::EMERGENCY],
            [LogLevel::ERROR],
            [LogLevel::INFO],
            [LogLevel::NOTICE],
            [LogLevel::WARNING],
        ];
    }

    /**
     * @dataProvider provideLogLevels
     * @param string $level
     */
    public function testAllLevels($level)
    {
        $logger = $this->getLogger();

        LogEntries_clear_all();

        $logger->log($level, 'First log message');
        $logger->{$level}('Second log message');

        $entries = LogEntries();
        $this->assertCount(2, $entries);
    }

    public function testContextReplacement()
    {
        $logger = $this->getLogger();
        LogEntries_clear_all();

        $logger->log(LogLevel::INFO, 'My username is {username}', ['username' => 'Foo']);

        $entry = $this->getLastEntry();
        $this->assertEquals('My username is Foo', $entry['message']);
        $this->assertEquals(LogLevel::INFO, $entry['level']);
    }

    /**
     * @return string[]
     */
    public function provideContextReplaceValues()
    {
        return [
            ['Data and {context}', [], 'Data and {context}'],
            ['Data and {context}', ['context' => null], 'Data and '],
            ['Data and {context}', ['context' => new \stdClass()], 'Data and {context}'],
            ['Some user asked: {question}', ['question' => 'Foo?'], 'Some user asked: Foo?'],
        ];
    }

    /**
     * @dataProvider provideContextReplaceValues
     *
     * @param string   $message
     * @param string[] $context
     * @param string   $expected
     */
    public function testContextReplaceValues($message, $context, $expected)
    {
        $logger = $this->getLogger();
        $logger->log(LogLevel::INFO, $message, $context);

        $entry = $this->getLastEntry();
        $this->assertEquals($expected, $entry['message']);
    }

    public function testContextToString()
    {
        $logger = $this->getLogger();
        LogEntries_clear_all();

        $mock = $this->getMockBuilder('someDataProvider')
            ->setMethods(['__toString'])
            ->getMock();

        $mock->expects($this->atLeastOnce())
            ->method('__toString')
            ->will($this->returnValue('FooBar'));

        $logger->log(LogLevel::INFO, 'Some data and {context}', ['context' => $mock]);

        $entry = $this->getLastEntry();
        $this->assertEquals('Some data and FooBar', $entry['message']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testThrowExceptionOnInvalidLevel()
    {
        $logger = $this->getLogger();

        $logger->log('This log level should never be defined', 'Some message');
    }

    /**
     * @return array
     */
    public function getLastEntry()
    {
        $entries = LogEntries();
        $entry = array_pop($entries);

        return $entry;
    }

    public function tearDown()
    {
        LogEntries_clear_all();
    }
}
