<?php

	require	"tests/include.php";

	use apf\acl\os\User;
	use apf\core\Log;

	$log	=	new Log();

	$user	=	User::getCurrent();
	$log->info("Get current user: %s",$user);
	$log->info('Get current user group: %s',$user->getGroup());
	$log->info("Get GROUPS the user belongs to: %s",$user->getGroups());

	$log->info("Check another user: pthreat");
	$user	=	User::instance("pthreat");
	$log->info("User: %s",$user);
	$log->info('Group: %s',$user->getGroup());
	$log->info("Get GROUPS the user belongs to: %s",$user->getGroups());
