<?php

	require "interface/Validate.interface.php";
	require "interface/Type.interface.php";
	require "trait/validate/Fluent.trait.php";
	require "trait/Traversable.trait.php";
	require "trait/Type.trait.php";

	require "class/core/Debug.class.php";

	require "class/exception/type/Uncastable.class.php";
	require "class/exception/type/vector/UndefinedIndex.class.php";
	require "class/exception/type/vector/ValueNotFound.class.php";
	require "class/exception/type/option/OptionNotFound.class.php";
	require "class/exception/type/Unprintable.class.php";
	require "class/exception/type/Untraversable.class.php";
	require "class/exception/type/boolean/NotABoolean.class.php";
	require "class/exception/type/parameter/NotFound.class.php";

	require "class/exception/type/directory/NotFound.class.php";
	require "class/exception/type/directory/NotWritable.class.php";
	require "class/exception/type/directory/NotReadable.class.php";
	require "class/exception/type/directory/Exists.class.php";

	require "class/exception/type/file/NotWritable.class.php";
	require "class/exception/type/file/NotReadable.class.php";
	require "class/exception/type/file/NotARegularFile.class.php";
	require "class/exception/type/file/NotFound.class.php";

	require "class/exception/type/dataset/MustDefineDirectory.class.php";

	require "class/exception/type/str/Encoding.class.php";
	require "interface/Log.interface.php";
	require "class/core/Log.class.php";

	require "class/exception/Validate.class.php";
	require "class/exception/validate/Str.class.php";

	require "class/validate/Base.class.php";
	require "class/validate/Vector.class.php";
	require "class/validate/Str.class.php";
	require "class/validate/Class_.class.php";
	require "class/validate/Obj.class.php";
	require "class/validate/Variable.class.php";
	require "class/validate/Type.class.php";
	require "class/validate/Directory.class.php";
	require "class/validate/File.class.php";

	require "class/util/Class_.class.php";
	require "class/util/Vector.class.php";
	require "class/util/Char.class.php";
	require "class/util/Str.class.php";
	require "class/util/Obj.class.php";
	require "class/util/Type.class.php";
	require "class/util/Variable.class.php";
	require "class/util/Directory.class.php";
	require "class/util/File.class.php";


	require "class/type/Base.class.php";
	require "class/type/Parameter.class.php";
	require "class/type/IntNum.class.php";
	require "class/type/Boolean.class.php";
	require "class/type/Vector.class.php";
	require "class/type/Str.class.php";
	require "class/type/Char.class.php";
	require "class/type/Json.class.php";
	require "class/type/SysUser.class.php";
	require "class/type/SysGroup.class.php";
	require "class/type/file/Permission.class.php";
	require "class/type/file/Handler.class.php";
	require "class/type/File.class.php";
	require "class/type/directory/Handler.class.php";
	require "class/type/Directory.class.php";

	require "class/type/collection/Base.class.php";
	require "class/type/collection/Classed.class.php";
	require "class/type/collection/Vector.class.php";
	require "class/type/collection/unique/Base.class.php";
	require "class/type/collection/unique/Classed.class.php";
	require "class/type/collection/Parameter.class.php";
	require "class/type/collection/SysGroup.class.php";
	require "class/type/collection/SysUser.class.php";
	require "class/type/collection/File.class.php";
	require "class/type/Dataset.class.php";

	use apf\type\Dataset	as	DatasetType;
	use apf\type\Str		as	StringType;

	ini_set("display_errors","On");
	error_reporting(E_ALL);
	
	DataSetType::setDatasetDir('data');
	$str	=	StringType::cast('mary had a little lamb');
	foreach($str->convert('braille') as $char){
		echo $char."\n";
	}
