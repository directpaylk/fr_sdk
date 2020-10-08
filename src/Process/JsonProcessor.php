<?php
declare(strict_types=1);

namespace FriMi\SDK\Process;


use FriMi\SDK\Exception\InvalidArgumentException;

class JsonProcessor {
	public static function encode($data): string {
		$data = \json_encode($data);
		if ($data === false) {
			throw new InvalidArgumentException(\sprintf('Unexpected JSON encoding error: "%s"', \json_last_error_msg()));
		}
		return $data;
	}

	public static function decode(string $data) {
		$data = \json_decode($data, false);
		if (\json_last_error() !== \JSON_ERROR_NONE) {
			throw new InvalidArgumentException(\sprintf('Unexpected JSON decoding error: "%s"', \json_last_error_msg()));
		}

		return $data;
	}
}
