<?php

declare(strict_types = 1);

namespace Oops\SlimNetteBridge\Container;

use Psr\Container\NotFoundExceptionInterface;


final class ServiceNotFoundException extends \InvalidArgumentException implements NotFoundExceptionInterface
{
}
