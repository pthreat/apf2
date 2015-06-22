<?php

	namespace apf\type\base{

		use apf\type\Common;
		use apf\type\base\Vector	as	VectorType;

		class stdObj extends Common{

			public static function cast($value,$parameters=NULL){

				if(is_a($value,__CLASS__)){

					return $value;

				}

				if(is_array($value)){
				}

			}

			public function __get($val){

				throw new \InvalidArgumentException("Invalid offset \"$val\" in stdObj");

			}

		}

	}
