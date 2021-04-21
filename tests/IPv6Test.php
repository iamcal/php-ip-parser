<?php

	use PHPUnit\Framework\TestCase;

	final class IPv6Test extends TestCase{

		function testBasic(){

			$this->assertEquals($this->to_canonical("1:2:3:4:5:6:7:8"), "1:2:3:4:5:6:7:8", "Simple colon-hex");

			$this->assertEquals($this->to_canonical("1:2:3:4:5:6:7:ffff"), "1:2:3:4:5:6:7:ffff", "Simple colon-hex: in-bounds");
			$this->assertEquals($this->to_canonical("1:2:3:4:5:6:7:10000"), "Exception", "Simple colon-hex: in-bounds");
		}

		function testElided(){

			$this->assertEquals($this->to_canonical("1:2::3:4"), "1:2:0:0:0:0:3:4", "Elided zeros in middle");
			$this->assertEquals($this->to_canonical("::1"), "0:0:0:0:0:0:0:1", "Elided zeros at start");
			$this->assertEquals($this->to_canonical("1::"), "1:0:0:0:0:0:0:0", "Elided zeros at end");
			$this->assertEquals($this->to_canonical("::"), "0:0:0:0:0:0:0:0", "Elided zeros entirely");
		}

		function testQuad(){

			$this->assertEquals($this->to_canonical("1:2:3:4:5:6:77.77.88.88"), "1:2:3:4:5:6:4d4d:5858", "Dotted quad allowed for final 2 segments");

			$this->assertEquals($this->to_canonical("fe80::1.2.3.4"), "fe80:0:0:0:0:0:102:304", "Dotted quad combined with elided zeros");

			$this->assertEquals($this->to_canonical("1:2:3:4:5:6:77.77.0x58.0130"), "1:2:3:4:5:6:4d4d:5858", "Dotted quad with hex and octal atoms");

			$this->assertEquals($this->to_canonical("1:2:3:4:5:6:77.77.22616"), "1:2:3:4:5:6:4d4d:5858", "Dotted quad with class B");
			$this->assertEquals($this->to_canonical("1:2:3:4:5:6:77.5068888"), "1:2:3:4:5:6:4d4d:5858", "Dotted quad with class A");

			$this->assertEquals($this->to_canonical("1:2:3:4:5:6:1296914520"), "1:2:3:4:5:6:4d4d:5858", "Dotted quad with long");
		}

		function testLeadingZeros(){

			$this->assertEquals($this->to_canonical("0001:0002:0003:0004:0005:0006:0007:0008"), "1:2:3:4:5:6:7:8", "Leading zero are OK");
			$this->assertEquals($this->to_canonical("00001:0002:0003:0004:0005:0006:0007:0008"), "Exception", "But no more than 4 hex-digits per segment");
		}

		function testTooLong(){

			$this->assertEquals($this->to_canonical("1:2:3:4:5:6:7:8:9"), "Exception", "Too long");
			$this->assertEquals($this->to_canonical("1:2:3:4::5:6:7:8"), "Exception", "Too long, elided 1");
			$this->assertEquals($this->to_canonical("::1:2:3:4::5:6:7:8"), "Exception", "Too long, elided 2");
			$this->assertEquals($this->to_canonical("1:2:3:4::5:6:7:8::"), "Exception", "Too long, elided 3");
		}


		function to_canonical($in){
			$p = new iamcal\IPParser();
			try {
				$out = $p->parse($in);
				if ($out['type'] == 'ipv6'){
					return $out['canonical'];
				}else{
					return $out['type'].':'.$out['canonical'];
				}
			} catch (Exception $e) {
				return "Exception";
			}
		}
	}
