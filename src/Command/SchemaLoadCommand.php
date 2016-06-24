<?php

namespace DbTk\SchemaLoader\Command;

use DbTk\SchemaLoader\Loader\LoaderFactory;
use LinkORB\Component\DatabaseManager\DatabaseManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Exception\ConnectionException;
use PDO;
use RuntimeException;

/**
 * @author Joost Faassen <j.faassen@linkorb.com>
 * @author Igor Mukhin <igor.mukhin@gmail.com>
 */
class SchemaLoadCommand extends Command {
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('schema:load')
            ->setDescription('Load database schema into database')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'Schema filename'
            )
            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'Database connection details. You can use PDO url or just a database name.'
            )
            ->addOption(
                'apply',
                null,
                InputOption::VALUE_NONE,
                'Apply allow you to apply changes'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');
        $filename  = $input->getArgument('filename');
        $apply = $input->getOption('apply');


        $scheme = parse_url($url, PHP_URL_SCHEME);
        $user = parse_url($url, PHP_URL_USER);
        $pass = parse_url($url, PHP_URL_PASS);
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);
        $dbname = substr(parse_url($url, PHP_URL_PATH), 1);
        if (!$port) {
            $port = 3306;
        }
        $dsn = sprintf(
            '%s:host=%s;port=%d',
            $scheme,
            $host,
            $port
        );
        //echo $dsn;exit();
        try {
            $pdo = new PDO($dsn, $user, $pass);
        } catch (\Exception $e) {
            throw new RuntimeException("Can't connect to server with provided address and credentials");
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $dbname . "'");
        if (!$stmt->fetchColumn()) {
            $output->writeln("<info>Creating database</info>");
            $stmt = $pdo->query("CREATE DATABASE " . $dbname . "");
        } else {
            $output->writeln("<error>Database exists...</error>");
        }

        $loader = LoaderFactory::getInstance()->getLoader($filename);
        $toSchema = $loader->loadSchema($filename);

        $config = new Configuration();
        $dbmanager = new DatabaseManager();

        $connectionParams = array(
            'url' => $dbmanager->getUrlByDatabaseName($url)
        );


        $connection = DriverManager::getConnection($connectionParams, $config);
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        $output->writeln(sprintf(
            '<info>Loading file <comment>`%s`</comment> into database <comment>`%s`</comment></info>',
            $filename,
            $url
        ));

        $schemaManager = $connection->getSchemaManager();

        $fromSchema = $schemaManager->createSchema();

        $comparator = new Comparator();
        $schemaDiff = $comparator->compare($fromSchema, $toSchema);

        $platform = $connection->getDatabasePlatform();
        $queries = $schemaDiff->toSaveSql($platform);

        if (!count($queries)) {
            $output->writeln("<info>No schema changes required</info>");
            return;
        }

        if ($apply) {
            $output->writeln("<info>APPLYING...</info>");
            foreach ($queries as $query) {
                $output->writeln(sprintf(
                    '<info>Running: <comment>%s</comment></info>',
                    $query
                ));

                $stmt = $connection->query($query);
            }
        } else {
            $output->writeln("<info>CHANGES: The following SQL statements need to be executed to synchronise the schema (use <comment>--apply</comment>)</info>");

            foreach ($queries as $query) {
                $output->writeln($query);
            }
        }
    }
}
