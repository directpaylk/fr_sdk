<?php


namespace FriMi\SDK\Payment;


use FriMi\SDK\Exception\InvalidArgumentException;
use FriMi\SDK\Exception\RuntimeException;

abstract class AbstractRequest {
	protected const REQ_TYPE_ID = '000'; // This must be overridden.
	protected const APP_ID = 'FRIMI';
	protected const MODULE_ID = 'WAM';
	protected const DATE_FORMAT = 'd-M-Y h:i:s';
	protected const SENDER_ID = '';

	protected $tid;
	protected $request_id;
	protected $mid; // Merchant ID

	protected $request_timestamp;

	abstract protected function buildDateTime(): string;

	public function buildPayload(): \stdClass {
		$this->selfTest();
		return $this->buildHeader();
	}

	/**
	 * @param string $terminal_id 8 Digit Terminal ID.
	 *
	 * @return self
	 */
	public function setTid(string $terminal_id): self {
		$this->validateNumeric($terminal_id, 'Terminal ID')
		     ->validateLength($terminal_id, 8, 'Terminal ID');
		$this->tid = $terminal_id;
		return $this;
	}

	public function setRequestId(string $request_id): self {
		$this
			->validateNumeric($request_id, 'Request ID')
			->validateMaxLength($request_id, 15, 'Request ID'); // Spec says 14, but example passes 15 as well.
		$this->request_id = $request_id;
		return $this;
	}

	public function setMid(string $merchant_id): self {
		$this->validateNumeric($merchant_id, 'Merchant ID')
			//->validateLength($merchant_id, 10, 'Merchant ID');
			   ->validateMaxLength($merchant_id, 10, 'Merchant ID');
		$this->mid = $merchant_id;
		return $this;
	}

	protected function validateLength(string $value, int $length, string $field_label): self {
		if (\strlen($value) !== $length) {
			throw new InvalidArgumentException(\sprintf('"%s" field must be exactly %d characters long', $field_label, $length));
		}
		return $this;
	}

	protected function validateMaxLength(string $value, int $length, string $field_label): self {
		if (\strlen($value) > $length) {
			throw new InvalidArgumentException(\sprintf('"%s" field must not be more than %d characters long', $field_label, $length));
		}
		return $this;
	}

	protected function validateNumeric(string $value, string $field_label): self {
		if (!\is_numeric($value)) {
			throw new InvalidArgumentException(\sprintf('"%s" field must be a number.', $field_label));
		}
		return $this;
	}

	protected function selfTest(): void {
		$required_fields = [
			'tid' => 'Terminal ID',
			'request_id' => 'Request ID',
			'mid' => 'Merchant ID',
		];

		foreach ($required_fields as $field => $label) {
			if (!isset($this->{$field}) || $this->{$field} === '') {
				throw new RuntimeException(\sprintf('Unable to complete self-test due to "%s" field not being set.', $label));
			}
		}
	}

	protected static function formatDate(int $timestamp): string {
		return \date(static::DATE_FORMAT, $timestamp);
	}

	protected function buildHeader(): \stdClass {
		$request = new \stdClass();
		$request->tid = $this->tid;
		$request->request_id = $this->request_id;
		$request->app_id = static::APP_ID;
		$request->module_id = static::MODULE_ID;
		$request->req_type_id = static::REQ_TYPE_ID;
		$request->date_time = $this->buildDateTime();
		$request->request_timestamp = $request->date_time;
		$request->sender_id = static::SENDER_ID;

		return $request;
	}

	public function checkSafeReturn(string $field, $value): bool {
		return isset($this->{$field}) && $this->{$field} === $value;
	}
}
