<?php
/**
 * @file DataTestCase.php
 */

namespace App\Tests\data;


use App\CommonMark\Block\Parser\CallableParser;
use App\CommonMark\Block\Renderer\CallableRenderer;
use App\CommonMark\Extension\PoketoolsCommonMarkExtension;
use App\CommonMark\Inline\Parser\CloseBracketInternalLinkParser;
use App\Entity\Version;
use App\Entity\VersionGroup;
use App\Repository\VersionGroupRepository;
use App\Repository\VersionRepository;
use Doctrine\ORM\EntityManagerInterface;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Yaml\Parser;

/**
 * Base class for testing data.
 */
abstract class DataTestCase extends KernelTestCase
{
    protected const SUPPORTED_VERSIONS = [
        'red',
        'blue',
        'yellow',
        'gold',
        'silver',
        'crystal',
        'ruby',
        'sapphire',
        'emerald',
        'colosseum',
        'xd',
        'firered',
        'leafgreen',
        'diamond',
        'pearl',
        'platinum',
        'heartgold',
        'soulsilver',
        'black',
        'white',
        'black-2',
        'white-2',
        'x',
        'y',
        'omega-ruby',
        'alpha-sapphire',
        'sun',
        'moon',
        'ultra-sun',
        'ultra-moon',
    ];
    private const BASE_DIR_DATA = __DIR__.'/../../resources/data/';
    private const BASE_DIR_SCHEMA = __DIR__.'/../../resources/schema';

    /**
     * Assert the data follows the schema.
     *
     * @param string $name
     * @param array $data
     */
    protected static function assertDataSchema(string $name, array $data): void
    {
        $validator = new Validator();
        $validator->validate(
            $data,
            (object)['$ref' => 'file://'.realpath(sprintf('%s/%s.json', self::BASE_DIR_SCHEMA, $name))],
            Constraint::CHECK_MODE_TYPE_CAST | Constraint::CHECK_MODE_VALIDATE_SCHEMA
        );
        self::assertTrue($validator->isValid(), self::buildSchemaErrorMessage($validator->getErrors()));
    }

    /**
     * @param array[] $errors
     *
     * @return string
     */
    private static function buildSchemaErrorMessage(array $errors): string
    {
        $messages = ['Data does not follow schema:'];
        foreach ($errors as $error) {
            $messages[] = sprintf('[%s] %s', $error['property'], $error['message']);
        }

        return implode("\n", $messages);
    }

    /**
     * @param string $directory
     *
     * @return Finder
     */
    protected function getFinderForDirectory(string $directory): Finder
    {
        $directory = self::BASE_DIR_DATA.trim($directory, '/');
        $finder = new Finder();
        $finder->in($directory)
            ->sortByName();

        return $finder;
    }

    /**
     * Build a proper data provider generator from the given Finder.
     *
     * The contents of the file will be sent as the first parameter.
     *
     * @param Finder $finder
     *
     * @return \Generator
     */
    protected function buildDataProvider(Finder $finder): \Generator
    {
        foreach ($finder as $fileInfo) {
            yield $fileInfo->getFilename() => [$fileInfo->getContents()];
        }
    }

    /**
     * @param string $versionSlug
     *
     * @return Version
     */
    protected function getVersion(string $versionSlug): Version
    {
        /** @var VersionRepository $versionRepo */
        $versionRepo = $this->getContainer()
            ->get('doctrine')
            ->getManagerForClass(Version::class)
            ->getRepository(Version::class);
        $version = $versionRepo->findOneBy(['slug' => $versionSlug]);

        return $version;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        static $container = null;
        if ($container === null) {
            $container = $this->getKernel()->getContainer();
        }

        return $container;
    }

    /**
     * @return KernelInterface
     */
    protected function getKernel(): KernelInterface
    {
        static $kernel = null;
        if ($kernel === null) {
            $kernel = self::bootKernel();
        }

        return $kernel;
    }

    /**
     * @param string $yaml
     *
     * @return array
     */
    protected function parseYaml(string $yaml): array
    {
        $data = $this->getYamlParser()->parse($yaml);
        self::assertNotEmpty($data, 'Data is empty');

        return $data;
    }

    /**
     * @return Parser
     */
    protected function getYamlParser(): Parser
    {
        static $parser = null;

        if (!isset($parser)) {
            $parser = $this->getContainer()->get('test.yaml.parser');
        }

        return $parser;
    }

    /**
     * @param string $versionGroupSlug
     *
     * @return VersionGroup
     */
    protected function getVersionGroup(string $versionGroupSlug): VersionGroup
    {
        /** @var VersionGroupRepository $versionGroupRepo */
        $versionGroupRepo = $this->getContainer()
            ->get('doctrine')
            ->getManagerForClass(VersionGroup::class)
            ->getRepository(VersionGroup::class);
        $versionGroup = $versionGroupRepo->findOneBy(['slug' => $versionGroupSlug]);

        return $versionGroup;
    }

    /**
     * @param string|null $versionSlug
     *
     * @return CommonMarkConverter
     * @throws \ReflectionException
     */
    protected function getMarkdownConverter(string $versionSlug = null): CommonMarkConverter
    {
        /** @var CommonMarkConverter[] $converters */
        static $converters = [];

        if (!isset($converters[$versionSlug])) {
            // Build the LinkParser mock
            /** @var LoggerInterface|MockObject $logger */
            $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
            $logger->expects($this->never())->method('warning');
            /** @var UrlGeneratorInterface|MockObject $urlGenerator */
            $urlGenerator = $this->getMockBuilder(UrlGeneratorInterface::class)->getMock();
            $urlGenerator->method('generate')->willReturnArgument(0);
            /** @var EntityManagerInterface $em */
            $em = $this->getContainer()->get('doctrine')->getManager();
            if ($versionSlug !== null) {
                /** @var Version|null $version */
                $version = $em->getRepository(Version::class)->findOneBy(['slug' => $versionSlug]);
            } else {
                $version = null;
            }
            $linkParser = new CloseBracketInternalLinkParser($logger, $urlGenerator, $em, $version);

            // Build the CallableParser mock
            $jsonEncoder = $this->getContainer()->get('test.serializer.encoder.json');
            $callableParser = new CallableParser($version, $logger, $jsonEncoder);

            // Build the CallableRenderer stub
            /** @var CallableRenderer|MockObject $callableRenderer */
            $callableRenderer = $this->createMock(CallableRenderer::class);
            $callableRenderer->method('render')->willReturn('');

            // Put it all together
            $extension = new PoketoolsCommonMarkExtension($linkParser, $callableParser, $callableRenderer);
            $environment = new Environment();
            $environment->addExtension($extension);
            $config = $this->getContainer()->getParameter('commonmark_config');
            $converters[$versionSlug] = new CommonMarkConverter($config, $environment);
        }

        return $converters[$versionSlug];
    }
}
