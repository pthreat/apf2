<?php

	require "tests/include.php";

	use apf\type\base\Vector		as	VectorType;
	use apf\type\base\DiskVector	as	DV;

	$dv	=	DV::cast('bcda',['autoCast'=>FALSE]);

	echo $dv->sort();
