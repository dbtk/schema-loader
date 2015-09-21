<?php

namespace DbTk\SchemaLoader\Tests;

use DbTk\SchemaLoader\Loader\LoaderFactory;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
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

        $userTable = $loadedSchema->getTable('user');

        // Check columns
        $this->assertEquals($userTable->getColumn('id'), new Column('id', Type::getType('integer'), array('unsigned'=>true, 'autoincrement'=>true)));
        $this->assertEquals($userTable->getColumn('name'), new Column('name', Type::getType('string'), array('length'=>32)));
        $this->assertEquals($userTable->getColumn('about'), new Column('about', Type::getType('text'), array('default'=>null)));
        $this->assertEquals($userTable->getColumn('age'), new Column('age', Type::getType('smallint'), array('unsigned'=>true)));
        $this->assertEquals($userTable->getColumn('projects_done'), new Column('projects_done', Type::getType('integer'), array('unsigned'=>true, 'default'=>0)));
        $this->assertEquals($userTable->getColumn('profile_views'), new Column('profile_views', Type::getType('bigint'), array('unsigned'=>true, 'default'=>0)));
        $this->assertEquals($userTable->getColumn('hourly_rate'), new Column('hourly_rate', Type::getType('decimal'), array('scale'=>6, 'precision'=>2, 'unsigned'=>true, 'default'=>0)));
        $this->assertEquals($userTable->getColumn('total_earned'), new Column('total_earned', Type::getType('float'), array('scale'=>10, 'precision'=>2, 'unsigned'=>true, 'default'=>0)));
        $this->assertEquals($userTable->getColumn('created_at'), new Column('created_at', Type::getType('datetime'), array('default'=>"2000-01-01 12:01:01")));
        $this->assertEquals($userTable->getColumn('born_at'), new Column('born_at', Type::getType('date'), array('default'=>"2000-01-01")));
        $this->assertEquals($userTable->getColumn('workday_starts'), new Column('workday_starts', Type::getType('time'), array('default'=>"9:00")));
        $this->assertEquals($userTable->getColumn('workday_ends'), new Column('workday_ends', Type::getType('time'), array('default'=>"18:00")));
        $this->assertEquals($userTable->getColumn('last_logged_in'), new Column('last_logged_in', Type::getType('datetimetz'), array('default'=>"2000-01-01 12:01:01T0200")));
        $this->assertEquals($userTable->getColumn('enabled'), new Column('enabled', Type::getType('boolean'), array('default'=>false)));

        $this->assertEquals($userTable->getColumn('skills'), new Column('skills', Type::getType('array'), array('notnull'=>false, 'default'=>null)));
        $this->assertEquals($userTable->getColumn('bad_habits'), new Column('bad_habits', Type::getType('simple_array')));
        $this->assertEquals($userTable->getColumn('resume'), new Column('resume', Type::getType('json_array')));
        $this->assertEquals($userTable->getColumn('photo'), new Column('photo', Type::getType('binary')));
        $this->assertEquals($userTable->getColumn('video'), new Column('video', Type::getType('blob')));
        $this->assertEquals($userTable->getColumn('guid'), new Column('guid', Type::getType('guid')));

        // Check indexes
        $this->assertNotNull($userTable->getPrimaryKey());
        $this->assertEquals($userTable->getIndexes(), array(
            'primary'=>new Index('primary', array('id'), false, true),
            'guid'=>new Index('guid', array('guid'), true, false)
        ));

        // Check constraints
        $projectTable = $loadedSchema->getTable('project');
        $projectConstraint = $projectTable->getForeignKeys()['fk_users_project'];
        $this->assertEquals($projectConstraint->getLocalTableName(), 'project');
        $this->assertEquals($projectConstraint->getForeignTableName(), 'user');
        $this->assertEquals($projectConstraint->getLocalColumns(), array('user_id'));
        $this->assertEquals($projectConstraint->getForeignColumns(), array('id'));
        $this->assertEquals($projectConstraint->onUpdate(), 'CASCADE');
        $this->assertEquals($projectConstraint->onDelete(), 'SET NULL');
    }
}
