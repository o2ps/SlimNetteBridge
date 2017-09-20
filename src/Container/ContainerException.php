<?php

declare(strict_types = 1);

namespace Oops\SlimNetteBridge\Container;

use Psr\Container\ContainerExceptionInterface;


final class ContainerException extends \RuntimeException implements ContainerExceptionInterface
{
}
