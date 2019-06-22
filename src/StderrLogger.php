<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Cycle\Console;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

final class StderrLogger implements LoggerInterface
{
    use LoggerTrait;

    /** @var bool */
    private $enableColors;

    /**
     * @param bool $enableColors
     */
    public function __construct(bool $enableColors)
    {
        $this->enableColors = $enableColors;
    }

    /**
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = [])
    {
        if (!$this->enableColors) {
            error_log('> ' . $message);
        }

        if ($level == LogLevel::ERROR) {
            error_log("! \033[31m" . $message . "\033[0m");
        } elseif ($level == LogLevel::ALERT) {
            error_log("! \033[35m" . $message . "\033[0m");
        } elseif (strpos($message, 'SHOW') === 0) {
            error_log("> \033[34m" . $message . "\033[0m");
        } else {
            if ($this->isPostgresSystemQuery($message)) {
                error_log("> \033[90m" . $message . "\033[0m");
                return;
            }

            if (strpos($message, 'SELECT') === 0) {
                error_log("> \033[32m" . $message . "\033[0m");
            } elseif (strpos($message, 'INSERT') === 0) {
                error_log("> \033[36m" . $message . "\033[0m");
            } else {
                error_log("> \033[33m" . $message . "\033[0m");
            }
        }
    }

    protected function isPostgresSystemQuery(string $query): bool
    {
        $query = strtolower($query);
        if (
            strpos($query, 'tc.constraint_name')
            || strpos($query, 'pg_indexes')
            || strpos($query, 'tc.constraint_name')
            || strpos($query, 'pg_constraint')
            || strpos($query, 'information_schema')
            || strpos($query, 'pg_class')
        ) {
            return true;
        }

        return false;
    }
}