<?php

	use PHPUnit\Framework\TestCase;

	final class IPv4Test extends TestCase{

		function testBasic(){

			$this->check("1.2.3.4", "1.2.3.4", "Simple dotted quad");

			$this->check("3232271615", "192.168.140.255", "Single long");

			$this->check("0300.0250.0214.0377", "192.168.140.255", "Octal quad");

			$this->check("0xc0.0xa8.0x8c.0xff", "192.168.140.255", "Hex quad");

			$this->check("192.168.36095", "192.168.140.255", "Class B address");
			$this->check("192.11046143", "192.168.140.255", "Class A address");

			$this->check("0300.0xa8.36095", "192.168.140.255", "Mix of styles");
		}

		function check($in, $expected, $name){
			$this->assertEquals($this->to_canonical($in), $expected, $name);
		}

		function to_canonical($in){
			$p = new iamcal\IPParser();
			try {
				$out = $p->parse($in);
				if ($out['type'] == 'ipv4'){
					return $out['canonical'];
				}else{
					return $out['type'].':'.$out['canonical'];
				}
			} catch (Exception $e) {
				return "Exception";
			}
		}
	}
