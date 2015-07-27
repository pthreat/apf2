<?php

	require "tests/include.php";

	use apf\type\base\Vector		as	VectorType;
	use apf\type\base\DiskVector	as	DV;
	use apf\core\Log;

	$log		=	new Log();
	$array	=	["test"=>['hello'],['goodbye']];
	$array	=	"feg";
	$dv		=	DV::cast($array,['autoCast'=>FALSE]);

	$log->info("foreach");
	var_dump($dv->current());
	var_dump($dv->next());
	var_dump($dv->next());
	var_dump($dv->next());
	var_dump($dv->next());
	var_dump($dv->next());
	var_dump($dv->next());

	foreach($dv as $k=>$d){

		$log->debug("$k => %s",$d);

	}

	$log->info("Try to get an undefined offset");

	try{

		$dv[10];
		$log->info($dv);

	}catch(\Exception $e){

		$log->debug($e);

	}

	$dv->sort();

	echo $dv[0]."\n";
	echo $dv[1]."\n";
	echo $dv[2]."\n";

