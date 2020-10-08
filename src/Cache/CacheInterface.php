<?php

namespace FriMi\SDK\Cache;

interface CacheInterface {
  public function set(string $key, $value, int $expiration): void;
  public function get(string $key, $default = null);
  public function delete(string $key): void;
}
