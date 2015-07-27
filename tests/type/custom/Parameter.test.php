<?php

	require "tests/include.php";

	use apf\type\parser\Parameter	as	ParameterParser;

	function test($parameters=NULL){

		$parameters	=	ParameterParser::parse($parameters);
		var_dump($parameters->findParametersBeginningWith('name'));

	}

	test(['name'=>'gianni','edad'=>"aslkas",'documento'=>'al','ciudad'=>'lalala']);
