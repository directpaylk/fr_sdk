<?php
declare(strict_types=1);

namespace FriMi\SDK\Payment;


use FriMi\SDK\Exception\RuntimeException;
use FriMi\SDK\Process\Base64Processor;
use FriMi\SDK\Process\JsonProcessor;

class RefundRequest extends AbstractRequest implements RequestInterface {
	protected const REQ_TYPE_ID = '007';
	private const ORIGINAL_REQUEST_DATE_FORMAT = 'Y-m-d';

	private $original_request_id;
	private $original_request_timestamp;

	public function buildPayload(): \stdClass {
		$payload = parent::buildPayload();
		$payload->body = $this->buildBody();
		$payload->body = Base64Processor::encode(JsonProcessor::encode($payload->body));

		return $payload;
	}

	public function selfTest(): void {
		parent::selfTest();
		$required_fields = [
			'original_request_id' => 'Original Request ID',
			'original_request_timestamp' => 'Original Request Time',
		];

		foreach ($required_fields as $field => $label) {
			if (!isset($this->{$field}) || $this->{$field} === '') {
				throw new RuntimeException(\sprintf('Unable to complete self-test due to "%s" field not being set.', $label));
			}
		}
	}

	private function buildBody(): \stdClass {
		$body = new \stdClass();
		$body->reversal_req_ID = $this->original_request_id;
		$body->date = \date(static::ORIGINAL_REQUEST_DATE_FORMAT, $this->original_request_timestamp);
		$body->mid = $this->mid;

		return $body;
	}

	protected function buildDateTime(): string {
		$this->request_timestamp = time();
		return static::formatDate($this->request_timestamp);
	}

	public function setOriginalRequestId(string $request_id): self {
		$this->original_request_id = $request_id;
		return $this;
	}

	public function setOriginalRequestTimestamp(int $timestamp): self {
		$this->original_request_timestamp = $timestamp;
		return $this;
	}

	public function getOriginalRequestTimestamp(): int {
		return $this->original_request_timestamp;
	}
}
