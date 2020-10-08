<?php
declare(strict_types=1);

namespace FriMi\SDK\Response;

use FriMi\SDK\Exception\InvalidArgumentException;

class RefundResponse implements ResponseInterface {
	private const KNOWN_TXN_CODES = [
		'00', '01', '-1',
	];

	private $txn_code;
	private $description;

	public function setTxnCode(string $txn_code): self {
		if (!\in_array($txn_code, static::KNOWN_TXN_CODES, true)) {
			throw new InvalidArgumentException(\sprintf('Txn Code unknown: "%s"', $txn_code));
		}

		$this->txn_code = $txn_code;
		return $this;
	}

	public function setDescription(string $description): self {
		$this->description = $description;
		return $this;
	}

	public function getDescription(): string {
		return $this->description;
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
