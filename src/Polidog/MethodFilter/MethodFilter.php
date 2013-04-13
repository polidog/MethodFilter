<?php
namespace Polidog\MethodFilter;
use Polidog\MethodFilter\Exception\MethodFilterExecption;
class MethodFilter extends AbstractMethodFilter {
	
	private $filters = array();
	
	/**
	 * 唯一定義できるpublicメソッド
	 * @param string $methodName
	 * @param mixed $arguments
	 * @return mixed
	 */
	public function call($methodName, $arguments = null) {
		if (method_exists($this, $methodName)) {
			return $this->$methodName($arguments);
		}
	}
	
	private function addFilter($arguments) {
		list($type,$methodName,$callbacks) = $arguments;
		if (!is_array($callbacks)) {
			$callbacks = array($callbacks);
		}
		foreach ($callbacks as $key => $callback) {
			if (!$this->isClosure($callback)) {
				unset($callbacks[$key]);
			}
		}
		if (empty($callbacks)) {
			throw new MethodFilterExecption("no callback function!");
		}
		
		if (!isset($this->filters[$type])) {
			$this->filters[$type] = array();
		}
		
		$this->filters[$type][$methodName] = $callbacks;
	}

	
	protected function callPostFilter($name, $arguments, $return) {
		if ( isset($this->filters['post'][$name]) ) {
			foreach ($this->filters['post'] as $callback) {
				$return = $callback($arguments,$return);
			}
		}
		return $return;
	}
	
	protected function callPreFilter($name, $arguments) {
		if ( isset($this->filters['pre'][$name]) ) {
			foreach ($this->filters['pre'][$name] as $callback) {
				$arguments = $callback($arguments);
			}
		}
		return $arguments;
	}
}