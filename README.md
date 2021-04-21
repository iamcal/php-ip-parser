# PHP IPv4 & IPv6 Parser

[![Build Status](https://www.travis-ci.com/iamcal/php-ip-parser.svg?branch=main)](https://www.travis-ci.com/iamcal/php-ip-parser)
[![Coverage Status](https://coveralls.io/repos/github/iamcal/php-ip-parser/badge.svg?branch=main)](https://coveralls.io/github/iamcal/php-ip-parser?branch=main)
[![Latest Stable Version](http://img.shields.io/packagist/v/iamcal/ip-parser.svg?style=flat)](https://packagist.org/packages/iamcal/ip-parser)

This simple PHP  library is based on [a twitter thread](https://twitter.com/dave_universetf/status/1342685822286360576?s=11)
that became [a blog post](https://blog.dave.tf/post/ip-addr-parsing/) about parsing IP addresses.

## Usage

	$input = "1.2.3.4";

	$parser = new iamcal\IPParser();

	$out = $parser->parse($input);

The single public method, `->parse()`, takes a string and returns an array with two keys:


	Array
	(
	    [type] => ipv4
	    [canonical] => 1.2.3.4
	)

The `type` will either be `"ipv4"` or `"ipv6"`, while the `canonical` key will contain a canonicalized version of the IP.

Invalid IPs will throw a catchable exception.
