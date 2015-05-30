<?php

namespace DbTk\SchemaLoader\Command;

use RuntimeException;

/**
 *  @Cli("schema:load")
 *  @Arg("filename")
 *  @Arg("url")
 */
function SchemaLoadCommand($input, $output)
{
    $url = $input->getArgument('url');
    $filename = $input->getArgument('filename');
    $apply = true;

    $config = new \Doctrine\DBAL\Configuration();
    $connectionParams = array(
        'url' => $url
    );
    echo("Loading `$filename` into `$url`\n");
    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

    $toSchema = new \Doctrine\DBAL\Schema\Schema();
    
    $x = \Doctrine\DBAL\Types\Type::getTypesMap();

    
    $xml = simplexml_load_file($filename);
    
    foreach ($xml->table as $tableNode) {
        //echo ;
        //echo $child->getName() . ": " . $child . "<br>";
        $table = $toSchema->createTable((string)$tableNode['name']);

        /*
        $table->addColumn('id', 'integer', array("unsigned" => true, 'autoincrement' => true));
        $table->setPrimaryKey(array("id"));
        */

        foreach ($tableNode->column as $columnNode) {
            $name = $columnNode['name'];
            $srctype = $columnNode['type'];
            
            $type = trim($srctype, ' )');
            $part = explode('(', $type);
            $options = array();
            switch(strtolower($part[0])) {
                case 'int':
                    $type= 'integer';
                    break;
                    
                case "longtext":
                case "text":
                    $type = 'text';
                    break;
                case "varchar":
                    $type = 'string';
                    $options['length'] = $part[1];
                    break;
                default:
                    throw new RuntimeException("Unsupported type: " . $srctype);
            }
            //echo "Creating $name of $type - $srctype\n";

            $table->addColumn($name, $type, $options);
        }
        
    }

    $platform = $conn->getDatabasePlatform();

    //$queries = $toSchema->toSql($platform); // get queries to create this schema from zero.
    //print_r($queries);

    $sm = $conn->getSchemaManager();
    $fromSchema = $sm->createSchema();
    
    $comparator = new \Doctrine\DBAL\Schema\Comparator();
    $schemaDiff = $comparator->compare($fromSchema, $toSchema);

    //$queries = $schemaDiff->toSql($myPlatform); // queries to get from one to another schema.
    $queries = $schemaDiff->toSaveSql($platform);

    //$queries = $fromSchema->getMigrateToSql($toSchema, $platform);

    //print_r($sql);
    if (count($queries)>0) {
        if (!$apply) {
            echo "CHANGES: The following SQL statements need to be executed to synchronise the schema (use --apply)\n";
            foreach ($queries as $query) {
                echo "SQL: " . $query . "\n";
                //$stmt = $conn->query($query);
            }
        } else {
            foreach ($queries as $query) {
                echo "RUNNING: " . $query . "\n";
                $stmt = $conn->query($query);
            }
        }

    } else {
        echo "No schema changes required\n";
    }
}
