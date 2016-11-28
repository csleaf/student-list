<?php
namespace Nwoc;

class SecurityUtil {
	const GENERATE_ALLOWED_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFHIJKLMNOPQRSTUVWXYZ';

	public static function generate_session_id(int $size = 32) {
		$len = strlen(self::GENERATE_ALLOWED_CHARS);
		$out = str_repeat('x', $size);
		for ($i = 0; $i < $size; ++$i) {
			$out[$i] = strval(self::GENERATE_ALLOWED_CHARS[random_int(0, $len - 1)]);
		}
		return $out;
	}
}