<?php

declare(strict_types = 1);

namespace Oops\SlimNetteBridge\Application;

use Slim\App;


final class ChainApplicationConfigurator implements ApplicationConfigurator
{

	/**
	 * @var ApplicationConfigurator[]
	 */
	private $configurators = [];


	public function addConfigurator(ApplicationConfigurator $configurator): void
	{
		$this->configurators[] = $configurator;
	}


	public function configureApplication(App $application): void
	{
		foreach ($this->configurators as $configurator) {
			$configurator->configureApplication($application);
		}
	}

}
