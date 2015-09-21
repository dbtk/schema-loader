<?php

namespace DbTk\SchemaLoader\Loader;

use DbTk\SchemaLoader\Exception\FileNotFoundException;
use DbTk\SchemaLoader\Exception\UnsupportedFieldTypeException;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;

/**
 * @author Igor Mukhin <igor.mukhin@gmail.com>
 */
abstract class BaseLoader
{
    protected $extraOptions = array(
        'default'=>'typeaware',
        'notnull'=>'boolean',
        'length'=>'integer',
        'precision'=>'integer',
        'scale'=>'integer',
        'fixed'=>'boolean',
        'unsigned'=>'boolean',
        'autoincrement'=>'boolean',
        'comment'=>'string'
    );

    /**
     * @param  array $columns
     * @return Column[]
     */
    public function loadColumns($columns)
    {
        return array_map(function ($columnNode) {
            return $this->loadColumn($columnNode);
        }, $columns);
    }

    /**
     * @param  array  $column
     * @return Column
     */
    public function loadColumn($columnNode)
    {
        $type = trim($columnNode['type']);

        if (!Type::hasType($type)) {
            throw new UnsupportedFieldTypeException($type);
        }

        $options = $this->getColumnExtraOptions($type, $columnNode);
        return new Column((string)$columnNode['name'], Type::getType($type), $options);
    }

    /**
     * @param  string $type
     * @param  string $columnNode
     * @return array
     */
    public function getColumnExtraOptions($type, $columnNode)
    {
        $options = array();
        foreach ($this->extraOptions as $optionName=>$optionType) {
            if (isset($columnNode[$optionName])) {
                $options[$optionName] = $this->convertColumnOptionValue((string)$columnNode[$optionName], $type, $optionType);
            }
        }
        return $options;
    }

    /**
     * @param  string $optionValue
     * @param  string $type
     * @param  string $optionType
     * @return mixed
     */
    protected function convertColumnOptionValue($optionValue, $type, $optionType)
    {
        switch ($optionType) {
            case 'boolean':
                return $optionValue === 'true' ? true : false;

            case 'integer':
                return (int)$optionValue;

            case 'string':
                return (string)$optionValue;

            case 'typeaware':
                if ('null' == $optionValue) {
                    return null;
                }

                switch ($type) {
                    case 'tinyint':
                    case 'integer':
                    case 'bigint':
                    case 'float':
                    case 'decimal':
                        return (int)$optionValue;

                    case 'boolean':
                        return $optionValue === 'true' ? true : false;

                    default:
                        return (string)$optionValue;
                }

            default:
                throw new RuntimeException(sprintf("Uknown optionType '%s'", $optionType));
        }
    }

    /**
     * @param  array $indexes
     * @return Index[]
     */
    public function loadIndexes($indexes)
    {
        return array_map(function ($indexNode) {
            return $this->loadIndex($indexNode);
        }, $indexes);
    }

    /**
     * @param  array $indexNode
     * @return Index
     */
    public function loadIndex($indexNode)
    {
        $indexName = (string) $indexNode['name'];
        $columns = explode(',', (string) $indexNode['columns']);
        $isUnique = isset($indexNode['unique']) && 'true' === (string) $indexNode['unique'] ? true : false;
        $isPrimary = isset($indexNode['primary']) && 'true' === (string) $indexNode['primary'] ? true : false;

        return new Index($indexName, $columns, $isUnique, $isPrimary);
    }

    /**
     * @param  array $constraints
     * @return ForeignKeyConstraint[]
     */
    public function loadContstraints($constraints)
    {
        return array_map(function ($constraintNode) {
            return $this->loadContstraint($constraintNode);
        }, $constraints);
    }

    /**
     * @param  array $constraintNode
     * @return ForeignKeyConstraint
     */
    public function loadContstraint($constraintNode)
    {
        $name = null;
        if (isset($constraintNode['name'])) {
            $name = (string) $constraintNode['name'];
        }

        $columns = explode(',', (string) $constraintNode['columns']);
        $foreignTable = (string) $constraintNode['foreign-table'];
        $foreignColumns = explode(',', (string) $constraintNode['foreign-columns']);

        $options = array();
        if (isset($constraintNode['onDelete'])) {
            $options['onDelete'] = (string) $constraintNode['onDelete'];
        }
        if (isset($constraintNode['onUpdate'])) {
            $options['onUpdate'] = (string) $constraintNode['onUpdate'];
        }

        return new ForeignKeyConstraint($columns, $foreignTable, $foreignColumns, $name, $options);
    }
}
