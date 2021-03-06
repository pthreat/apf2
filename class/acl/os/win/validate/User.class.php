<?php

	namespace apf\acl\os\win\validate{

		use apf\acl\os\common\validate\User	as	CommonUserValidation;
		use apf\acl\os\win\User;

		class User extends CommonUserValidation{

			public static function isRoot($user){

				//we could check on the sudoers file
				//and some other things.

				$user	=	User::instance($user);
				return $user->getUID()===0;

			}

		}

	}
