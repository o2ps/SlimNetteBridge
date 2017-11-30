<?php

declare(strict_types = 1);

namespace OopsTests\SlimNetteBridge;

use Psr\Http;


final class LazyMiddleware
{

	public function __invoke(Http\Message\RequestInterface $request, Http\Message\ResponseInterface $response, array $args): Http\Message\ResponseInterface
	{
		return $response->withStatus(418, "I'm a teapot");
	}


	public function post(Http\Message\RequestInterface $request, Http\Message\ResponseInterface $response, array $args): Http\Message\ResponseInterface
	{
		return $response->withStatus(405, "Don't you try this on me");
	}

}
