<?php

namespace iamcal;

class IPParser{

	public function parse($in){

		return [
			'type'		=> 'ipv4',
			'canonical'	=> '1.2.3.4',
		];
	}
}
