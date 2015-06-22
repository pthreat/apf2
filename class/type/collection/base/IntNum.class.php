<?php

	namespace apf\type\collection\base{

		use apf\type\collection\Common	as	CommonCollection;
		use apf\type\base\IntNum			as	IntType;
		use apf\type\parser\Parameter		as	ParameterParser;

		class IntNum extends CommonCollection{

			public function __construct($val=NULL,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				$parameters->replace('allowedTypes',[
																	[
																		'type'	=>'primitive',
																		'value'	=>'integer'
																	]
				]);

				parent::__construct($val,$parameters);

			}

			/**
			*Everything you add to this collection it will be cast to an integer
			*/

			public function add($item,$parameters=NULL){

				$item	=	IntType::cast($item);
				return parent::add($item->valueOf(),$parameters);

			}

			public function offsetSet($value,$offset){

				$value	=	IntType::cast($value)->valueOf();
				parent::offsetSet($value,$offset);

			}

			public function offsetGet($offset){

				return IntType::cast(parent::offsetGet($offset));

			}

			public function current(){

				return IntType::cast(parent::current());

			}

		}

	}
