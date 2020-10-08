<?php
declare(strict_types=1);

namespace FriMi\SDK\Response;

use FriMi\SDK\Exception\InvalidArgumentException;

class PaymentResponse implements ResponseInterface {
	private $tid;
	private $request_id;
	private $req_type_id;
	private $date_time;
	private $txn_code;
	private $frimi_txn_ref_no;
	private $discount_amount;
	private $description;

	private $request_timestamp;

	private const KNOWN_TXN_CODES = [
		'00', '01', '-1',
	];

	public function setTid($tid): self {
		$this->tid = $tid;
		return $this;
	}

	public function setRequestId(string $request_id): self {
		$this->request_id = $request_id;
		return $this;
	}

	public function setReqTypeId(string $req_type_id): self {
		$this->req_type_id = $req_type_id;
		return $this;
	}

	public function setDateTime(int $date_time): self {
		$this->date_time = $date_time; // Unix timestamp.
		return $this;
	}

	public function setRequestTimestamp(int $timestamp): self {
		$this->request_timestamp = $timestamp;
		return $this;
	}

	public function setTxnCode(string $txn_code): self {
		if (!\in_array($txn_code, self::KNOWN_TXN_CODES, true)) {
			throw new InvalidArgumentException(\sprintf('Txn Code unknown: "%s"', $txn_code));
		}

		$this->txn_code = $txn_code;
		return $this;
	}

	public function setFrimiTxnRefNo(string $frimi_txn_ref_no): self {
		$this->frimi_txn_ref_no = $frimi_txn_ref_no;
		return $this;
	}

	public function setDiscountAmount(int $discount_amount): self {
		$this->discount_amount = $discount_amount;
		return $this;
	}

	public function setDescription(string $description): self {
		$this->description = $description;
		return $this;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function getRequestId(): string {
		return $this->request_id;
	}

	public function getRequestTimestamp(): int {
		return $this->request_timestamp;
	}

	public function isApproved(): bool {
		return $this->txn_code === '00';
	}

	public function isPending(): bool {
		return $this->txn_code === '01';
	}

	public function isFailed(): bool {
		return $this->txn_code === '-1';
	}
}
