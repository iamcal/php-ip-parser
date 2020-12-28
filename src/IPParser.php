<?php

namespace iamcal;

class IPParser{

	public function parse($in){

		# simple IPv4 dotted quad
		if (preg_match('!^([1-9]\d*)\.([1-9]\d*)\.([1-9]\d*)\.([1-9]\d*)$!', $in, $m)){
			$a = intval($m[1]);
			$b = intval($m[2]);
			$c = intval($m[3]);
			$d = intval($m[4]);
			if ($a > 255 || $b > 255 || $c > 255 || $d > 255){
				throw new \Exception('Invalid');
			}

			return [
				'type'		=> 'ipv4',
				'canonical'	=> "{$a}.{$b}.{$c}.{$d}",
			];
		}


		# simple IPv6 colon-hex
		if (preg_match('!^([0-9a-f]{1,4}):([0-9a-f]{1,4}):([0-9a-f]{1,4}):([0-9a-f]{1,4}):([0-9a-f]{1,4}):([0-9a-f]{1,4}):([0-9a-f]{1,4}):([0-9a-f]{1,4})$!i', $in, $m)){
			$a = hexdec($m[1]);
			$b = hexdec($m[2]);
			$c = hexdec($m[3]);
			$d = hexdec($m[4]);
			$e = hexdec($m[5]);
			$f = hexdec($m[6]);
			$g = hexdec($m[7]);
			$h = hexdec($m[8]);
			return [
				'type'		=> 'ipv6',
				'canonical'	=> sprintf('%x:%x:%x:%x:%x:%x:%x:%x', $a, $b, $c, $d, $e, $f, $g, $h),
			];
		}

		throw new \Exception('Invalid');
	}
}
