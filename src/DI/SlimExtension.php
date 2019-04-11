<?php

declare(strict_types = 1);

namespace Oops\SlimNetteBridge\DI;

use Nette;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\PhpGenerator\PhpLiteral;
use Nette\Schema\Expect;
use Nette\Utils\ArrayHash;
use Oops\SlimNetteBridge\Application\ApplicationFactory;
use Oops\SlimNetteBridge\Application\ChainApplicationConfigurator;
use Oops\SlimNetteBridge\Container\ContainerAdapter;
use Oops\SlimNetteBridge\Http\DefaultResponseFactory;
use Psr\Container\ContainerInterface;
use Psr\Http;
use Slim;


class SlimExtension extends CompilerExtension
{
	/** @var bool */
	private $debugMode;

	public function getConfigSchema(): Nette\Schema\Schema
	{
		return Expect::structure([
			'settings' => Expect::structure([
				'httpVersion' => Expect::string('1.1'),
				'responseChunkSize' => Expect::int(4096),
				'outputBuffering' => Expect::string('append'),
				'determineRouteBeforeAppMiddleware' => Expect::bool(false),
				'displayErrorDetails' => Expect::bool(false),
				'addContentLengthHeader' => Expect::bool(true),
				'routerCacheFile' => Expect::bool(false),
			]),
			'configurators' => Expect::structure([]),
		]);
	}


	public function __construct(bool $debugMode = false)
	{
		$this->debugMode = $debugMode;
	}


	public function loadConfiguration()
	{
		$config = $this->config;
		$builder = $this->getContainerBuilder();

		$config->displayErrorDetails = $this->debugMode;

		$containerAdapter = $builder->addDefinition($this->prefix('containerAdapter'))
			->setType(ContainerInterface::class)
			->setFactory(ContainerAdapter::class, [$this->name])
			->setAutowired(false);

		$chainConfigurator = $builder->addDefinition($this->prefix('configurator'))
			->setType(ChainApplicationConfigurator::class)
			->setAutowired(false);

		foreach ($config->configurators as $configurator) {
			if ( ! ($configurator instanceof Statement)) {
				$configurator = new Statement($configurator);
			}

			$chainConfigurator->addSetup('addConfigurator', [$configurator]);
		}

		$builder->addDefinition($this->prefix('applicationFactory'))
			->setFactory(ApplicationFactory::class, [$containerAdapter, $chainConfigurator])
			->setAutowired(false);

		$builder->addDefinition($this->prefix('application'))
			->setType(Slim\App::class)
			->setFactory($this->prefix('@applicationFactory::createApplication'));

		/**
		 * SERVICES REQUIRED BY SLIM FRAMEWORK
		 * {@see Slim\DefaultServicesProvider}
		 */

		$builder->addDefinition($this->prefix('settings'))
			->setType(ArrayHash::class)
			->setFactory(ArrayHash::class . '::from', [$config->settings])
			->setAutowired(false);

		$builder->addDefinition($this->prefix('environment'))
			->setType(Slim\Interfaces\Http\EnvironmentInterface::class)
			->setFactory(Slim\Http\Environment::class, [new PhpLiteral('$_SERVER')])
			->setAutowired(false);

		$builder->addDefinition($this->prefix('request'))
			->setType(Http\Message\ServerRequestInterface::class)
			->setFactory(Slim\Http\Request::class . '::createFromEnvironment', [$this->prefix('@environment')])
			->setAutowired(false);

		$builder->addDefinition($this->prefix('response'))
			->setType(Http\Message\ResponseInterface::class)
			->setFactory(DefaultResponseFactory::class . '::createResponse', $config->settings->httpVersion)
			->setAutowired(false);

		$builder->addDefinition($this->prefix('router'))
			->setType(Slim\Interfaces\RouterInterface::class)
			->setFactory(Slim\Router::class)
			->addSetup('setCacheFile', [$config->settings->routerCacheFile])
			->addSetup('setContainer', [$containerAdapter])
			->setAutowired(false);

		$builder->addDefinition($this->prefix('foundHandler'))
			->setType(Slim\Interfaces\InvocationStrategyInterface::class)
			->setFactory(Slim\Handlers\Strategies\RequestResponse::class)
			->setAutowired(false);

		$builder->addDefinition($this->prefix('phpErrorHandler'))
			->setFactory(Slim\Handlers\PhpError::class, [$config->settings->displayErrorDetails])
			->setAutowired(false);

		$builder->addDefinition($this->prefix('errorHandler'))
			->setFactory(Slim\Handlers\Error::class, [$config->settings->displayErrorDetails])
			->setAutowired(false);

		$builder->addDefinition($this->prefix('notFoundHandler'))
			->setType(Slim\Handlers\NotFound::class)
			->setAutowired(false);

		$builder->addDefinition($this->prefix('notAllowedHandler'))
			->setType(Slim\Handlers\NotAllowed::class)
			->setAutowired(false);

		$builder->addDefinition($this->prefix('callableResolver'))
			->setType(Slim\Interfaces\CallableResolverInterface::class)
			->setFactory(Slim\CallableResolver::class, [$containerAdapter])
			->setAutowired(false);
	}

}
