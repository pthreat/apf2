<?php

	namespace apf\acl\os\common{

		use apf\type\base\Str						as	StringType;
		use apf\type\base\Vector					as	VectorType;
		use apf\acl\os\common\collection\User	as	UserCollection;

		use apf\iface\acl\os\Group					as	GroupInterface;
		use apf\iface\Convertible;

		abstract class Group implements Convertible,GroupInterface{

			use \apf\traits\type\parser\Parameter;

			private $data	=	Array();

			protected function __construct($val,$parameters=NULL){

				$this->data	=	VectorType::cast($val);
				$this->parseParameters($parameters);

			}

			public function setName($name){

				$this->data['name']	=	$name;
				return $this;

			}

			public function getName(){

				return StringType::cast($this->data['name']);

			}

			public function getGID(){

				return $this->data['gid'];

			}

			public function toInt(){

				return $this->data->toInt();

			}

			public function toString(){

				return StringType::cast($this->data['name']);

			}

			public function toArray(){

				return $this->data;

			}

			public function toChar(){

				return CharType::cast($this->data['name']);

			}

			public function toReal(){

				return RealType::cast($this->data['gid']);

			}

			public function toJSON(){

				return $this->data->toJSON();

			}

			public function toBoolean(){

				$this->data->toBoolean();

			}

			public function jsonSerialize(){

				return $this->data;

			}

			public function __toString(){

				return $this->data['name'];

			}

		}

	}
