<?php
declare(strict_types=1);

namespace FriMi\SDK\Process;

final class Masker {
  public static function markPhoneNumber(string $string): string {
		return self::maskPrefix($string);
  }

  private static function maskPrefix(string $text, int $unmarked_last_digits = 4, string $mask_char = 'X'): string {
  	$length = \mb_strlen($text);

  	if ($length <= $unmarked_last_digits) {
  		return $text;
	  }

  	$mask = \str_repeat($mask_char, $length - $unmarked_last_digits);
  	$revealed = \mb_substr($text, -1 * $unmarked_last_digits);

		return $mask . $revealed;
  }
}
