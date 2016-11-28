<?php
namespace Nwoc;

class DataConversionUtil {
	public static function map_values($value, array $map, $default) {
		if (array_key_exists($value, $map))
			return $map[$value];
		return $default;
	}
}