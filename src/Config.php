<?php
/**
 * Cycle ORM
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Cycle\Console;

use Cycle\Console\Exception\ConfigException;
use Psr\Log\LoggerInterface;
use Spiral\Database\Config\DatabaseConfig;
use Spiral\Database\Driver\MySQL\MySQLDriver;
use Spiral\Database\Driver\Postgres\PostgresDriver;
use Spiral\Database\Driver\SQLite\SQLiteDriver;
use Spiral\Database\Driver\SQLServer\SQLServerDriver;

final class Config
{
    /** @var DatabaseConfig|null */
    private $dbalConfig;

    /** @var string|null */
    private $entityDirectory;

    /** @var string|null */
    private $cacheDirectory;

    /** @var LoggerInterface|null */
    private $logger;

    /**
     * @return DatabaseConfig|null
     */
    public function getDatabaseConfig(): ?DatabaseConfig
    {
        return $this->dbalConfig;
    }

    /**
     * @return string|null
     */
    public function getEntityDirectory(): ?string
    {
        return $this->entityDirectory;
    }

    /**
     * @return string|null
     */
    public function getCacheFile(): ?string
    {
        if ($this->cacheDirectory === null) {
            return null;
        }

        return $this->cacheDirectory . DIRECTORY_SEPARATOR . 'cycle-schema.php';
    }

    /**
     * @return LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param string $conn
     * @param string $username
     * @param string $password
     * @return Config
     */
    public static function forDatabase(
        string $conn,
        string $username = '',
        string $password = ''
    ): Config {
        $cfg = new self();

        $cfg->dbalConfig = new DatabaseConfig([
            'default'     => 'db',
            'databases'   => ['db' => ['connection' => 'conn']],
            'connections' => [
                'conn' => [
                    'driver'  => $cfg->getDriver($conn),
                    'options' => [
                        'connection' => $conn,
                        'username'   => $username,
                        'password'   => $password,
                        'reconnect'  => true
                    ]
                ]
            ]
        ]);

        return $cfg;
    }

    /**
     * @param string $directory
     * @return Config
     */
    public function withEntityDirectory(string $directory): Config
    {
        if (!is_dir($directory)) {
            throw new ConfigException("Invalid entity directory `{$directory}`, directory not found");
        }

        $cfg = clone $this;
        $cfg->entityDirectory = $directory;

        return $cfg;
    }

    /**
     * @param string $directory
     * @return Config
     */
    public function withCacheDirectory(string $directory): Config
    {
        if (!is_dir($directory)) {
            throw new ConfigException("Invalid cache directory `{$directory}`, directory not found");
        }

        $cfg = clone $this;
        $cfg->cacheDirectory = $directory;

        return $cfg;
    }

    /**
     * @param LoggerInterface $logger
     * @return Config
     */
    public function withLogger(LoggerInterface $logger): Config
    {
        $cfg = clone $this;
        $cfg->logger = $logger;

        return $cfg;
    }

    /**
     * Config constructor.
     */
    private function __construct()
    {

    }

    /**
     * Isolate.
     */
    private function __clone()
    {
        if ($this->dbalConfig !== null) {
            $this->dbalConfig = clone $this->dbalConfig;
        }
    }

    /**
     * @param string $conn
     * @return string
     */
    private function getDriver(string $conn): string
    {
        switch (true) {
            case strpos($conn, 'sqlite:') === 0:
                return SQLiteDriver::class;
            case strpos($conn, 'mysql:') === 0:
                return MySQLDriver::class;
            case strpos($conn, 'pgsql:') === 0:
                return PostgresDriver::class;
            case strpos($conn, 'sqlsrv:') === 0:
                return SQLServerDriver::class;
        }

        throw new ConfigException("Undefined database driver on `{$conn}`");
    }
}