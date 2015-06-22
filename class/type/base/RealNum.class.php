<?php

	namespace apf\base\type{

		use apf\type\Common;
		use apf\type\exception\common\Uncastable	as	UncastableException;

		class RealNum extends Common{

			private static $hasBcMath	=	FALSE;
			private	$precision	=	2;

			public static function cast($num,$parameters=NULL){

				die("Finish real num class");

				$type		=	strtolower(gettype($options));

				switch($type){

					case 'int':
					case 'string':
					case 'null':
					case 'boolean':
					case 'double':
						return new static((double)$num);
					break;

					case 'array':
						//return a collection of decimal numbers	
					break;

					case 'object':


						if(is_object($num)&&Class_::hasMethod($num,'__toString')){

							$num	=	sprintf('%d',$num);
							return new static($num);

						}
					break;

					default:

						if($options["throw"]){

							throw new UncastableException("Can not cast value of type $type to a RealNum");

						}

					break;

				}

			}

			public static function instance($parameters=NULL){
				return new static(0,$parameters);
			}

			private function hasBcExtension(){

				//To be replaced with Platform::hasExtension('extensionName');
				if(!is_null(self::$hasBcMath)){

					return self::$hasBcMath;	

				}

				self::$hasBcMath	=	Vector::cast(get_loaded_extensions())->inArray('bcmath');

			}

			public function add($num,Array $options=Array()){

				$num	=	self::cast($num,array_merge($options,['throw'=>TRUE]));

				if($this->hasBcMath()){

					return bcadd($this->valueOf() + $num->valueOf());

				}

				return $this->valueOf() + $num;

			}

			public function setPrecision($int){

				$this->precision	=	IntNum::cast($int,['strict'=>TRUE]);

				if($this->hasBcMath()){

					bcscale($this->precision);

				}

				return $this;

			}

			public function getPrecision(){

				return $this->precision;

			}

			public function valueOf(){

				return sprintf("%.{$this->precision}f",$this->value);

			}

			public function toString(){
			}
			public function toChar(){
			}
			public function toJSON(){
			}
			public function toBoolean(){
			}

			public function __toString(){
				return "";
			}

		}

	}

