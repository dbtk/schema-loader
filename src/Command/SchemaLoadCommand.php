<?php

namespace DbTk\SchemaLoader\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use DbTk\SchemaLoader\Loader\LoaderFactory;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Comparator;

/**
 * @author Joost Faassen <j.faassen@linkorb.com>
 * @author Igor Mukhin <igor.mukhin@gmail.com>
 */
class SchemaLoadCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('schema:load')
            ->setDescription('Load Alice fixture data into database')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'Schema filename'
            )
            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'Database connection details'
            )
            ->addOption(
                'apply',
                null,
                InputOption::VALUE_NONE,
                'Apply allow you to synchronise schema'
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

        $loader = LoaderFactory::getInstance()->getLoader($filename);
        $toSchema = $loader->loadSchema($filename);

        $config = new Configuration();
        $connectionParams = array(
            'url' => $url
        );
        $connection = DriverManager::getConnection($connectionParams, $config);

        $output->writeln(sprintf(
            'Loading file `%s` into database `%s`',
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
            $output->writeln("No schema changes required");
            return;
        }

        if ($apply) {
            $output->writeln("APPLYING...");
            foreach ($queries as $query) {
                $output->writeln(sprintf(
                    'Running: %s',
                    $query
                ));

                $stmt = $connection->query($query);
            }
        } else {
            $output->writeln("CHANGES: The following SQL statements need to be executed to synchronise the schema (use --apply)");

            foreach ($queries as $query) {
                $output->writeln($query);
            }
        }

    }
}
