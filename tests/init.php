<?php
	require_once __DIR__.'/../vendor/autoload.php';

	if (version_compare(PHP_VERSION, '7.3', '>=')){

		class_alias('\PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
	}
