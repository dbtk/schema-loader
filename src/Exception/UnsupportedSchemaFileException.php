<?php

namespace DbTk\SchemaLoader\Exception;

/**
 * @author Igor Mukhin <igor.mukhin@gmail.com>
 */
class UnsupportedSchemaFileException extends RuntimeException
{
    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        parent::__construct(sprintf(
            "Unsupported schema file '%s'. There is no loader for it.",
            $filename
        ));
    }
}
