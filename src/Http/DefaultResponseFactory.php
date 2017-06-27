<?php

declare(strict_types = 1);

namespace Oops\SlimNetteBridge\Http;

use Slim\Http\Headers;
use Slim\Http\Response;


/**
 * @internal used by {@see Oops\SlimExtension\DI\SlimExtension}
 */
class DefaultResponseFactory
{

	public static function createResponse(string $protocolVersion): Response
	{
		$headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
		$response = new Response(200, $headers);

		return $response->withProtocolVersion($protocolVersion);
	}

}
