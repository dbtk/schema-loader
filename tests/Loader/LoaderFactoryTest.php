<?php

namespace DbTk\SchemaLoader\Tests;

use DbTk\SchemaLoader\Loader\LoaderFactory;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;

/**
 * Test class for LoaderFactory
 *
 * @author Igor Mukhin <igor.mukhin@gmail.com>
 */
class LoaderFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $filename;

    public function setUp()
    {
        $this->filename = __DIR__ . '/../fixtures/schema.xml';
    }

    public function testGetLoaderReturnsLoaderInterface()
    {
        $loader = LoaderFactory::getInstance()->getLoader($this->filename);
        $this->assertInstanceOf('DbTk\SchemaLoader\Loader\LoaderInterface', $loader);
    }

    public function testLoadSchemaLoadsProperSchema()
    {
        $loader = LoaderFactory::getInstance()->getLoader($this->filename);
        $loadedSchema = $loader->loadSchema($this->filename);
        $this->assertInstanceOf('Doctrine\DBAL\Schema\Schema', $loadedSchema);

        $loadedTable = $loadedSchema->getTable('user');

        $this->assertNotNull($loadedTable->getPrimaryKey());

        $this->assertEquals($loadedTable->getColumn('id'), new Column('id', Type::getType('integer'), array('autoincrement'=>true)));
        $this->assertEquals($loadedTable->getColumn('name'), new Column('name', Type::getType('string'), array('length'=>32)));
        $this->assertEquals($loadedTable->getColumn('display_name'), new Column('display_name', Type::getType('string'), array('length'=>64)));
        $this->assertEquals($loadedTable->getColumn('about'), new Column('about', Type::getType('text')));
        $this->assertEquals($loadedTable->getColumn('created_at'), new Column('created_at', Type::getType('date')));
        $this->assertEquals($loadedTable->getColumn('deleted_at'), new Column('deleted_at', Type::getType('datetime')));
    }
}
