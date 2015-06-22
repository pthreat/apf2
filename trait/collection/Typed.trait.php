<?php

	namespace apf\traits\collection{

		trait Typed{

			public function isValidItem($item,$requiredType,$throw=FALSE){

				$type		=	gettype($item);
				$isValid	=	$type	==	$requiredType;

				if($isValid){

					return TRUE;

				}

				if(!$throw){

					return FALSE;

				}

				$msg	=	"$requiredType collection only accepts $requiredType, \"$type\" was given";

				throw new \InvalidArgumentException($msg);

			}

		}

	}
