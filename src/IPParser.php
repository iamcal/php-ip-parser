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

			return $this->process_ipv4($a, $b, $c, $d);
		}

		# more complex IPv4 rules
		$atom = '(?:0[0-7]{3})|(?:0x[0-9a-fA-F]{2})|(?:\d+)';
		if (preg_match("!^({$atom})\.({$atom})\.({$atom})\.({$atom})\$!", $in, $m)){
			$a = $this->process_ipv4_atom($m[1]);
			$b = $this->process_ipv4_atom($m[2]);
			$c = $this->process_ipv4_atom($m[3]);
			$d = $this->process_ipv4_atom($m[4]);

			return $this->process_ipv4($a, $b, $c, $d);
		}

		# single long IPv4
		if (preg_match('!^\d+$!', $in)){
			$x = intval($in);
			$a = ($x & 0xff000000) >> 24;
			$b = ($x & 0xff0000) >> 16;
			$c = ($x & 0xff00) >> 8;
			$d = ($x & 0xff);

			return $this->process_ipv4($a, $b, $c, $d);
		}

		# class B address
		if (preg_match("!^({$atom})\.({$atom})\.(\d+)\$!", $in, $m)){
			$a = $this->process_ipv4_atom($m[1]);
			$b = $this->process_ipv4_atom($m[2]);
			$x = intval($m[3]);
			$c = ($x & 0xff00) >> 8;
			$d = ($x & 0xff);

			return $this->process_ipv4($a, $b, $c, $d);
		}

		# class A address
		if (preg_match("!^({$atom})\.(\d+)\$!", $in, $m)){
			$a = $this->process_ipv4_atom($m[1]);
			$x = intval($m[2]);
			$b = ($x & 0xff0000) >> 16;
			$c = ($x & 0xff00) >> 8;
			$d = ($x & 0xff);

			return $this->process_ipv4($a, $b, $c, $d);
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
			return array(
				'type'		=> 'ipv6',
				'canonical'	=> sprintf('%x:%x:%x:%x:%x:%x:%x:%x', $a, $b, $c, $d, $e, $f, $g, $h),
			);
		}

		throw new \Exception('Invalid');
	}

	private function process_ipv4($a, $b, $c, $d){

		if ($a > 255 || $b > 255 || $c > 255 || $d > 255){
			throw new \Exception('Invalid');
		}

		return array(
			'type'		=> 'ipv4',
			'canonical'	=> "{$a}.{$b}.{$c}.{$d}",
		);
	}

	private function process_ipv4_atom($atom){

		if (preg_match('!^0[0-7]{3}$!', $atom)){
			return octdec($atom);
		}

		if (preg_match('!^0x[0-9a-fA-F]{2}$!', $atom)){
			return hexdec(substr($atom, 2));
		}

		return intval($atom);
	}

}
