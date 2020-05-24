<?php

namespace App\Command;

use App\Command\DataClass\Encounter;
use App\Entity\Embeddable\Range;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Merge similar encounters into a single encounter.
 */
final class DataEncountersMergeCommand extends Command
{
    use EncountersTrait;

    protected static $defaultName = 'app:data:encounters:merge';

    /**
     * @var string
     */
    private $dataPath;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * DataEncountersMergeCommand constructor.
     *
     * @param string $dataPath
     * @param SerializerInterface $serializer
     */
    public function __construct(string $dataPath, SerializerInterface $serializer)
    {
        parent::__construct();

        $this->dataPath = $dataPath;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setDescription('Merge similar encounters into a single encounter');
    }

    /**
     * @inheritDoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->text(['Loading data', 'This will take a while...', '']);
        $path = $this->dataPath.'/encounter.csv';
        $this->data = new ArrayCollection($this->loadData($path));

        $this->io->text('Examining data');
        $count = count($this->data);
        $removed = $this->mergeEncounters();

        $this->io->text(['Writing new data to '.$path, 'This will take a while...']);
        $success = $this->writeData($path);

        if ($success) {
            $this->io->success(sprintf('Removed %u encounters (%u%%).', $removed, $removed / $count * 100));

            return 0;
        }

        $this->io->error('Error occurred writing output file.');

        return 1;
    }

    private function mergeEncounters(): int
    {
        $count = count($this->data);
        $removed = 0;
        $progress = $this->io->createProgressBar($count);
        $progress->setFormat('debug');
        $progress->display();
        /** @var Encounter $encounter */
        $encounter = $this->data->first();
        do {
            // Merge similar encounters
            $similar = $this->findSimilar($encounter);
            foreach ($similar as $similarEncounter) {
                $chance = $encounter->getChance() + $similarEncounter->getChance();
                if ($chance > 100) {
                    throw new \Exception(
                        sprintf(
                            'Chance is greater than 100 (%u%%) after adding encounter #%u.',
                            $chance,
                            $similarEncounter->getId()
                        )
                    );
                }
                $encounter->setChance($chance);
                $min = min($encounter->getLevel()->getMin(), $similarEncounter->getLevel()->getMin());
                $max = max($encounter->getLevel()->getMax(), $similarEncounter->getLevel()->getMax());
                $encounter->getLevel()->setMin($min)->setMax($max);
                if ($this->data->removeElement($similarEncounter) === false) {
                    throw new \Exception(sprintf('Could not remove old encounter #%u.', $similarEncounter->getId()));
                }
                $count--;
                $removed++;
            }

            $progress->setMaxSteps($count);
            $progress->advance();
        } while ($encounter = $this->data->next());
        $progress->finish();
        $this->io->newLine(2);

        return $removed;
    }

    /**
     * Find encounters that differ only by chance and level.
     *
     * @param Encounter $encounter
     *
     * @return Encounter[]|ArrayCollection
     */
    private function findSimilar(Encounter $encounter): ArrayCollection
    {
        $similar = $this->data->filter(
            function (Encounter $other) use ($encounter) {
                return $encounter !== $other
                    && $encounter->getVersion() === $other->getVersion()
                    && $encounter->getLocation() === $other->getLocation()
                    && $encounter->getArea() === $other->getArea()
                    && $encounter->getMethod() === $other->getMethod()
                    && $encounter->getSpecies() === $other->getSpecies()
                    && $encounter->getPokemon() === $other->getPokemon()
                    && $encounter->getConditions()->toArray() == $other->getConditions()->toArray()
                    && $encounter->getNote() === $other->getNote();
            }
        );

        return $similar;
    }

    /**
     * Find encounters that differ only by level.
     *
     * @param Encounter $encounter
     *
     * @return ArrayCollection
     */
    private function findSimilarByLevel(Encounter $encounter): ArrayCollection
    {
        $similar = $this->data->filter(
            function (Encounter $other) use ($encounter) {
                return $encounter !== $other
                    && $encounter->getVersion() === $other->getVersion()
                    && $encounter->getLocation() === $other->getLocation()
                    && $encounter->getArea() === $other->getArea()
                    && $encounter->getMethod() === $other->getMethod()
                    && $encounter->getSpecies() === $other->getSpecies()
                    && $encounter->getPokemon() === $other->getPokemon()
                    && $encounter->getChance() === $other->getChance()
                    && $encounter->getConditions()->toArray() == $other->getConditions()->toArray();
            }
        );

        return $similar;
    }

    /**
     * Find encounters that differ only by condition.
     *
     * @param Encounter $encounter
     *
     * @return ArrayCollection
     */
    private function findSimilarByCondition(Encounter $encounter): ArrayCollection
    {
        $similar = $this->data->filter(
            function (Encounter $other) use ($encounter) {
                return $encounter !== $other
                    && $encounter->getVersion() === $other->getVersion()
                    && $encounter->getLocation() === $other->getLocation()
                    && $encounter->getArea() === $other->getArea()
                    && $encounter->getMethod() === $other->getMethod()
                    && $encounter->getSpecies() === $other->getSpecies()
                    && $encounter->getPokemon() === $other->getPokemon()
                    && Range::equals($encounter->getLevel(), $other->getLevel())
                    && $encounter->getChance() === $other->getChance();
            }
        );

        return $similar;
    }
}
