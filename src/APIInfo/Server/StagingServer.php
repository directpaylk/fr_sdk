<?php
declare(strict_types=1);

namespace FriMi\SDK\APIInfo\Server;

class StagingServer implements APIServerInterface {
	protected const TIME_ZONE = 'Asia/Colombo';

	public function getTokenRequestUrl(): string {
		return 'https://uatopenapi.nationstrust.com:8243/token';
	}

	public function getPaymentRequestUrl(): string {
		return 'https://uatopenapi.nationstrust.com:8243/ntb/vi.0.0/sense';
	}

	public function getPaymentReversalUrl(): string {
		return 'https://uatopenapi.nationstrust.com:8243/ntb/vi.0.0/sense';
	}

	public function isRefundAllowed(int $unix_timestamp): bool {
		$server_cutoff = new \DateTime('now', new \DateTimeZone(self::TIME_ZONE));
		$server_cutoff->setTime(24, 00);
		return ($server_cutoff->getTimestamp() - $unix_timestamp) > 0;
	}

	public static function getName(): string {
		return 'staging';
	}
}
