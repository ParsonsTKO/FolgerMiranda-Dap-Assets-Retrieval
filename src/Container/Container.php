<?php declare(strict_types=1);

namespace App\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\RegisterEnvVarProcessorsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag;

final class Container implements ContainerInterface
{
    /**
     * @var string
     */
    private $environment;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param string $environment
     * @throws \Exception
     */
    public function __construct(string $environment)
    {
        if (!in_array($environment, ['dev', 'prod'])) {
            throw new \Exception(sprintf(
                'Invalid environment "%s". Available environments are "dev" and "prod".',
                $environment
            ));
        }

        $this->environment = $environment;
    }

    /**
     * Build container
     */
    public function build() : void
    {
        $containerBuilder = new ContainerBuilder();

        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('config.yaml');

        $containerBuilder->compile(true);

        $this->container = $containerBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $this->assertContainerBuilt();

        return $this->container->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        $this->assertContainerBuilt();

        return $this->container->has($id);
    }

    /**
     * @throws \Exception
     */
    private function assertContainerBuilt() : void
    {
        if (null === $this->container) {
            throw new \Exception('Container has not be built, call $container->build before use.');
        }
    }
}
