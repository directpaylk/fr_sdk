<?php
declare(strict_types=1);

namespace FriMi\SDK\Process;

final class Base64Processor {
  public static function encode(string $data): string {
  	return \base64_encode($data);
  }

  public static function decode(string $data): string {
  	return (string) \base64_decode($data);
  }
}
