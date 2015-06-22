<?php

	namespace apf\traits\type\parser{

		use apf\type\parser\Parameter	as	ParameterParser;

		trait Parameter{

			protected	$parameters	=	NULL;

			public function parseParameters($parameters=NULL,$merge=TRUE){

				if(is_null($this->parameters)){

					$this->parameters	=	ParameterParser::parse($parameters);
					$this->parameters->replace('cast',FALSE);

					return $this->parameters;

				}

				$parameters	=	ParameterParser::parse($parameters);
				$parameters->replace('cast',FALSE);

				if($merge){

					return $this->parameters	=	$this->parameters->merge($parameters);

				}

				$currentParameters	=	clone($this->parameters);

				return $currentParameters->merge($parameters);

			}

			public function &getParameters(){

				return $this->parameters;

			}


		}

	}
