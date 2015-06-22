<?php

	namespace apf\io\common\parser{

		use apf\type\base\Str					as	StringType;
		use apf\type\base\Vector				as	VectorType;
		use apf\type\util\common\Variable	as	VarUtil;

		use apf\iface\convertible\Str			as	StringConvertible;
		use apf\iface\convertible\IntNum		as	IntConvertible;
		use apf\iface\convertible\Vector		as	VectorConvertible;

		use apf\iface\io\parser\Permission	as	PermissionInterface;

		abstract class Permission implements PermissionInterface,StringConvertible,IntConvertible,VectorConvertible{

			protected	$info		=	NULL;
			protected	$owner	=	Array('read'=>NULL,'write'=>NULL,'execute'=>NULL);
			protected	$group	=	Array('read'=>NULL,'write'=>NULL,'execute'=>NULL);
			protected	$world	=	Array('read'=>NULL,'write'=>NULL,'execute'=>NULL);


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

			public function toArray(){

				return VectorType::cast([
												'type'	=>	$this->info,
												'owner'	=>	$this->owner,
												'group'	=>	$this->group,
												'world'	=>	$this->world
				]);

			}

			public function toChar(){

				return CharType::cast($this->info);

			}

			//Return octal permission value as an integer
			public function toInt(){
			}

			public function __toString(){

				return $this->toString()->valueOf();

			}

		}

	}
