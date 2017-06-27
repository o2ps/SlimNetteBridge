<?php

declare(strict_types = 1);

namespace OopsTests\SlimNetteBridge;

use Oops\SlimNetteBridge\Application\ApplicationConfigurator;
use Psr\Http;
use Slim\App;


class ConfiguratorMock implements ApplicationConfigurator
{

	public function configureApplication(App $application): void
	{
		$application->get('/whoami', function (Http\Message\RequestInterface $request, Http\Message\ResponseInterface $response, array $args): Http\Message\ResponseInterface {
			return $response->withStatus(418, "I'm a teapot");
		});
	}

}
