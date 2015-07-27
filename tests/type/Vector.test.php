<?php

	require __DIR__."/../include.php";

	use apf\core\Log				as	Log;
	use apf\type\base\Vector	as	VectorType;

	$log	=	new Log();
	$log->setNoPrefix();

	
	$array	=	[1,2,3];
	$log->info("Vector type test %s",$array);

	$vector	=	VectorType::cast($array,[
														'allowedTypes'=>[
																				[
																					'type'	=>'primitive',
																					'value'	=>'integer'
																				],
																				[
																					'type'	=>'primitive',
																					'value'	=>'string'
																				],
														],
														'maxMemory'	=>	[
																				'amount'		=>'2140',
																				'meassure'	=>'kilobyte'
														]
	]);

	$vector[]	=	1;
	$vector[]	=	"hello";

	try{

		$vector[]	=	new stdClass();

	}catch(\Exception $e){

		$log->emergency($e);

	}

	$vector[]	=	"test";
	echo $vector;

	$vector->pad(10,30);

	foreach($vector as $k=>$v){
		echo "$k=>$v"."\n";
	}

	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';
	$vector[]='alalalala';

	echo $vector->flip()->hello;


