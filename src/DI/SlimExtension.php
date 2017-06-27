<?php

declare(strict_types = 1);

namespace Oops\SlimNetteBridge\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Statement;
use Nette\PhpGenerator\PhpLiteral;
use Nette\Utils\ArrayHash;
use Oops\SlimNetteBridge\Application\ApplicationFactory;
use Oops\SlimNetteBridge\Application\ChainApplicationConfigurator;
use Oops\SlimNetteBridge\Http\DefaultResponseFactory;
use Psr\Container\ContainerInterface;
use Psr\Http;
use Slim;


class SlimExtension extends CompilerExtension
{

	private $defaults = [
		'settings' => [
			'httpVersion' => '1.1',
			'responseChunkSize' => 4096,
			'outputBuffering' => 'append',
			'determineRouteBeforeAppMiddleware' => FALSE,
			'displayErrorDetails' => NULL,
			'addContentLengthHeader' => TRUE,
			'routerCacheFile' => FALSE,
		],
		'configurators' => [],
	];


	public function __construct(bool $debugMode)
	{
		$this->defaults['settings']['displayErrorDetails'] = $debugMode;
	}


	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		$chainConfigurator = $builder->addDefinition($this->prefix('configurator'))
			->setClass(ChainApplicationConfigurator::class)
			->setAutowired(FALSE);

		foreach ($config['configurators'] as $configurator) {
			if ( ! ($configurator instanceof Statement)) {
				$configurator = new Statement($configurator);
			}

			$chainConfigurator->addSetup('addConfigurator', [$configurator]);
		}

		$builder->addDefinition($this->prefix('applicationFactory'))
			->setClass(ApplicationFactory::class, ['@' . ContainerInterface::class, $chainConfigurator])
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('application'))
			->setClass(Slim\App::class)
			->setFactory($this->prefix('@applicationFactory::createApplication'));


		/**
		 * SERVICES REQUIRED BY SLIM FRAMEWORK
		 * {@see Slim\DefaultServicesProvider}
		 */

		$builder->addDefinition('settings')
			->setClass(ArrayHash::class)
			->setFactory(ArrayHash::class . '::from', [$config['settings']])
			->setAutowired(FALSE);

		$builder->addDefinition('environment')
			->setClass(Slim\Interfaces\Http\EnvironmentInterface::class)
			->setFactory(Slim\Http\Environment::class, [new PhpLiteral('$_SERVER')])
			->setAutowired(FALSE);

		$builder->addDefinition('request')
			->setClass(Http\Message\ServerRequestInterface::class)
			->setFactory(Slim\Http\Request::class . '::createFromEnvironment', ['@environment'])
			->setAutowired(FALSE);

		$builder->addDefinition('response')
			->setClass(Http\Message\ResponseInterface::class)
			->setFactory(DefaultResponseFactory::class . '::createResponse', [$config['settings']['httpVersion']])
			->setAutowired(FALSE);

		$builder->addDefinition('router')
			->setClass(Slim\Interfaces\RouterInterface::class)
			->setFactory(Slim\Router::class)
			->addSetup('setCacheFile', [$config['settings']['routerCacheFile']])
			->addSetup('setContainer', ['@' . ContainerInterface::class])
			->setAutowired(FALSE);

		$builder->addDefinition('foundHandler')
			->setClass(Slim\Interfaces\InvocationStrategyInterface::class)
			->setFactory(Slim\Handlers\Strategies\RequestResponse::class)
			->setAutowired(FALSE);

		$builder->addDefinition('phpErrorHandler')
			->setClass(Slim\Handlers\PhpError::class, [$config['settings']['displayErrorDetails']])
			->setAutowired(FALSE);

		$builder->addDefinition('errorHandler')
			->setClass(Slim\Handlers\Error::class, [$config['settings']['displayErrorDetails']])
			->setAutowired(FALSE);

        $builder->addDefinition('notFoundHandler')
	        ->setClass(Slim\Handlers\NotFound::class)
	        ->setAutowired(FALSE);

        $builder->addDefinition('notAllowedHandler')
	        ->setClass(Slim\Handlers\NotAllowed::class)
	        ->setAutowired(FALSE);

        $builder->addDefinition('callableResolver')
			->setClass(Slim\Interfaces\CallableResolverInterface::class)
			->setFactory(Slim\CallableResolver::class)
	        ->setAutowired(FALSE);
	}

}
