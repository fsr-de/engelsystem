<?php

namespace Engelsystem\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

class EngelsystemLogger extends AbstractLogger
{
    protected $allowedLevels = [
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::DEBUG,
        LogLevel::EMERGENCY,
        LogLevel::ERROR,
        LogLevel::INFO,
        LogLevel::NOTICE,
        LogLevel::WARNING,
    ];

    /**
     * Logs with an arbitrary level.
     *
     * @TODO: Implement $context['exception']
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @throws InvalidArgumentException
     */
    public function log($level, $message, array $context = [])
    {
        if (!$this->checkLevel($level)) {
            throw new InvalidArgumentException();
        }

        $message = $this->interpolate($message, $context);

        LogEntry_create($level, $message);
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $message
     * @param array  $context
     * @return string
     */
    protected function interpolate($message, array $context = [])
    {
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (is_array($val) || (is_object($val) && !method_exists($val, '__toString'))) {
                continue;
            }

            // replace the values of the message
            $message = str_replace('{' . $key . '}', $val, $message);
        }

        return $message;
    }

    /**
     * @param string $level
     * @return bool
     */
    protected function checkLevel($level)
    {
        return in_array($level, $this->allowedLevels);
    }
}
