<?php
/**
 * Slow query logger that should wrap a logger sent to a Knit store in order to raise query log message
 * when a slow query happens.
 * 
 * @package SplotKnitModule
 * @subpackage Knit
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2014, Michał Dudek
 * @license MIT
 */
namespace Splot\KnitModule\Knit;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

use MD\Foundation\LogLevels;

class SlowQueryLogger extends AbstractLogger implements LoggerInterface
{

    /**
     * The original store logger.
     * 
     * @var LoggerInterface
     */
    protected $storeLogger;

    /**
     * Is the slow query logger enabled?
     * 
     * @var boolean
     */
    protected $enabled = true;

    /**
     * Query execution time in seconds that qualifies the query to be logged as slow.
     * 
     * @var float
     */
    protected $threshold = 0.5;

    /**
     * Log level to which a slow query should be raised.
     * 
     * @var string
     */
    protected $raiseToLevel = LogLevel::NOTICE;

    /**
     * Constructor.
     * 
     * @param LoggerInterface $storeLogger  The original store logger.
     * @param boolean         $enabled      [optional] Is the slow query logger enabled? Default: true.
     * @param float           $threshold    [optional] Query execution time in seconds that qualifies
     *                                      the query to be logged as slow. Default: 0.5 [s].
     * @param string          $raiseToLevel [optional] Log level to which a slow query should be raised.
     *                                      Default: LogLevel::NOTICE.
     */
    public function __construct(LoggerInterface $storeLogger, $enabled = true, $threshold = 0.5, $raiseToLevel = LogLevel::NOTICE) {
        $this->storeLogger = $storeLogger;
        $this->enabled = $enabled;
        $this->threshold = $threshold;
        $this->raiseToLevel = $raiseToLevel;
    }

    /**
     * Raises the log message level if it's query execution time was higher than the configured threshold
     * and passes it to the store logger with the new level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array()) {
        if ($this->enabled
            && isset($context['executionTime'])
            && $context['executionTime'] >= $this->threshold
            && LogLevels::isLowerLevel($level, $this->raiseToLevel)
        ) {
            $level = $this->raiseToLevel;
        }

        // pass it to the real logger
        $this->storeLogger->log($level, $message, $context);
    }

}