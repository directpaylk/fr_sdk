<?php
declare(strict_types=1);

namespace FriMi\SDK\Payment;


class StatusRequest extends PaymentRequest {
	protected const REQ_TYPE_ID = '003';

	public function setRequestTimestamp(int $timestamp): self {
	    \Log::info($timestamp);
		$this->request_timestamp = $timestamp;
		return $this;
	}

	protected function buildDateTime(): string {
		return static::formatDate($this->request_timestamp);
	}
}
