<?php

declare(strict_types = 1);

namespace OopsTests\SlimNetteBridge;

use Oops\SlimNetteBridge\Application\ApplicationConfigurator;
use Psr\Http;
use Slim\App;


final class LazyConfiguratorMock implements ApplicationConfigurator
{

	public function configureApplication(App $application): void
	{
		$application->get('/whoami', LazyMiddleware::class);
		$application->post('/whoami', LazyMiddleware::class . ':post');
	}

}
