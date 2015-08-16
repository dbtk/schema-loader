<?php

namespace DbTk\SchemaLoader\Loader;

use DbTk\SchemaLoader\Exception\FileNotFoundException;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;

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
            $columns = $this->loadColumns($tableNode->xpath('.//column'));
            $indexes = $this->loadIndexes($tableNode->xpath('.//index'));
            $constraints = $this->loadContstraints($tableNode->xpath('.//constraint'));

            $table = new Table((string)$tableNode['name'], $columns, $indexes, $constraints);

            return $table;
        }, $tables);
    }

}
