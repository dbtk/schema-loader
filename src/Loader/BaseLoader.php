<?php

namespace DbTk\SchemaLoader\Loader;

use DbTk\SchemaLoader\Exception\FileNotFoundException;
use DbTk\SchemaLoader\Exception\UnsupportedFieldTypeException;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Column;

/**
 * @author Igor Mukhin <igor.mukhin@gmail.com>
 */
abstract class BaseLoader
{
    protected $typesMap = array(
        'bigint'=>'bigint',
        'tinyint'=>'boolean',
        'integer'=>'integer',
        'int'=>'integer',
        'longtext'=>'text',
        'text'=>'text',
        'varchar'=>'string',
        'char'=>'string',
        'decimal'=>'decimal',
        'float'=>'float',
        'datetime'=>'datetime',
        'date'=>'date'
    );

    /**
     * @param  string $type
     * @return boolean
     */
    public function isTypeSupported($type)
    {
        return isset($this->typesMap[$type]);
    }

    /**
     * Apply extra options for special types.
     *
     * @param  Column $column
     * @param  string $extra
     */
    public function applyType(Column $column, $type, $extra)
    {
        switch(strtolower($type)) {
            case "varchar":
            case "char":
                $column
                    ->setLength($extra)
                ;
                break;

            case "decimal":
            case "float":
                list($precision, $scale) = explode(',', $extra);
                $column
                    ->setPrecision($precision)
                    ->setScale($scale)
                ;
                break;
        }
    }

    /**
     * @param  string $type
     * @return Type
     *
     * @throws UnsupportedFieldTypeException
     */
    public function getType($type)
    {
        if (!$this->isTypeSupported($type)) {
            throw new UnsupportedFieldTypeException($srctype);
        }

        $actialType = $this->typesMap[$type];

        return Type::getType($actialType);
    }
}
