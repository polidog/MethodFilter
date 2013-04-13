<?php

namespace Polidog\MethodFilter;

use Polidog\MethodFilter\Execption\MethodFilterExecption;

/**
 * 実行したいメソッドに対してフィルター機能を提供する
 * @version 0.0.1
 * @author polidog <polidogs@gmail.com>
 * 
 * @property mixed $object 実行するクラスのインスタンス
 * @property boolean $isExecuteProtectMethod private, protectedなメソッドを実行するかのフラグ
 */
abstract class AbstractMethodFilter implements IntarfaceMethodFilter {

	protected $object;
	protected $isExecuteProtectMethod = false;
	protected $isPublicMethodCheck = true;
	protected $isPreFilterRewriteArgs = true;

	/**
	 * コンストラクタ
	 * @param object $object
	 * @throws MethodFilterExecption
	 */
	public function __construct($object) {

		if ($this->isPublicMethodCheck && $this->hasPublicMethod()) {
			throw new MethodFilterExecption("Public methods that are defined");
		}

		if (is_object($object)) {
			$this->object = $object;
		} else {
			throw new MethodFilterExecption('no set object');
		}
	}

	
	/**
	 * メソッドが実行された場合の動作
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 * @throws MethodFilterExecption
	 */
	public function __call($name, $arguments) {
		
		
		$_arguments = $this->callPreFilter($name, $arguments);
		if (!is_array($_arguments)) {
			$_arguments = array($_arguments);
		}
		if ($this->isPreFilterRewriteArgs) {
			$arguments = $_arguments;
		}
		
		if (!$this->isExecuteMethod($name)) {

			if (!$this->isExecuteProtectMethod) {
				throw new MethodFilterExecption('method not exist! name:' . $name);
			}

			$method = new ReflectionMethod($this->object, $name);
			$method->setAccessible(true);
			$return = $method->invokeArgs($this->object, $arguments);
		} else {
			$return = call_user_func_array(array($this->object, $name), $arguments);
		}
		$return = $this->callPostFilter($name, $arguments, $return);
		return $return;
	}

	/**
	 * インスタンス変数でそのままコールされた場合の処理
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __invoke($name, $arguments = array()) {
		return $this->__call($name, $arguments);
	}

	/**
	 * クラス変数をセットする
	 * @param string $name 変数名
	 * @param mixed $value 変数にセットする名前
	 */
	public function __set($name, $value) {
		if ($name == 'isExecuteProtectMethod') {
			if (is_bool($value)) {
				$this->isExecuteProtectMethod = $value;
			}
		}
	}
	
	/**
	 * publicなメソッドが定義されているかチェックする
	 * @return boolean
	 */
	protected function hasPublicMethod() {
		$reflection = new \ReflectionObject($this);
		foreach ($reflection->getMethods() as $method) {
			if ($method->isPublic()) {
				if ($method->name != 'call') {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * 実行できるメソッドか判定する
	 * @param string $name
	 * @return boolean
	 */
	protected function isExecuteMethod($name) {
		return is_callable(array($this->object, $name));
	}

	/**
	 * クロージャーか判定する
	 * @param $closuer
	 * @return boolean
	 */
	protected function isClosure($closuer) {
		return ( is_object($closuer) && $closuer instanceof \Closure );
	}

	/**
	 * 実行前フィルタークラス
	 * @param string $name 実行するメソッド名
	 * @param array $arguments 実行するメソッドの引数
	 */
	abstract protected function callPreFilter($name, $arguments);

	/**
	 * 実行後フィルタークラス
	 * @param string $name 実行したメソッド名
	 * @param array $arguments 実行したメソッドの引数
	 */
	abstract protected function callPostFilter($name, $arguments, $return);
}
