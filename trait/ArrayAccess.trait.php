<?php

	namespace apf\traits{

		trait ArrayAccess{

			public function offsetSet($offset,$value){

				if(empty($offset)){

					$offset	=	count($this->value);

				}

				$this->value[$offset]	=	$value;

			}

			public function offsetExists($offset){

				return isset($offset,$this->value);

			}

			public function offsetGet($offset){

				if(!array_key_exists($offset,$this->value)){

					throw new UndefinedIndexException("No such index $offset");

				}

				return $this->useCast ? parent::castAny($this->value[$offset]) : $this->value[$offset];

			}

			public function offsetUnset($offset){

				unset($this->value[$offset]);

			}

		}

	}
