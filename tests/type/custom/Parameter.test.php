<?php

	require "tests/include.php";

	use apf\type\parser\Parameter	as	ParameterParser;

	function test($parameters=NULL){

		$parameters	=	ParameterParser::parse($parameters);
		$nombre		=	$parameters->demand('name');
		$edad			=	$parameters->demand('edad')->toInt()->valueOf();
		$apellido	=	$parameters->find('apellido','Maggiora');
		$documento	=	$parameters->selectCase('documento',['dni','lc','le','ci'],'dni');
		$domicilio	=	$parameters->findInsert('domicilio','Nicolas Repetto 84');
		$ciudad		=	$parameters->replace('ciudad','CABA');

		echo $nombre."\n";
		echo $apellido."\n";
		echo $edad."\n";
		echo $documento."\n";
		echo $domicilio."\n";
		echo $ciudad."\n";

	}

	test(['name'=>'gianni','edad'=>"aslkas",'documento'=>'al','ciudad'=>'lalala']);
