<?php
declare(strict_types=1);

namespace FriMi\SDK\Handler;

use FriMi\SDK\APIInfo\Server\APIServerInterface;
use FriMi\SDK\Exception\HTTPException;
use FriMi\SDK\Exception\InvalidArgumentException;
use FriMi\SDK\Payment\RequestInterface;
use FriMi\SDK\Process\JsonProcessor;
use FriMi\SDK\Response\ResponseInterface;
use FriMi\SDK\Response\TokenResponse;
use FriMi\SDK\Transport\HTTPResponse;
use FriMi\SDK\Transport\TransportInterface;

abstract class HandlerAbstract {
	protected $server;
	protected $token;
	protected $transport;

	/**
	 * @var \FriMi\SDK\Payment\PaymentRequest
	 */
	protected $request;

	abstract public function execute(): ResponseInterface;
	abstract protected function getUrl(): string;
	abstract protected function composeResponse(HTTPResponse $response): ResponseInterface;

	public function __construct(APIServerInterface $server, TokenResponse $response, TransportInterface $transport) {
		$this->server = $server;
		$this->token = $response;
		$this->transport = $transport;
	}

	public function setPaymentRequestObject(RequestInterface $request): void {
		$this->request = $request;
	}

	protected function initializeRequest(): ResponseInterface {
		$payload = $this->request->buildPayload();
		$headers = [
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer  ' . $this->token->getToken(),
		];

		$fields = JsonProcessor::encode($payload);

		$response = $this->transport->request('POST', $this->getUrl(), $headers, $fields);

		\Log::info(json_encode($response));

		if (!$response->isSuccess()) {
			throw new HTTPException('Payment request authorization failed.', 1);
		}

		return $this->composeResponse($response);
	}

	protected function processRawAPIResponse(HTTPResponse $response): \stdClass {
		try {
			$data = JsonProcessor::decode($response->response_contents);
		}
		catch (InvalidArgumentException $exception) {
			throw new HTTPException($exception->getMessage(), 1, $exception);
		}

		if (!isset($data->tid, $data->request_id, $data->body, $data->req_type_id, $data->date_time)) {
			throw new HTTPException('Unexpected response from FriMi back-end.', 6);
		}

		$request = $this->request;

		if (!$request->checkSafeReturn('request_id', $data->request_id)) {
			throw new HTTPException('Payment request ID mismatch.', 3);
		}

		if (!$request->checkSafeReturn('tid', $data->tid)) {
			throw new HTTPException('Terminal ID mismatch.', 4);
		}
		return $data;
	}
}
