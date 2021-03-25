<?php

/**
 * Cycle ORM CLI bootstrap.
 *
 * @license MIT
 * @author  Vlad Shashkov (AZA)
 */

declare(strict_types=1);

namespace Cycle\Bootstrap;

use Cycle\ORM\Schema;

final class DefaultSchemaHandler implements SchemaHandlerInterface
{
    /**
     * @var Config
     */
    private $cfg;

    /**
     * DefaultSchemaHandler constructor.
     * @param Config $cfg
     */
    public function __construct(Config $cfg)
    {
        $this->cfg = $cfg;
    }

    public function handle(Schema $schema): void
    {
        Bootstrap::storeSchema($this->cfg, $schema);
    }
}
