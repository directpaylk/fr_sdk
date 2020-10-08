<?php
declare(strict_types=1);

namespace FriMi\SDK\Handler;

use FriMi\SDK\APIInfo\Server\APIServerInterface;
use FriMi\SDK\Credential\ClientCredentials;
use FriMi\SDK\Exception\HTTPException;
use FriMi\SDK\Exception\InvalidArgumentException;
use FriMi\SDK\Process\JsonProcessor;
use FriMi\SDK\Response\TokenResponse;
use FriMi\SDK\Transport\TransportInterface;

final class TokenRequestHandler {
	private $credentials;
	private $transport;
	private $server;

	public function __construct(APIServerInterface $server, ClientCredentials $credentials, TransportInterface $transport) {
		$this->credentials = $credentials;
		$this->transport = $transport;
		$this->server = $server;
	}

	public function execute(): TokenResponse {
		$headers = [
			'Content-Type' => 'application/x-www-form-urlencoded',
			'Authorization' => 'Basic ' . $this->credentials->getBase64(),
		];
		$fields = http_build_query([
			'grant_type' => 'client_credentials',
		]);

		$response = $this->transport->request('POST', $this->server->getTokenRequestUrl(), $headers, $fields);

		if (!$response->isSuccess()) {
			throw new HTTPException('Failed to validate merchant credentials.', 1);
		}

		try {
			$data = JsonProcessor::decode($response->response_contents);
		}
		catch (InvalidArgumentException $exception) {
			throw new HTTPException('Unexpected response from FriMi back-end.', 3, $exception);
		}

		if (!isset($data->access_token, $data->expires_in)) {
			throw new HTTPException('Unexpected response from FriMi back-end.', 2);
		}

		return new TokenResponse($data->access_token, $data->expires_in);
	}
}
