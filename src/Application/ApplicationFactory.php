<?php

declare(strict_types = 1);

namespace Oops\SlimNetteBridge\Application;

use Psr\Container\ContainerInterface;
use Slim\App;


class ApplicationFactory
{

	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @var ApplicationConfigurator
	 */
	private $configurator;


	public function __construct(ContainerInterface $container, ApplicationConfigurator $configurator)
	{
		$this->container = $container;
		$this->configurator = $configurator;
	}


	public function createApplication(): App
	{
		$app = new App($this->container);
		$this->configurator->configureApplication($app);
		return $app;
	}

}
