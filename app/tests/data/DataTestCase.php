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
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Yaml\Parser;

/**
 * Base class for testing data.
 */
abstract class DataTestCase extends KernelTestCase
{
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
     * @param array $context
     *
     * @return CommonMarkConverter
     * @throws \ReflectionException
     */
    protected function getMarkdownConverter(?string $versionSlug = null, array $context = []): CommonMarkConverter
    {
        // Build the LinkParser mock
        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        foreach (['error', 'critical', 'alert', 'emergency'] as $logType) {
            $logger->method($logType)->willReturnCallback(
                function ($message) use ($logType, $context) {
                    $this->logToException($logType, $message, $context);
                }
            );
        }
        /** @var UrlGeneratorInterface|MockObject $urlGenerator */
        $urlGenerator = $this->getMockBuilder(UrlGeneratorInterface::class)->getMock();
        $urlGenerator->method('generate')->willReturnArgument(0);
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        if ($versionSlug !== null) {
            /** @var Version|null $version */
            $version = $this->getVersion($versionSlug);
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

        return new CommonMarkConverter($config, $environment);
    }

    /**
     * Callback to turn log calls into exceptions.
     *
     * @param string $type
     * @param string $message
     * @param array $context
     */
    protected function logToException(string $type, string $message, array $context = [])
    {
        $message = sprintf('"%s" logged: "%s"', $type, $message);

        // Add the context to the message.
        if (!empty($context)) {
            $contextStrings = [];
            foreach ($context as $contextItem) {
                $contextStrings[] = sprintf('[%s]', $contextItem);
            }
            $message = implode(' ', $contextStrings).' '.$message;
        }

        throw new \RuntimeException($message);
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
}
