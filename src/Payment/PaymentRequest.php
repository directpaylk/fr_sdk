<?php
declare(strict_types=1);

namespace FriMi\SDK\Payment;


use FriMi\SDK\Exception\InvalidArgumentException;
use FriMi\SDK\Exception\RuntimeException;
use FriMi\SDK\Process\Base64Processor;
use FriMi\SDK\Process\JsonProcessor;

class PaymentRequest extends AbstractRequest implements RequestInterface {
	protected const REQ_TYPE_ID = '002';

	protected $frimi_id = '';
	protected $merchant_ref_no = '';

	/**
	 * @var int
	 */
	protected $txn_amount;

	/**
	 * @var int
	 */
	protected $txn_currency_code;
	protected $mobile_no = '';

	/**
	 * @var int
	 */
	protected $discount_amount = 0;
	protected $description = '';

	/**
	 * Pairs of currencies and their fraction pointers. E.g LKR uses 2 decimal points.
	 * @var int[]
	 */
	protected $currencies = [
		144 => 2,
	];

	public function setMerchantRefNo(string $merchant_ref_no): self {
		$this->validateMaxLength($merchant_ref_no, 15, 'Merchant Reference No');
		$this->merchant_ref_no = $merchant_ref_no;
		return $this;
	}

	public function setTxnCurrencyCode(int $txn_currency_code): self {
		if (!isset($this->currencies[$txn_currency_code])) {
			throw new InvalidArgumentException(\sprintf('Currency code %d is not supported', $txn_currency_code));
		}

		$this->txn_currency_code = $txn_currency_code;
		return $this;
	}

	public function setTxnAmount(int $txn_amount): self {
		if (!isset($this->txn_currency_code)) {
			throw new InvalidArgumentException('Currency code must be set first before setting the currency.');
		}
		$this->txn_amount = \number_format($txn_amount / 100, 2, '.', '');
		return $this;
	}

	public function setMobileNo(string $mobile_no): self {
		$this->validateNumeric($mobile_no, 'Mobile Number')
		     ->validateLength($mobile_no, 10, 'Mobile Number');
		$this->mobile_no = $mobile_no;
		return $this;
	}

	public function setFrimiId(string $frimi_id): self {
		$this->validateNumeric($frimi_id, 'FriMi ID')
		     ->validateLength($frimi_id, 10, 'FriMi ID');
		$this->frimi_id = $frimi_id;
		return $this;
	}

	public function setUserIdentifier(string $user_identifier): self {
		$this->validateNumeric($user_identifier, 'FriMi ID or Mobile Number')
		     ->validateLength($user_identifier, 10, 'FriMi ID or Mobile Number');

		if (strpos($user_identifier, '0') === 0) {
			$this->setMobileNo($user_identifier);
			return $this;
		}

		if (\strpos($user_identifier, '2') === 0) {
			$this->setFrimiId($user_identifier);
			return $this;
		}

		throw new InvalidArgumentException('Entered FriMi ID / Mobile Number appears to be invalid.');
	}

	public function setDiscountAmount(int $discount_amount): self {
		if (!isset($this->txn_currency_code)) {
			throw new InvalidArgumentException('Currency code must be set first before setting the discount.');
		}
		$this->discount_amount = \number_format($discount_amount / 100, 2, '.', '');
		return $this;
	}

	public function setDescription(string $description): self {
		$this->validateMaxLength($description, 50, 'Description');
		$this->description = $description;
		return $this;
	}



	protected function buildDateTime(): string {
		$this->request_timestamp = time();
		return static::formatDate($this->request_timestamp);
	}

	private function buildBody(): \stdClass{
		$request = new \stdClass();
		$request->frimi_id = (string) $this->frimi_id;
		$request->merchant_ref_no = $this->merchant_ref_no;
		$request->txn_amount = $this->txn_amount;
		$request->txn_currency_code = (string) $this->txn_currency_code;
		$request->mid = $this->mid;
		$request->mobile_no = (string) $this->mobile_no;
		$request->discount_amount = $this->discount_amount;
		$request->description = $this->description;
		$request->custom_field_01 = '';
		$request->custom_field_02 = '';

		return $request;
	}

	public function selfTest(): void {
		parent::selfTest();
		$required_fields = [
			'txn_amount' => 'Txn Amount',
			'discount_amount' => 'Discount Amount',
			'merchant_ref_no' => 'Merchant Ref No',
		];

		foreach ($required_fields as $field => $label) {
			if (!isset($this->{$field}) || $this->{$field} === '') {
				throw new RuntimeException(\sprintf('Unable to complete self-test due to "%s" field not being set.', $label));
			}
		}

		if (empty($this->mobile_no) && empty($this->frimi_id)) {
			throw new RuntimeException('Either of FriMi ID or Mobile Number fields must be set.');
		}
	}

	public function buildPayload(): \stdClass {
		$payload = parent::buildPayload();
		$payload->body = $this->buildBody();
		$payload->body = Base64Processor::encode(JsonProcessor::encode($payload->body));

		return $payload;
	}

	public function normalizeCurrency(string $amount): int {
		return (int) ((float) $amount * (10 ** $this->currencies[$this->txn_currency_code]));
	}

	public function getTimestamp(): int {
		return $this->request_timestamp;
	}
}
