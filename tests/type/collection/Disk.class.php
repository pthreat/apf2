<?php

	require "tests/include.php";

	use apf\type\collection\common\Disk	as	DiskCollection;

	$a	=	new DiskCollection();

	$a[]	=	Array(1,2,3);
	$a[]	=	Array(4,5,6);
	$a[]	=	Array(7,8,9);
	$a[]	=	3;

	foreach($a as $k=>$p){
		var_dump($p);
	}
