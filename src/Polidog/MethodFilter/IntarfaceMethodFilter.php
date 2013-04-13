<?php
namespace Polidog\MethodFilter;

interface IntarfaceMethodFilter {
	public function call($methodName,$arguments = null);
}