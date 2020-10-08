<?php
declare(strict_types=1);

namespace FriMi\SDK\Response;

/**
 * @internal
 * @package FriMi\SDK\Response
 */
class TokenResponse implements ResponseInterface {
	private $token;
	private $expiration;
	private $lifetime;

	public function __construct(string $token, int $expires_in) {
		$this->token = $token;
		$this->expiration = time() + $expires_in;
		$this->lifetime = $expires_in;
	}

	public function isValid(): bool {
		return !(time() >= $this->expiration);
	}

	public function getToken(): string {
		return $this->token;
	}

	public function getLifeTime(): int {
		return $this->lifetime;
	}
}
