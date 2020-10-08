<?php

namespace FriMi\SDK\Cache;

class PHPObjectCache implements CacheInterface {
	private $cache;

	public function set(string $key, $value, int $expiration): void {
		$this->cache[$key] = [$value, time() + $expiration];
	}

	public function get(string $key, $default = null) {
		if (!\array_key_exists($key, $this->cache)) {
			return $default;
		}
		if (time() >= $this->cache[$key][1]) {
			return $default;
		}
		return $this->cache[$key][0];
	}

	public function delete(string $key): void {
		unset($this->cache[$key]);
	}
}
