<?php

	use PHPUnit\Framework\TestCase;

	final class IPv6Test extends TestCase{

		function testBasic(){

			$this->assertEquals($this->to_canonical("1:2:3:4:5:6:7:8"), "1:2:3:4:5:6:7:8", "Simple colon-hex");
		}

		function testElided(){

		#	$this->assertEquals($this->to_canonical("1:2::3:4"), "1:2:0:0:0:0:3:4", "Elided zeros in middle");
		#	$this->assertEquals($this->to_canonical("::1"), "0:0:0:0:0:0:0:1", "Elided zeros at start");
		#	$this->assertEquals($this->to_canonical("1::"), "1:0:0:0:0:0:0:0", "Elided zeros at end");
		#	$this->assertEquals($this->to_canonical("::"), "0:0:0:0:0:0:0:0", "Elided zeros entirely");
		}

		function testQuad(){

		#	$this->assertEquals($this->to_canonical("1:2:3:4:5:6:77.77.88.88"), "1:2:3:4:5:6:4d4d:5858", "Dotted quad allowed for final 2 segments");

		#	$this->assertEquals($this->to_canonical("fe80::1.2.3.4"), "fe80:0:0:0:0:0:102:304", "Dotted quad combined with elided zeros");
		}

		function testLeadingZeros(){

			$this->assertEquals($this->to_canonical("0001:0002:0003:0004:0005:0006:0007:0008"), "1:2:3:4:5:6:7:8", "Leading zero are OK");
			$this->assertEquals($this->to_canonical("00001:0002:0003:0004:0005:0006:0007:0008"), "Exception", "But no more than 4 hex-digits per segment");
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
