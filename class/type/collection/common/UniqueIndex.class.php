<?php

	namespace apf\type\collection\common{

		use apf\type\collection\Common;
		use apf\type\parser\Parameter			as	ParameterParser;
		use apf\type\util\common\Variable	as	VarUtil;
		use apf\type\exception\collection\IndexAlreadyExists;

		class UniqueIndex extends Common{

			//Ignores duplicated keys
			public function add($item,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$key			=	VarUtil::printVar($parameters->demand('key')->valueOf());

				if(parent::hasKey($key)){

					if(!$parameters->find('ignoreDuplicateKeys',FALSE)->toBoolean()->valueOf()){

						throw new IndexAlreadyExists("Index $key already exists in this collection");

					}

					return;

				}

				parent::add($item,$parameters);

			}

		}

	}

