<?php

	namespace apf\data\format{

		use apf\type\Base		as	BaseType;

		use apf\exception\data\format\json\UndeEcodable	as	UndecodableException;
		use apf\exception\data\format\json\NotEncodable	as	NotEncodableException;

		class	Json extends BaseType {

			private	$encoded	=	FALSE;

			public static function cast($val,$options=NULL){
	
				$this->value	=	$val;

			}

			public static function instance($options=NULL){

				return new static(NULL,$options);

			}

			public function encode(){

				if(ValidateVar::isTraversable($this->value)){

					foreach($this->value as &$value){
						var_dump($value);
					}

				}

				$this->value	=	json_encode($this->value);

				if($this->value===FALSE){

					throw new NotEncodableException("Can not encode to JSON: ".parent::export());

				}

				return $this;

			}

			public function decode(){

				if(!is_string($this->value)){

					throw new UndecodableException("Can not decode JSON: ".parent::export());

				}

				$this->value	=	json_decode($this->value);

				return $decode;

			}

			public function toString(){
			
				$this->encode();
				return StringType::cast($this->value);

			}

			public function __toString(){
	
				try{

					if(!is_string($this->value)){

						$this->encode();

					}

					return $this->value;

				}catch(\Exception $e){

					return "";

				}

			}

		}

	}
