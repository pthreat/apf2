<?php

	namespace apf\acl\os\win{

		use apf\type\base\Str						as	StringType;
		use apf\type\base\Vector					as	VectorType;
		use apf\type\parser\Parameter				as	ParameterParser;
		use apf\acl\os\common\collection\User	as	UserCollection;
		use apf\acl\os\common\Group				as	CommonGroup;

		class Group extends CommonGroup{

			public static function instance($val,$parameters=NULL){

				//Assume current group

				if(!is_numeric($val)&&empty($val)){

					return new static(posix_getgrgid(posix_getegid()),$parameters);

				}

				$val	=	StringType::cast($val,$parameters)->valueOf();

				//A user group could be badly formed
				//we have to check first if the group exists by name
				//and then check by GID.

				$info	=	posix_getgrnam($val);

				if($info){

					return new static($info,$parameters);

				}

				$info	=	posix_getgrgid($val);

				if($info){

					return new static($info);

				}

				throw new UncastableException("Could not find group \"$val\" by GID or group name");

			}

			public function setGID($gid){

				$gid	=	IntType::cast($gid);

				if($gid->valueOf()<0){

					throw new \InvalidArgumentException("Invalid GID $gid");

				}

				$this->data['gid']	=	$gid->valueOf();

				return $this;

			}

			public function getMembers(){

				$sysUserCollection	=	new SysUserCollection();

				if(empty($this->data['members'])){

					$this->data['members']	=	Array($this->data['name']);

				}

				foreach($this->data['members'] as $member){

					$sysUserCollection->add(SysUser::cast($member));

				}

				return $sysUserCollection;

			}

		}

	}
