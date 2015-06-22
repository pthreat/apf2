<?php

	namespace apf\acl\os\common{

		use apf\type\base\Str					as	StringType;
		use apf\type\base\Vector				as	VectorType;
		use apf\type\util\common\Variable	as	VarUtil;
		use apf\iface\acl\os\User				as	UserInterface;
		use apf\iface\Convertible				as	ConvertibleInterface;

		abstract class User implements UserInterface,ConvertibleInterface{

			use \apf\traits\type\parser\Parameter;

			protected	$data	=	Array();

			protected function __construct($val){

				$this->data	=	VectorType::cast($val);

			}

			public function setName($name){

				$this->data['name']	=	VarUtil::printvar($name);
				return $this;

			}

			public function getName(){

				return $this->data['name'];

			}

			public function getUID(){

				return $this->data['uid'];

			}

			public function setHome($home){

				$this->data['dir']	=	VarUtil::printVar($home);
				return $this;

			}

			public function getHome(){

				return StringType::cast($this->data['dir']);

			}

			public function setPass($pass){

				$this->data['pass']	=	VarUtil::printVar($pass);
				return $this;

			}

			public function getPass(){

				return StringType::cast($this->data['pass']);

			}	

			public function setShell($shell){

				$this->data['shell']	=	VarUtil::printVar($shell);
				return $this;

			}

			public function getShell(){

				return StringType::cast($this->data['shell']);

			}

			public function setInfo($info){

				$this->data['gecos']	=	VarUtil::printVar($info);
				return $this;

			}

			public function getInfo(){

				return StringType::cast($this->data['gecos']);

			}

			public function toString(){

				return $this->data->toString();

			}

			public function toJSON(){

				return $this->data->toJSON();

			}

			public function toInt(){

				return $this->data->toInt();

			}

			public function toChar(){
			}

			public function toReal(){
			}

			public function toArray(){

				return $this->data;

			}

			public function toBoolean(){

				return $this->data->toBoolean();

			}

			public function jsonSerialize(){

				return $this->data;

			}

			public function __toString(){

				return $this->data['name'];

			}

		}

	}
