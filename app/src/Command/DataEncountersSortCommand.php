<?php

namespace App\Command;

use App\Command\DataClass\Encounter;
use App\Repository\EncounterMethodRepository;
use App\Repository\VersionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;

class DataEncountersSortCommand extends Command
{
    use EncountersTrait;

    protected static $defaultName = 'app:data:encounters:sort';

    /**
     * @var string
     */
    private $dataPath;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var VersionRepository
     */
    private $versionsRepo;

    /**
     * @var EncounterMethodRepository
     */
    private $encounterMethodRepo;

    /**
     * DataEncountersSortCommand constructor.
     *
     * @param string $dataPath
     * @param SerializerInterface $serializer
     * @param VersionRepository $versionsRepo
     * @param EncounterMethodRepository $encounterMethodRepo
     */
    public function __construct(
        string $dataPath,
        SerializerInterface $serializer,
        VersionRepository $versionsRepo,
        EncounterMethodRepository $encounterMethodRepo
    ) {
        parent::__construct();

        $this->dataPath = $dataPath;
        $this->serializer = $serializer;
        $this->versionsRepo = $versionsRepo;
        $this->encounterMethodRepo = $encounterMethodRepo;
    }

    protected function configure()
    {
        $this
            ->setDescription('Sort encounters in a useful way.')
            ->addOption(
                'id-spacing',
                null,
                InputOption::VALUE_REQUIRED,
                'The gap in id numbers to allow for adding encounters.',
                1
            );
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
        $this->io->text(['Loading data', 'This will take a while...', '']);
        $path = $this->dataPath.'/encounter.csv';
        $this->data = new ArrayCollection($this->loadData($path));

        $this->sortData();
        $this->resetIds($input->getOption('id-spacing'));

        $this->io->text(['Writing new data to '.$path, 'This will take a while...']);
        $success = $this->writeData(str_replace('.csv', '.new.csv', $path));

        if ($success) {
            $this->io->success('Finished sorting encounters.');

            return 0;
        }

        $this->io->error('Error occurred writing output file.');

        return 1;
    }

    /**
     * Sort the data
     *
     * The sort order is
     * - version (using loaded entities)
     * - location
     * - area
     * - method (using loaded entities)
     * - chance (ascending)
     */
    private function sortData()
    {
        $this->io->text('Sorting data...');
        $progress = $this->io->createProgressBar();
        $progress->setFormat('debug_nomax');
        $progress->display();

        // Load sorting tables
        $versionsOrder = [];
        foreach ($this->versionsRepo->findAll() as $version) {
            $versionsOrder[$version->getSlug()] = $version->getPosition();
        }
        $methodOrder = [];
        foreach ($this->encounterMethodRepo->findAll() as $method) {
            $methodOrder[$method->getSlug()] = $method->getPosition();
        }

        $it = $this->data->getIterator();
        $it->uasort(
            function (Encounter $a, Encounter $b) use ($progress, $versionsOrder, $methodOrder) {
                $progress->advance();
                if ($a->getVersion() !== $b->getVersion()) {
                    return $versionsOrder[$a->getVersion()] - $versionsOrder[$b->getVersion()];
                }
                if ($a->getLocation() !== $b->getLocation()) {
                    return strnatcasecmp($a->getLocation(), $b->getLocation());
                }
                if ($a->getArea() !== $b->getLocation()) {
                    return strnatcasecmp($a->getArea(), $b->getArea());
                }
                if ($a->getMethod() !== $b->getMethod()) {
                    return $methodOrder[$a->getMethod()] - $methodOrder[$b->getMethod()];
                }
                if ($a->getChance() !== $b->getChance()) {
                    return $b->getChance() - $a->getChance();
                }

                return 0;
            }
        );

        $progress->finish();
        $this->io->newLine(2);
        $this->data = new ArrayCollection(iterator_to_array($it));
    }

    private function resetIds(int $spacing)
    {
        $this->io->text('Resetting encounter ids...');
        $progress = $this->io->createProgressBar($this->data->count());
        $progress->setFormat('debug');
        $progress->display();
        $id = 1;
        foreach ($this->data as &$encounter) {
            $encounter->setId($id);

            $id += $spacing;
            $progress->advance();
        }
        unset($encounter, $id);
        $progress->finish();
        $this->io->newLine(2);
    }
}
