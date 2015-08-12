<?php

namespace DbTk\SchemaLoader\Exception;

/**
 * @author Igor Mukhin <igor.mukhin@gmail.com>
 */
class UnsupportedFieldTypeException extends RuntimeException
{
    /**
     * @param string $type
     */
    public function __construct($type)
    {
        parent::__construct(sprintf(
            "Unsupported field type '%s'",
            $type
        ));
    }
}
