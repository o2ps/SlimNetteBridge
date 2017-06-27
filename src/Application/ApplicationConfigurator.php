<?php

declare(strict_types = 1);

namespace Oops\SlimNetteBridge\Application;

use Slim\App;


interface ApplicationConfigurator
{

	public function configureApplication(App $application): void;

}
