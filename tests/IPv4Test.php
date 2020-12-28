<?php

	use PHPUnit\Framework\TestCase;

	final class IPv4Test extends TestCase{

		function testBasic(){

			$this->check("1.2.3.4", "1.2.3.4", "Simple dotted quad");
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
