<?php
declare(strict_types=1);

namespace FriMi\SDK\Handler;

use FriMi\SDK\Exception\HTTPException;
use FriMi\SDK\Exception\InvalidArgumentException;
use FriMi\SDK\Exception\RuntimeException;
use FriMi\SDK\Payment\RefundRequest;
use FriMi\SDK\Payment\RequestInterface;
use FriMi\SDK\Process\Base64Processor;
use FriMi\SDK\Process\JsonProcessor;
use FriMi\SDK\Response\ResponseInterface;
use FriMi\SDK\Response\RefundResponse;
use FriMi\SDK\Transport\HTTPResponse;

class RefundRequestHandler extends HandlerAbstract {

	public function execute(): ResponseInterface {
		return $this->initializeRequest();
	}

	protected function getUrl(): string {
		return $this->server->getPaymentReversalUrl();
	}

	public function setPaymentRequestObject(RequestInterface $request): void {
		if (!($request instanceof RefundRequest)) {
			throw new InvalidArgumentException('Only RefundRequest objects are allowed.');
		}

		if (!$this->server->isRefundAllowed($request->getOriginalRequestTimestamp())) {
			throw new RuntimeException('Refund for this transaction is no longer allowed because the cut-off time has passed.');
		}

		$this->request = $request;
	}

	protected function getResponseObject(): ResponseInterface {
		return new RefundResponse();
	}

	protected function composeResponse(HTTPResponse $raw_response): ResponseInterface {
		$data = $this->processRawAPIResponse($raw_response);

		$response = $this->getResponseObject();

		$body = Base64Processor::decode($data->body);
		try {
			$body = JsonProcessor::decode($body);
		}
		catch (InvalidArgumentException $exception) {
			throw new HTTPException($exception->getMessage(), 2, $exception);
		}

		/**
		 * @var RefundResponse $response
		 */
		$response->setDescription($body->description);
		$response->setTxnCode($body->txn_code);

		/**
		 * @var RefundResponse $response
		 */

		return $response;
	}
}
