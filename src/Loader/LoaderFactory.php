<?php

namespace DbTk\SchemaLoader\Loader;

use DbTk\SchemaLoader\Loader\LoaderInterface;
use DbTk\SchemaLoader\Loader\XmlLoader;

use DbTk\SchemaLoader\Exception\UnsupportedSchemaFileException;

/**
 * @author Igor Mukhin <igor.mukhin@gmail.com>
 */
class LoaderFactory
{
    private static $instance = null;
    protected $loaders = array();

    /**
     * @return LoaderFactory
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$instance->addLoader(new XmlLoader());
        }

        return self::$instance;
    }

    /**
     * @param LoaderInterface $loader
     */
    public function addLoader(LoaderInterface $loader)
    {
        if (in_array($loader, $this->loaders)) {
            return;
        }

        $this->loaders[] = $loader;
    }

    /**
     * Returns loader for specified file.
     *
     * @param  string $filename
     * @return Schema
     */
    public function getLoader($filename)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($filename)) {
                return $loader;
            }
        }

        throw new UnsupportedSchemaFileException($filename);
    }
}
