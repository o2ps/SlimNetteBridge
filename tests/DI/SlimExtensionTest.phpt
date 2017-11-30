<?php

declare(strict_types = 1);

namespace OopsTests\SlimNetteBridge\DI;

use Nette\Configurator;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Psr\Container\ContainerInterface;
use Psr\Http;
use Slim;
use Tester\Assert;
use Tester\TestCase;


require_once __DIR__ . '/../bootstrap.php';


/**
 * @testCase
 */
class SlimExtensionTest extends TestCase
{

	public function testDefaultServices(): void
	{
		$container = $this->createContainer('default');

		Assert::type(ArrayHash::class, $container->getService('slim.settings'));
		Assert::type(Slim\Interfaces\Http\EnvironmentInterface::class, $container->getService('slim.environment'));
		Assert::type(Http\Message\RequestInterface::class, $container->getService('slim.request'));
		Assert::type(Http\Message\ResponseInterface::class, $container->getService('slim.response'));
		Assert::type(Slim\Router::class, $container->getService('slim.router'));
		Assert::type(Slim\Handlers\Strategies\RequestResponse::class, $container->getService('slim.foundHandler'));
		Assert::type(Slim\Handlers\PhpError::class, $container->getService('slim.phpErrorHandler'));
		Assert::type(Slim\Handlers\Error::class, $container->getService('slim.errorHandler'));
		Assert::type(Slim\Handlers\NotFound::class, $container->getService('slim.notFoundHandler'));
		Assert::type(Slim\Handlers\NotAllowed::class, $container->getService('slim.notAllowedHandler'));
		Assert::type(Slim\CallableResolver::class, $container->getService('slim.callableResolver'));
	}


	public function testContainerAdapter(): void
	{
		$container = $this->createContainer('default');
		$containerAdapter = $container->getService('slim.containerAdapter');

		Assert::type(ContainerInterface::class, $containerAdapter);
		Assert::same($container->getService('slim.settings'), $containerAdapter->get('settings'));
		Assert::same($container->getService('slim.environment'), $containerAdapter->get('environment'));
		Assert::same($container->getService('slim.request'), $containerAdapter->get('request'));
		Assert::same($container->getService('slim.response'), $containerAdapter->get('response'));
		Assert::same($container->getService('slim.router'), $containerAdapter->get('router'));
		Assert::same($container->getService('slim.foundHandler'), $containerAdapter->get('foundHandler'));
		Assert::same($container->getService('slim.phpErrorHandler'), $containerAdapter->get('phpErrorHandler'));
		Assert::same($container->getService('slim.errorHandler'), $containerAdapter->get('errorHandler'));
		Assert::same($container->getService('slim.notFoundHandler'), $containerAdapter->get('notFoundHandler'));
		Assert::same($container->getService('slim.notAllowedHandler'), $containerAdapter->get('notAllowedHandler'));
		Assert::same($container->getService('slim.callableResolver'), $containerAdapter->get('callableResolver'));
	}


	public function testSettings(): void
	{
		$container = $this->createContainer('settings');

		/** @var ArrayHash $settings */
		$settings = $container->getService('slim.settings');
		Assert::same('1.1', $settings['httpVersion']);
		Assert::false($settings['addContentLengthHeader']);
	}


	public function testConfigurators(): void
	{
		$container = $this->createContainer('configurators');

		$request = new Slim\Http\Request(
			'GET',
			new Slim\Http\Uri('http', 'example.com', NULL, '/whoami'),
			new Slim\Http\Headers(),
			[],
			[],
			new Slim\Http\Stream(\fopen('php://input', 'r'))
		);
		$this->assertRequest($container, $request, 418, "I'm a teapot");
	}


	public function testLazyConfigurators(): void
	{
		$container = $this->createContainer('lazyConfigurators');

		$request = new Slim\Http\Request(
			'GET',
			new Slim\Http\Uri('http', 'example.com', NULL, '/whoami'),
			new Slim\Http\Headers(),
			[],
			[],
			new Slim\Http\Stream(\fopen('php://input', 'r'))
		);
		$this->assertRequest($container, $request, 418, "I'm a teapot");

		$request = new Slim\Http\Request(
			'POST',
			new Slim\Http\Uri('http', 'example.com', NULL, '/whoami'),
			new Slim\Http\Headers(),
			[],
			[],
			new Slim\Http\Stream(\fopen('php://input', 'r'))
		);
		$this->assertRequest($container, $request, 405, "Don't you try this on me");
	}


	private function createContainer(string $configFile): Container
	{
		$configurator = new Configurator();
		$configurator->setTempDirectory(\dirname(\TEMP_DIR));
		$configurator->addConfig(__DIR__ . '/' . $configFile . '.neon');

		return $configurator->createContainer();
	}


	private function assertRequest(Container $container, Slim\Http\Request $request, int $statusCode, string $statusReason): void
	{
		/** @var Slim\App $app */
		$app = $container->getByType(Slim\App::class);

		$processedResponse = $app->process($request, new Slim\Http\Response());
		Assert::same($statusCode, $processedResponse->getStatusCode());
		Assert::same($statusReason, $processedResponse->getReasonPhrase());
	}

}


(new SlimExtensionTest())->run();
