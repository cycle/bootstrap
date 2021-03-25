<?php

/**
 * Cycle ORM CLI bootstrap.
 *
 * @license MIT
 * @author  Vlad Shashkov (AZA)
 */

declare(strict_types=1);

namespace Cycle\Bootstrap;

use Cycle\ORM\SchemaInterface;

interface SchemaHandlerInterface
{
    /**
     * @param SchemaInterface $schema
     */
    public function handle(SchemaInterface $schema): void;
}
