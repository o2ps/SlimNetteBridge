<?php
declare(strict_types = 1);

namespace OopsTests\SlimNetteBridge\DI;

use Nette\Configurator;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
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

		Assert::type(ArrayHash::class, $container->getService('settings'));
		Assert::type(Slim\Interfaces\Http\EnvironmentInterface::class, $container->getService('environment'));
		Assert::type(Http\Message\RequestInterface::class, $container->getService('request'));
		Assert::type(Http\Message\ResponseInterface::class, $container->getService('response'));
		Assert::type(Slim\Router::class, $container->getService('router'));
		Assert::type(Slim\Handlers\Strategies\RequestResponse::class, $container->getService('foundHandler'));
		Assert::type(Slim\Handlers\PhpError::class, $container->getService('phpErrorHandler'));
		Assert::type(Slim\Handlers\Error::class, $container->getService('errorHandler'));
		Assert::type(Slim\Handlers\NotFound::class, $container->getService('notFoundHandler'));
		Assert::type(Slim\Handlers\NotAllowed::class, $container->getService('notAllowedHandler'));
		Assert::type(Slim\CallableResolver::class, $container->getService('callableResolver'));
	}


	public function testSettings(): void
	{
		$container = $this->createContainer('settings');

		/** @var ArrayHash $settings */
		$settings = $container->getService('settings');
		Assert::same('1.1', $settings['httpVersion']);
		Assert::false($settings['addContentLengthHeader']);
	}


	public function testConfigurators(): void
	{
		$container = $this->createContainer('configurators');

		/** @var Slim\App $app */
		$app = $container->getByType(Slim\App::class);
		$request = new Slim\Http\Request(
			'GET',
			new Slim\Http\Uri('http', 'example.com', NULL, '/whoami'),
			new Slim\Http\Headers(),
			[],
			[],
			new Slim\Http\Stream(\fopen('php://input', 'r'))
		);;

		$processedResponse = $app->process($request, new Slim\Http\Response());
		Assert::same(418, $processedResponse->getStatusCode());
		Assert::same("I'm a teapot", $processedResponse->getReasonPhrase());
	}


	private function createContainer(string $configFile): Container
	{
		$configurator = new Configurator();
		$configurator->setTempDirectory(\dirname(\TEMP_DIR));
		$configurator->addConfig(__DIR__ . '/' . $configFile . '.neon');

		return $configurator->createContainer();
	}

}


(new SlimExtensionTest())->run();
