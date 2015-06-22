<?php

	namespace apf\acl\os\unix{

		use apf\type\base\Str						as	StringType;
		use apf\type\parser\Parameter				as	ParameterType;
		use apf\type\util\common\Variable		as	VarUtil;
		use apf\acl\os\common\User					as	CommonUser;
		use apf\acl\os\common\collection\Group	as	GroupCollection;
		use apf\io\os\unix\File;

		class User extends CommonUser{

			public static function instance($val,$parameters=NULL){

				if(is_a($val,__CLASS__)){

					return $val;

				}

				//Assume current user

				if(!is_numeric($val)&&empty($val)){

					return self::getCurrent();

				}

				$val	=	StringType::cast($val,$parameters)->valueOf();

				//As a username can be made of only numbers
				//we have to check first if the user exists 
				//by it's name and then check by UID :(

				$info	=	posix_getpwnam($val);

				if($info){

					return new static($info,$parameters);

				}

				$info	=	posix_getpwuid($val);

				if($info){

					return new static($info,$parameters);

				}

				throw new UncastableException("Could not find user \"$val\" by UID or username");

			}

			public static function getCurrent(){

				return new static(posix_getpwuid(posix_geteuid()));

			}

			public function setUID($uid){

				$uid	=	IntType::cast($uid);

				if($uid->valueOf()<0){

					throw new \InvalidArgumentException("Invalid UID $uid");

				}

				$this->data['uid']	=	$uid;

				return $this;

			}

			public function getUID(){

				return $this->uid;

			}

			public function isRoot(){

				return $this->getUID()===0;

			}

			public function setGID($gid){

				$gid	=	IntType::cast($gid);

				if($gid->valueOf()<0){

					throw new \InvalidArgumentException("Invalid UID $gid");

				}

				$this->data['gid']	=	$gid;

				return $this;

			}

			public function getGroup(){

				return Group::instance($this->data['gid']);

			}

			//Get all groups this user belongs to
			//Since there's not a posix function available that does this
			//we have to do it by hand :'( ... yes there's a function that returns 
			//a user's group but NOT all the groups the user belongs to!
			//There's no getgrouplist in PHP :( (man -k getgrouplist)
			//https://bugs.php.net/bug.php?id=61089

			public function getGroups($parameters=NULL){

				$parameters			=	ParameterType::parse($parameters);

				$parameters->replace('mode','r');
				$parameters->replace('mustExist',TRUE);
				$parameters->replace('file','/etc/group');

				$skipMainGroup		=	$parameters->find('skipMainGroup',TRUE)->toBoolean()->valueOf();
				$groupsCollection	=	new GroupCollection();
				$groupFile			=	new File($parameters);

				if(!$skipMainGroup){

					$groupsCollection->add($this->getGroup());

				}

				foreach($groupFile->getHandler() as $line){

					if(!$line->match($this,$parameters)){

						continue;

					}

					$groupsCollection->add(Group::instance($line->trim()->cutFirst(':')));

				}

				return $groupsCollection;

			}

			public function getGID(){

				return $this->gid;

			}

		}

	}
