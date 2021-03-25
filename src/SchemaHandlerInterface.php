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

interface SchemaHandlerInterface
{
    /**
     * @param Schema $schema
     */
    public function handle(Schema $schema): void;
}
