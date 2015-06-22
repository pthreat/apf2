<?php

	namespace apf\type\collection\base{

		use apf\type\base\Char				as	CharType;
		use apf\type\parser\Parameter		as	ParameterParser;
		use apf\type\collection\Common;

		class Char extends Common{

			public function __construct($val=NULL,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$parameters->replace('allowedTypes',[['type'=>'class','value'=>'apf\\type\\base\\Char']]);
				parent::__construct($val,$parameters);

			}

			public function current(){

				return CharType::cast(parent::current());

			}

			public function offsetGet($offset){

				return CharType::cast(parent::offsetGet($offset));

			}

		}

	}
