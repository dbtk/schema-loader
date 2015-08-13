<?php

namespace DbTk\SchemaLoader\Loader;

use Doctrine\DBAL\Schema\Schema;

/**
 * @author Igor Mukhin <igor.mukhin@gmail.com>
 */
interface LoaderInterface
{
    /**
     * Check if loader supports file.
     *
     * @param  string $filename
     * @return boolean
     */
    public function supports($filename);

    /**
     * Load schema from file.
     *
     * @param  string $filename
     * @return Schema
     */
    public function loadSchema($filename);
}
