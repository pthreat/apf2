<?php

	namespace apf\io\os\unix\validate{

		use apf\util\File									as	FileUtil;
		use apf\util\Variable							as	VarUtil;
		use apf\type\Str									as	StringType;
		use apf\type\SysUser								as	SysUserType;
		use apf\type\SysGroup							as	SysGroupType;
		use apf\type\collection\Parameter			as	ParameterType;
		use apf\type\file\Permission;
		use apf\exception\type\directory\NotFound	as	DirectoryNotFoundException;

		use apf\io\os\unix\validate\File				as	FileValidation;
		use apf\io\common\validate\Directory		as	CommonDirectoryValidation;

		class Directory extends CommonDirectoryValidation{

			public static function isReadable($dir,$parameters=NULL){
				echo "Check if it's readable";
			}

			public static function isWritable($dir,$parameters=NULL){
				echo "Check if it's writable";
			}

			public static function exists($dir,$parameters=NULL){
				echo "Check if it exists\n";
			}

			public static function hasFiles($dir,$parameters=NULL){
				echo "Validate if it has files\n";
			}

		}

	}
