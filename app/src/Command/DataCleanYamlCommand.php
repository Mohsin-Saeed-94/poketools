<?php

namespace App\Command;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\Drivers\Destination\YamlDestinationDriver;
use DragoonBoots\A2B\Drivers\Source\YamlSourceDriver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataCleanYamlCommand extends Command
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
     * DataCleanYamlCommand constructor.
     *
     * @param YamlSourceDriver $yamlSourceDriver
     * @param YamlDestinationDriver $yamlDestinationDriver
     */
    public function __construct(
        YamlSourceDriver $yamlSourceDriver,
        YamlDestinationDriver $yamlDestinationDriver
    ) {
        parent::__construct();

        $this->yamlSourceDriver = $yamlSourceDriver;
        $this->yamlDestinationDriver = $yamlDestinationDriver;
    }

    protected function configure()
    {
        $this
            ->setDescription(
                'Clean a YAML data directory, normalizing data presentation and generating anchors/references'
            )
            ->addArgument('path', InputArgument::REQUIRED, 'YAML directory path')
            ->addOption(
                'id',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                '(Required) Id field in the YAML files'
            )
            ->addOption('no-refs', null, InputOption::VALUE_NONE, "Don't generate anchors/references");
    }

    /**
     * @inheritDoc
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        if (!$input->getOption('id')) {
            $io = new SymfonyStyle($input, $output);
            $ids = $io->ask(
                'Enter id field names separated by commas.',
                null,
                function ($ids) {
                    if (empty($ids)) {
                        throw new \RuntimeException('You must enter at least one id field.');
                    }

                    $ids = explode(',', $ids);
                    array_map('trim', $ids);

                    return $ids;
                }
            );

            $input->setOption('id', $ids);
        }
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
        $path = realpath($input->getArgument('path'));
        $useRefs = !$input->getOption('no-refs');

        // Pretend this is a migration so the Yaml Driver can process it.
        $driverUri = sprintf('yaml://%s', $path);
        $idFields = [];
        foreach ($input->getOption('id') as $id) {
            $idFields[] = new IdField(['name' => $id, 'type' => 'string']);
        }
        $definition = new DataMigration(
            [
                'name' => 'Clean YAML',
                'source' => $driverUri,
                'destination' => $driverUri,
                'sourceIds' => $idFields,
                'destinationIds' => $idFields,
            ]
        );
        $this->yamlSourceDriver->configure($definition);
        $this->yamlDestinationDriver->configure($definition);
        $this->yamlDestinationDriver->setOption('refs', $useRefs);

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
    }
}
