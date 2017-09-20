<?php

declare(strict_types = 1);

namespace Oops\SlimNetteBridge\Container;

use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Psr\Container\ContainerInterface;


final class ContainerAdapter implements ContainerInterface
{

	/**
	 * @var string
	 */
	private $prefix;

	/**
	 * @var Container
	 */
	private $container;


	public function __construct(string $prefix, Container $container)
	{
		$this->prefix = $prefix;
		$this->container = $container;
	}


	public function get($id)
	{
		try {
			return $this->container->getService($this->prefix($id));

		} catch (MissingServiceException $exception) {
			throw new ServiceNotFoundException($exception->getMessage(), $exception->getCode(), $exception);

		} catch (\Exception $exception) {
			throw new ContainerException($exception->getMessage(), $exception->getCode(), $exception);
		}
	}


	public function has($id)
	{
		return $this->container->hasService($this->prefix($id));
	}


	private function prefix(string $id): string
	{
		return $this->prefix . '.' . $id;
	}

}
