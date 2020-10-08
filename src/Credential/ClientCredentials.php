<?php
declare(strict_types=1);

namespace FriMi\SDK\Credential;

use FriMi\SDK\Process\Base64Processor;

final class ClientCredentials {
	private $username;
	private $password;

	/**
	 * Implement debugInfo to hide the user password in case of an accidental
	 * stack trace or a @see var_dump() call.
	 * @return array
	 */
	public function __debugInfo() {
		return [
			'username' => $this->username,
			'password' => '-- Hidden --'
		];
	}

	public function getBase64(): string {
		return Base64Processor::encode($this->username . ':' . $this->password);
	}

	/**
	 *
	 * @param string $username
	 * @param string $password
	 */
	public function __construct(string $username, string $password) {
		$this->username = $username;
		$this->password = $password;
	}
}
