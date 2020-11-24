<?php
declare(strict_types=1);

namespace FriMi\SDK\APIInfo\Server;

class ProductionServer implements APIServerInterface {
	protected const TIME_ZONE = 'Asia/Colombo';

	public function getTokenRequestUrl(): string {
		return 'https://coapi.nationstrust.com:8243/token';
	}

	public function getPaymentRequestUrl(): string {
		return 'https://coapi.nationstrust.com:8243/ntb/sense/1.0.0/common';
	}

	public function getPaymentReversalUrl(): string {
		return 'https://coapi.nationstrust.com:8243/ntb/sense/1.0.0/common';
	}

	public function isRefundAllowed(int $unix_timestamp): bool {
		$server_cutoff = new \DateTime('now', new \DateTimeZone(self::TIME_ZONE));
		$server_cutoff->setTime(24, 00);
		return ($server_cutoff->getTimestamp() - $unix_timestamp) > 0;
	}

	public static function getName(): string {
		return 'prod';
	}
}
