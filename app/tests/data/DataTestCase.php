<?php
/**
 * @file DataTestCase.php
 */

namespace App\Tests\data;


use App\CommonMark\Block\Parser\CallableParser;
use App\CommonMark\Block\Renderer\CallableRenderer;
use App\CommonMark\Block\Renderer\TableRenderer;
use App\CommonMark\Extension\PoketoolsBlockExtension;
use App\CommonMark\Extension\PoketoolsInlineExtension;
use App\CommonMark\Extension\PoketoolsTableExtension;
use App\CommonMark\Inline\Parser\CloseBracketInternalLinkParser;
use App\Entity\Version;
use App\Entity\VersionGroup;
use App\Repository\VersionGroupRepository;
use App\Repository\VersionRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Extension\CommonMarkCoreExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Table\TableRenderer as CommonMarkTableRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Base class for testing data.
 */
abstract class DataTestCase extends KernelTestCase
{
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
     * @param string|null $versionSlug
     * @param array $context
     * @param string[]|null $logs
     *   This will be filled with the log output from the converter.
     * @return CommonMarkConverter
     * @throws \ReflectionException
     */
    protected function getMarkdownConverter(
        ?string $versionSlug = null,
        array $context = [],
        ?array &$logs = null
    ): CommonMarkConverter {
        // Build the LinkParser mock
        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        foreach (['error', 'critical', 'alert', 'emergency'] as $logType) {
            $logger->method($logType)->willReturnCallback(
                function ($message) use ($logType, $context, &$logs) {
                    if ($logs === null) {
                        $this->logToException($logType, $message, $context);
                    } else {
                        $logs[] = $this->formatLoggedMessage($logType, $message, $context);
                    }
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
        $extensions = [
            new CommonMarkCoreExtension(),
            new TableExtension(),
            new DisallowedRawHtmlExtension(),
            new PoketoolsTableExtension(new TableRenderer(new CommonMarkTableRenderer())),
            new PoketoolsInlineExtension($linkParser),
            new PoketoolsBlockExtension($callableParser, $callableRenderer),
        ];
        $environment = new Environment();
        foreach ($extensions as $extension) {
            $environment->addExtension($extension);
        }
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
        $message = $this->formatLoggedMessage($type, $message, $context);

        throw new \RuntimeException($message);
    }

    /**
     * @param string $type
     * @param string $message
     * @param array $context
     * @return string
     */
    private function formatLoggedMessage(string $type, string $message, array $context): string
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

        return $message;
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
