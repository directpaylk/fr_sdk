<?php
declare(strict_types=1);

namespace FriMi\SDK\Handler;

use FriMi\SDK\Exception\HTTPException;
use FriMi\SDK\Exception\InvalidArgumentException;
use FriMi\SDK\Process\Base64Processor;
use FriMi\SDK\Process\JsonProcessor;
use FriMi\SDK\Response\PaymentResponse;
use FriMi\SDK\Response\ResponseInterface;
use FriMi\SDK\Transport\HTTPResponse;

class PaymentRequestHandler extends HandlerAbstract {

	protected function getResponseObject(): ResponseInterface {
		return new PaymentResponse();
	}

	public function execute(): ResponseInterface {
		return $this->initializeRequest();
	}

	protected function getUrl(): string {
		return $this->server->getPaymentRequestUrl();
	}

	protected function composeResponse(HTTPResponse $response_raw): ResponseInterface {
		$data = $this->processRawAPIResponse($response_raw);

		$response = $this->getResponseObject();
		/**
		 * @var PaymentResponse $response
		 */
		$response->setDateTime(strtotime($data->date_time))
		         ->setTid($data->tid)
		         ->setRequestId($data->request_id)
		         ->setReqTypeId($data->req_type_id);

		$body = Base64Processor::decode($data->body);

		try {
			$body = JsonProcessor::decode($body);
		}
		catch (InvalidArgumentException $exception) {
			throw new HTTPException($exception->getMessage(), 2, $exception);
		}

		/*if (!$request->checkSafeReturn('merchant_ref_no', $body->merchant_ref_no)) {
			throw new HTTPException('Merchant Ref No mismatch.', 4);
		}
		$response->setMerchantRefNo($body->merchant_ref_no);*/

		/*
		 * The discount value from server could be different from the discount we
		 * purposed. Since we do not get the currency in the response, we assume the
		 * same currency payment request was made from, and use the same power to adjust
		 * the number to an integer.
		 */
		if (isset($body->discount_amount)) {
			$response->setDiscountAmount($this->request->normalizeCurrency($body->discount_amount)); // This could be different from the discount we purposed.
		}

		if (isset($body->frimi_txn_ref_no)) {
			$response->setFrimiTxnRefNo($body->frimi_txn_ref_no);
		}

		$response->setDescription($body->description);
		$response->setTxnCode($body->txn_code);
		$response->setRequestTimestamp($this->request->getTimestamp());

		return $response;
	}
}
