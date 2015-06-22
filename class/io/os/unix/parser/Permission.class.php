<?php

	namespace apf\io\os\unix\parser{

		use apf\type\base\Char					as	CharType;
		use apf\type\base\Str					as	StringType;
		use apf\type\base\Vector				as	VectorType;
		use apf\type\util\common\Variable	as	VarUtil;

		use apf\io\os\unix\File;

		class Permission{

			private	$info		=	NULL;
			private	$owner	=	Array('read'=>NULL,'write'=>NULL,'execute'=>NULL);
			private	$group	=	Array('read'=>NULL,'write'=>NULL,'execute'=>NULL);
			private	$world	=	Array('read'=>NULL,'write'=>NULL,'execute'=>NULL);

			protected function __construct(){
			}

			public static function parse($file,$perms=NULL){

				$perms		=	VarUtil::printVar($perms);

				if(strlen($perms)>9){

					$msg	=	"Permissions string must not be longer than 6 characters";	
					throw new \InvalidArgumentException($msg);

				}

				$curPerms	=	self::fromFile($file);

				if(empty($perms)){

					return $curPerms;

				}


				$obj			=	new static();
				$obj->info	=	$obj->getInfo();

				$owner		=	implode('',(Array)$curPerms->owner);
				$group		=	implode('',(Array)$curPerms->group);
				$world		=	implode('',(Array)$curPerms->world);

				$curPermStr	=	sprintf('%s%s%s',$owner,$group,$world);
				$perms		=	StringType::cast($perms)->toArray()->valueOf();
				$curPermStr	=	VectorType::cast($curPermStr)->valueOf();

				foreach($curPermStr as $k=>$p){

					if(!array_key_exists($k,$perms)){

						$perms[$k]	=	CharType::cast($curPermStr[$k]);

						continue;

					}
	
					$perm			=	$perms[$k]=='='	?	$p	:	$perms[$k];
					$perms[$k]	=	CharType::cast($perm);

				}


				$perms		=	VectorType::cast($perms)->chunk(3)->valueOf();
				$newPerms	=	Array();

				foreach($perms as $key=>$value){

					switch($key){

						case 0:	//owner
						case 1:	//group

							$validPerms	=	['r','w','s','S','x','-'];

							foreach($value as $k=>$v){

								$v	=	sprintf('%s',$v);

								//if the user has specified a = character, leave permissions
								//untouched

								if($v=='='){

									$newPerms[]	=	$key===0	?	$owner[$k]	:	$group[$k];
									continue;
										
								}

								//Check if the given permission is valid

								if(!in_array($v,$validPerms)){

									throw new \InvalidArgumentException("Invalid permission \"$v\"");

								}

								if($v=='-'){

									$newPerms[]	=	$v;
									continue;

								}

								switch($k){

									case 0:
										if($v!='r'){

											$msg	=	"Wrong placed argument \"$v\" should be 'r' or '-'";
											throw new \InvalidArgumentException($msg);

										}
									break;

									case 1:
										if($v!='w'){

											$msg	=	"Wrong placed argument \"$v\" should be one of 'w','-'";
											throw new \InvalidArgumentException($msg);

										}
									break;

									case 2:

										$valid	=	['x','s','S','t','T'];

										if(!in_array($v,$valid)){

											$msg	=	"Wrong placed argument \"$v\" should be one of ";
											$msg	=	sprintf("%s %s",$msg,implode(',',$valid));

											throw new \InvalidArgumentException($msg);

										}

									break;

								}

								$newPerms[]	=	$v;

							}

						break;

						case 2:	//world

							$validPerms	=	['r','w','x','t','T','W','s','S','-'];

							foreach($value as $k=>$v){

								$v	=	sprintf('%s',$v);

								if($v=='='){

									$newPerms[]	=	$world[$k];
									continue;
										
								}

								if($v=='-'){

									$newPerms[]	=	$v;
									continue;

								}

								if(!in_array($v,$validPerms)){

									$value	=	sprintf('%s',$v);
									throw new \InvalidArgumentException("Invalid permission \"$v\"");

								}

								switch($k){

									case 0:
										if($v!=='r'){

											$msg	=	"Wrong placed argument \"$v\" should be 'r' or '-'";
											throw new \InvalidArgumentException($msg);

										}
									break;

									case 1:
										if($v!=='w'){

											$msg	=	"Wrong placed argument \"$v\" should be one of 'w','-'";
											throw new \InvalidArgumentException($msg);

										}
									break;

									case 2:
										$valid	=	['x','s','S','t','T','-'];

										if(!in_array($v,$valid)){

											$msg	=	"Wrong placed argument \"$v\" should be one of ";
											$msg	=	sprintf("%s %s",$msg,implode(',',$valid));

											throw new \InvalidArgumentException($msg);

										}
									break;

								}

								$newPerms[]	=	$v;

							}

						break;

					}

				}

				$obj->owner['read']		=	$newPerms[0];
				$obj->owner['write']		=	$newPerms[1];
				$obj->owner['execute']	=	$newPerms[2];

				$obj->group['read']		=	$newPerms[3];
				$obj->group['write']		=	$newPerms[4];
				$obj->group['execute']	=	$newPerms[5];

				$obj->world['read']		=	$newPerms[6];
				$obj->world['write']		=	$newPerms[7];
				$obj->world['execute']	=	$newPerms[8];

				$permissions	=	$newPerms;
				$mode				=	0;

				if ($permissions[0] == 'r') $mode += 0400; 
				if ($permissions[1] == 'w') $mode += 0200; 
				if ($permissions[2] == 'x') $mode += 0100; 
				else if ($permissions[2] == 's') $mode += 04100; 
				else if ($permissions[2] == 'S') $mode += 04000; 

				if ($permissions[3] == 'r') $mode += 040; 
				if ($permissions[4] == 'w') $mode += 020; 
				if ($permissions[5] == 'x') $mode += 010; 
				else if ($permissions[5] == 's') $mode += 02010; 
				else if ($permissions[5] == 'S') $mode += 02000; 

				if ($permissions[6] == 'r') $mode += 04; 
				if ($permissions[7] == 'w') $mode += 02; 
				if ($permissions[8] == 'x') $mode += 01; 
				else if ($permissions[8] == 't') $mode += 01001; 
				else if ($permissions[8] == 'T') $mode += 01000;

				$obj->value=decoct($mode);

				return $obj;

			}

			public function getValue(){

				return $this->value;

			}

			//The following method has been taken from the PHP manual
			//http://php.net/file_perms

			private static function fromFile($file){

				$perms	=	fileperms($file);

				clearstatcache(TRUE,$file);

				$obj		=	new static();

				if (($perms & 0xC000) == 0xC000) {
					// Socket
					$obj->info = 's';
				} elseif (($perms & 0xA000) == 0xA000) {
					// Symbolic Link
					$obj->info	= 'l';
				} elseif (($perms & 0x8000) == 0x8000) {
					// Regular
					$obj->info = '-';
				} elseif (($perms & 0x6000) == 0x6000) {
					// Block special
					$obj->info = 'b';
				} elseif (($perms & 0x4000) == 0x4000) {
					// Directory
					$obj->info = 'd';
				} elseif (($perms & 0x2000) == 0x2000) {
					// Character special
					$obj->info = 'c';
				} elseif (($perms & 0x1000) == 0x1000) {
					// FIFO pipe
					$obj->info = 'p';
				} else {

					// Unknown
					$obj->info = 'u';

				}

				// Owner
				$obj->owner['read']		=	(($perms & 0x0100) ? 'r' : '-');
				$obj->owner['write']		=	(($perms & 0x0080) ? 'w' : '-');
				$obj->owner['execute']	=	(($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) :
																				(($perms & 0x0800) ? 'S' : '-'));

				// Group
				$obj->group['read']		=	(($perms & 0x0020) ? 'r' : '-');
				$obj->group['write']		=	(($perms & 0x0010) ? 'w' : '-');
				$obj->group['execute']	=	(($perms & 0x0008) ?	(($perms & 0x0400) ? 's' : 'x' ) :
																				(($perms & 0x0400) ? 'S' : '-'));

				// World
				$obj->world['read']		=	(($perms & 0x0004) ? 'r' : '-');
				$obj->world['write']		=	(($perms & 0x0002) ? 'w' : '-');
				$obj->world['execute']	=	(($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) :
																				(($perms & 0x0200) ? 'T' : '-'));

				return $obj;

			}

			public function getInfo(){

				return $this->info;

			}

			public function getOwner(){

				$perms				=	new \stdClass();
				$perms->read		=	$this->owner['read']		==	'-'	?	FALSE	:	TRUE;
				$perms->write		=	$this->owner['write']	==	'-'	?	FALSE	:	TRUE;
				$perms->execute	=	$this->owner['execute']	==	'-'	?	FALSE	:	TRUE;

				return $perms;

			}

			public function getGroup(){

				$perms				=	new \stdClass();
				$perms->read		=	$this->group['read']		==	'-'	?	FALSE	:	TRUE;
				$perms->write		=	$this->group['write']	==	'-'	?	FALSE	:	TRUE;
				$perms->execute	=	$this->group['execute']	==	'-'	?	FALSE	:	TRUE;

				return $perms;

			}

			public function getWorld(){

				$perms				=	new \stdClass();
				$perms->read		=	$this->world['read']		==	'-'	?	FALSE	:	TRUE;
				$perms->write		=	$this->world['write']	==	'-'	?	FALSE	:	TRUE;
				$perms->execute	=	$this->world['execute']	==	'-'	?	FALSE	:	TRUE;

				return $perms;

			}

			public function toString($parameters=NULL){

				$perms	=	sprintf('%s%s%s%s',$this->info,implode('',$this->owner),implode('',$this->group),implode('',$this->world));

				return StringType::cast($perms,$parameters);

			}

			public function jsonSerialize(){

				return [
							'type'	=>	$this->info,
							'owner'	=>	$this->owner,
							'group'	=>	$this->group,
							'world'	=>	$this->world
				];
									
			}

			public function __toString(){

				return $this->toString()->valueOf();

			}

		}

	}
