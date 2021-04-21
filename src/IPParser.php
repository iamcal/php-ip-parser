<?php

namespace iamcal;

class IPParser{

	public function parse($in){

		#
		# fast path rules
		#

		# simple IPv4 dotted quad
		if (preg_match('!^([1-9]\d*)\.([1-9]\d*)\.([1-9]\d*)\.([1-9]\d*)$!', $in, $m)){
			$a = intval($m[1]);
			$b = intval($m[2]);
			$c = intval($m[3]);
			$d = intval($m[4]);

			return $this->process_ipv4($a, $b, $c, $d);
		}

		# simple IPv6 colon-hex
		$atom6 = '[0-9a-fA-F]{1,4}';
		if (preg_match("!^($atom6):($atom6):($atom6):($atom6):($atom6):($atom6):($atom6):($atom6)$!", $in, $m)){
			$a = hexdec($m[1]);
			$b = hexdec($m[2]);
			$c = hexdec($m[3]);
			$d = hexdec($m[4]);
			$e = hexdec($m[5]);
			$f = hexdec($m[6]);
			$g = hexdec($m[7]);
			$h = hexdec($m[8]);

			return $this->process_ipv6($a, $b, $c, $d, $e, $f, $g, $h);
		}


		#
		# slow path rules
		#

		# complex IPv4 formats
		$atom4 = '(?:0[0-7]{3})|(?:0x[0-9a-fA-F]{2})|(?:[1-9]\d*)';

		$ipv4_quad	= "({$atom4})\.({$atom4})\.({$atom4})\.({$atom4})";
		$ipv4_long	= "\d+";
		$ipv4_class_b	= "({$atom4})\.({$atom4})\.([1-9]\d*)";
		$ipv4_class_a	= "({$atom4})\.([1-9]\d*)";

		$ipv4 = "(?:($ipv4_quad)|($ipv4_long)|($ipv4_class_b)|($ipv4_class_a))";

		if (preg_match("!^{$ipv4}$!", $in, $m)){

			$quad = $this->decode_ipv4($m);

			return $this->process_ipv4($quad[0], $quad[1], $quad[2], $quad[3]);
		}


		# IPv6 with elided parts
		if (strpos($in, '::') !== false){
			list($in1, $in2) = explode('::', $in, 2);

			# get atoms from before the split
			if (strlen($in1)){
				if (preg_match("!^($atom6)(?::($atom6)){0,6}$!", $in1, $m)){
					$parts = explode(':', $in1);
					$pre_atoms = array();
					foreach ($parts as $atom){
						$pre_atoms[] = hexdec($atom);
					}
				}else{
					throw new \Exception('Invalid');
				}
			}else{
				$pre_atoms = array();
			}

			# get atoms from after the split
			if (strlen($in2)){
				# simple atoms
				if (preg_match("!^($atom6)(?::($atom6)){0,6}$!", $in2, $m)){
					$parts = explode(':', $in2);
					$post_atoms = array();
					foreach ($parts as $atom){
						$post_atoms[] = hexdec($atom);
					}

				# trailing dotted quad
				}else if(preg_match("!^(?:($atom6):){0,6}($ipv4)$!", $in2, $m)){

					$parts = explode(':', $in2);
					$in3 = array_pop($parts);

					if (preg_match("!^{$ipv4}$!", $in3, $m)){
						$quad = $this->decode_ipv4($m);
					}else{
						throw new \Exception('Invalid');
					}

					$post_atoms = array();
					foreach ($parts as $part) $post_atoms[] = hexdec($part);
					$post_atoms[] = ($quad[0] << 8) + $quad[1];
					$post_atoms[] = ($quad[2] << 8) + $quad[3];
				}else{
					throw new \Exception('Invalid');
				}
			}else{
				$post_atoms = array();
			}


			# too many atoms?
			$total = count($pre_atoms) + count($post_atoms);
			if ($total > 7){
				throw new \Exception('Invalid');
			}

			# glue together
			$atoms = array();
			foreach ($pre_atoms as $atom) $atoms[] = $atom;
			for ($i=0; $i<(8-$total); $i++) $atoms[] = 0;
			foreach ($post_atoms as $atom) $atoms[] = $atom;

			return $this->process_ipv6($atoms[0], $atoms[1], $atoms[2], $atoms[3], $atoms[4], $atoms[5], $atoms[6], $atoms[7]);
		}


		# IPv6 with trailing dotted quad (no elided parts)
		if (preg_match("!^($atom6):($atom6):($atom6):($atom6):($atom6):($atom6):{$ipv4}$!i", $in, $m)){
			$a = hexdec($m[1]);
			$b = hexdec($m[2]);
			$c = hexdec($m[3]);
			$d = hexdec($m[4]);
			$e = hexdec($m[5]);
			$f = hexdec($m[6]);

			$quad = $this->decode_ipv4(array_slice($m, 6));

			$g = ($quad[0] << 8) + $quad[1];
			$h = ($quad[2] << 8) + $quad[3];

			return $this->process_ipv6($a, $b, $c, $d, $e, $f, $g, $h);
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

	private function process_ipv6($a, $b, $c, $d, $e, $f, $g, $h){

		if ($a > 65535 || $b  > 65535 || $c > 65535 || $d > 65535 || $e > 65535 || $f > 65535 || $g > 65535 || $h > 65535){
			throw new \Exception('Invalid');
		}

		return array(
			'type'		=> 'ipv6',
			'canonical'	=> sprintf('%x:%x:%x:%x:%x:%x:%x:%x', $a, $b, $c, $d, $e, $f, $g, $h),
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


	private function decode_ipv4($m){

		$a = 0;
		$b = 0;
		$c = 0;
		$d = 0;

		# quad
		if (isset($m[1]) && strlen($m[1])){
			$a = $this->process_ipv4_atom($m[2]);
			$b = $this->process_ipv4_atom($m[3]);
			$c = $this->process_ipv4_atom($m[4]);
			$d = $this->process_ipv4_atom($m[5]);
		}

		# long
		if (isset($m[6]) && strlen($m[6])){
			$x = intval($m[6]);
			$a = ($x & 0xff000000) >> 24;
			$b = ($x & 0xff0000) >> 16;
			$c = ($x & 0xff00) >> 8;
			$d = ($x & 0xff);
		}

		# class B
		if (isset($m[7]) && strlen($m[7])){
			$a = $this->process_ipv4_atom($m[8]);
			$b = $this->process_ipv4_atom($m[9]);
			$x = intval($m[10]);
			$c = ($x & 0xff00) >> 8;
			$d = ($x & 0xff);
		}

		# class A
		if (isset($m[11]) && strlen($m[11])){
			$a = $this->process_ipv4_atom($m[12]);
			$x = intval($m[13]);
			$b = ($x & 0xff0000) >> 16;
			$c = ($x & 0xff00) >> 8;
			$d = ($x & 0xff);
		}

		if ($a > 255 || $b > 255 || $c > 255 || $d > 255){
			throw new \Exception('Invalid');
		}

		return array($a, $b, $c, $d);
	}


}
