<?php

namespace DbTk\SchemaLoader\Loader;

use DbTk\SchemaLoader\Exception\FileNotFoundException;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Column;

/**
 * @author Joost Faassen <j.faassen@linkorb.com>
 * @author Igor Mukhin <igor.mukhin@gmail.com>
 */
class XmlLoader extends BaseLoader implements LoaderInterface
{

    /**
     * {@inheritdoc}
     */
    public function supports($filename)
    {
        return ('.xml' == strtolower(substr($filename, -4)));
    }

    /**
     * {@inheritdoc}
     *
     * @throws FileNotFoundException
     */
    public function loadSchema($filename)
    {
        if (!file_exists($filename)) {
            throw new FileNotFoundException($filename);
        }

        $xml = simplexml_load_file($filename);

        $tables = $this->loadTables($xml->xpath('//table'));
        return new Schema($tables);
    }

    /**
     * @param  array $tables
     * @return Table[]
     */
    public function loadTables($tables)
    {
        return array_map(function($tableNode){
            $columns = $this->loadColumns($tableNode->xpath('//column'));
            $table = new Table((string)$tableNode['name'], $columns);

            if ((string) $tableNode['primaryKey']) {
                $table->setPrimaryKey(
                    [(string) $tableNode['primaryKey']]
                );
            }

            return $table;
        }, $tables);
    }

    /**
     * @param  array $columns
     * @return Column[]
     */
    public function loadColumns($columns)
    {
        return array_map(function($columnNode){
            // var_dump($columnNode);
            return $this->loadColumn($columnNode);
        }, $columns);
    }

    /**
     * @param  array  $column
     * @return Column
     */
    public function loadColumn($columnNode)
    {
        // @todo Refactor that line - its ugly little bit
        $extra = '';
        $type = trim($columnNode['type'], ' )');
        if (false !== strpos($type, '(')) {
            list($type, $extra) = explode('(', $type);
        }

        $column = new Column($columnNode['name'], $this->getType($type));
        $this->applyType($column, $type, $extra);

        if (strtolower($columnNode['autoincrement']) == 'true') {
            $column->setAutoincrement(true);
        }

        return $column;
    }
}
