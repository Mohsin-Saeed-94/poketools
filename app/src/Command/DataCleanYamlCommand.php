<?php

namespace App\Command;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationManagerInterface;
use DragoonBoots\A2B\Drivers\Destination\YamlDestinationDriver;
use DragoonBoots\A2B\Drivers\Source\YamlSourceDriver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DataCleanYamlCommand extends Command
{
    protected static $defaultName = 'app:data:clean-yaml';

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var YamlSourceDriver
     */
    private $yamlSourceDriver;

    /**
     * @var YamlDestinationDriver
     */
    private $yamlDestinationDriver;

    /**
     * @var DataMigrationManagerInterface
     */
    private $migrationManager;

    /**
     * DataCleanYamlCommand constructor.
     *
     * @param YamlSourceDriver $yamlSourceDriver
     * @param YamlDestinationDriver $yamlDestinationDriver
     * @param DataMigrationManagerInterface $migrationManager
     */
    public function __construct(
        YamlSourceDriver $yamlSourceDriver,
        YamlDestinationDriver $yamlDestinationDriver,
        DataMigrationManagerInterface $migrationManager
    ) {
        parent::__construct();

        $this->yamlSourceDriver = $yamlSourceDriver;
        $this->yamlDestinationDriver = $yamlDestinationDriver;
        $this->migrationManager = $migrationManager;
    }

    protected function configure()
    {
        $this
            ->setDescription(
                'Clean a YAML data directory, normalizing data presentation and generating anchors/references'
            )
            ->addArgument('migration', InputArgument::REQUIRED, 'Migration class with the YAML as the destination');
    }

    /**
     * @inheritDoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->io = new SymfonyStyle($input, $output);
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migration = $this->migrationManager->getMigration($input->getArgument('migration'));
        $destinationDefinition = $migration->getDefinition();
        $path = $destinationDefinition->getDestination();
        if ($destinationDefinition->getDestinationDriver() !== YamlDestinationDriver::class) {
            $this->io->error(
                sprintf(
                    'The migration "%s" does not use the YAML driver.',
                    $input->getArgument('migration')
                )
            );
        }
        $path = realpath($path);
        if ($path === false) {
            $this->io->error(sprintf('The path "%s" given in the migration is unusable.', $path));
        }

        // Pretend this is a migration so the Yaml Driver can process it.
        $idFields = $destinationDefinition->getDestinationIds();
        $sourceDefinition = new DataMigration(
            [
                'name' => 'Clean YAML',
                'source' => $path,
                'sourceDriver' => YamlDestinationDriver::class,
                'destination' => $path,
                'destinationDriver' => YamlDestinationDriver::class,
                'sourceIds' => $idFields,
                'destinationIds' => $idFields,
            ]
        );
        $this->yamlSourceDriver->configure($sourceDefinition);
        $this->yamlDestinationDriver->configure($sourceDefinition);
        $migration->configureDestination($this->yamlDestinationDriver);

        // Run the data through the Yaml Driver
        $it = $this->yamlSourceDriver->getIterator();
        $progress = $this->io->createProgressBar(count($this->yamlSourceDriver));
        $progress->setFormat('debug');
        foreach ($it as $row) {
            $this->yamlDestinationDriver->write($row);
            $progress->advance();
        }
        $progress->finish();
        $this->io->newLine(2);
        $this->io->success('Finished cleaning YAML files.');

        return 0;
    }
}
