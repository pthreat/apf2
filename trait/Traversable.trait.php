<?php

	namespace apf\traits{

		use \apf\type\Common	as	Type;

		trait Traversable{

			protected	$position	=	0;
			protected	$autoCast	=	FALSE;

			/**
			*The main purpouse of this method is about specifying whether you'd like
			*to cast the array values (as being accesed through \ArrayAccess or \Iterator interfaces)
			*to Apollo types.
			*
			*@param bool TRUE in case you'd like to cast every value to an Apollo Type.
			*@param bool FALSE in case you don't want to cast every value to an Apollo type.
			*
			*@return \apf\type\base\Vector This instance
			*/

			public function autoCast($boolean){

				$this->autoCast	=	(boolean)$boolean;
				return $this;

			}


			/*******************************************
			*Iterator interface methods
			*/

			public function key(){
				
				return key($this->value);

			}

			public function rewind(){

				return $this->autoCast	?	Type::castAny(reset($this->value))	:	reset($this->value);

			}

			public function current(){

				return $this->autoCast	?	Type::castAny(current($this->value))	:	current($this->value);

			}

			public function next(){

				$this->position++;
				return $this->autoCast	?	Type::castAny(next($this->value))	:	next($this->value);

			}

			public function valid(){

				$key	=	$this->key();

				return $key !== NULL && $key!==FALSE;

			}

			/**
			*End of iterator interface methods
			********************************************/

			/*******************************************
			*Accesory iterator interface methods
			*/

			public function prev(){
				
				if($this->position>0){

					$this->position--;

				}

				return $this->autoCast	?	Type::castAny(prev($this->value))	:	prev($this->value);

			}

			public function getPosition(){

				return $this->position;

			}

			/**
			*End of accesory iterator interface methods
			********************************************/
		}

	}
