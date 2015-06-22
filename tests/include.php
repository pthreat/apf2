<?php

	ini_set("display_errors","On");
	error_reporting(E_ALL);

	require	"class/core/Debug.class.php";
	require	"interface/Log.interface.php";
	require	"interface/type/Common.interface.php";
	require	"interface/type/Collection.interface.php";
	require	"trait/Traversable.trait.php";
	require	"trait/pattern/Singleton.trait.php";
	require	"trait/InnerLog.trait.php";

	require	"trait/type/parser/Parameter.trait.php";

	///////////////////////////////////////////////////
	//Convertible interfaces

	require	"interface/convertible/Boolean.interface.php";
	require	"interface/convertible/Char.interface.php";
	require	"interface/convertible/Str.interface.php";
	require	"interface/convertible/IntNum.interface.php";
	require	"interface/convertible/RealNum.interface.php";
	require	"interface/convertible/Vector.interface.php";
	require	"interface/convertible/Json.interface.php";
	require	"interface/Convertible.interface.php";
	require	"interface/type/base/Vector.interface.php";

	//End of convertible interfaces
	//////////////////////////////////////////////////


	require	"interface/OS.interface.php";

	require	"class/type/validate/base/Number.class.php";
	require	"class/type/validate/base/IntNum.class.php";
	require	"class/type/validate/base/Str.class.php";

	require	"class/core/Log.class.php";
	require	"class/core/Alias.class.php";

	require	"class/core/Config.class.php";
	require	"class/core/config/adapter/Ini.class.php";

	require	"class/core/Cache.class.php";
	require	"class/core/cache/Entry.class.php";
	require	"class/core/cache/adapter/file/Entry.class.php";
	require	"class/core/cache/adapter/File.class.php";

	require	"class/core/os/Common.class.php";
	require	"class/core/os/Linux.class.php";

	require	"class/core/OS.class.php";

	require	"class/type/exception/base/vector/ValueNotFound.class.php";

	require	"class/type/Common.class.php";
	require	"class/type/base/Boolean.class.php";
	require	"class/type/base/IntNum.class.php";

	require	"class/type/base/StrCommon.class.php";
	require	"class/type/base/Str.class.php";
	require	"class/type/base/Char.class.php";

	require	"class/type/base/RealNum.class.php";

	require	"class/type/base/VectorCommon.class.php";
	require	"class/type/base/StrDisk.class.php";
	require	"class/type/base/DiskVector.class.php";
	require	"class/type/base/Vector.class.php";

	//COMMON COLLECTIONS

	require	"class/type/collection/Common.class.php";
	require	"class/type/collection/common/UniqueIndex.class.php";
	require	"class/type/collection/common/UniqueValue.class.php";
	require	"class/type/collection/common/Disk.class.php";

	require	"class/type/collection/base/Str.class.php";
	require	"class/type/collection/base/Char.class.php";
	require	"class/type/collection/base/Vector.class.php";
	require	"class/type/collection/base/IntNum.class.php";

	require	"class/type/util/common/Variable.class.php";
	require	"class/type/util/common/Class_.class.php";
	require	"class/type/util/common/Obj.class.php";

	require	"class/type/util/base/Common.class.php";
	require	"class/type/util/base/IntNum.class.php";
	require	"class/type/util/base/Str.class.php";
	require	"class/type/util/base/Char.class.php";
	require	"class/type/util/base/Vector.class.php";


	require	"class/type/validate/common/Class_.class.php";
	require	"class/type/validate/common/Obj.class.php";
	require	"class/type/validate/common/Variable.class.php";


	require	"class/type/collection/custom/Parameter.class.php";
	require	"class/type/custom/Parameter.class.php";
	require	"class/type/parser/Parameter.class.php";

	require	"class/parser/exception/CouldNotParse.class.php";
	require	"class/parser/file/Ini.class.php";

	require	"class/type/exception/base/str/Encoding.class.php";
	require	"class/type/exception/base/str/UndefinedOffset.class.php";
	require	"class/type/exception/custom/parameter/NotFound.class.php";
	require	"class/type/exception/custom/parameter/NotInCase.class.php";
	require	"class/type/exception/base/vector/UndefinedIndex.class.php";
	require	"class/type/exception/base/str/EmptyString.class.php";
	require	"class/type/exception/common/Untraversable.class.php";

	
	////////////////////////////////////////////////////////////////////
	//Utilities 

	//Conversion utilities
	require	"class/util/convert/meassure/Byte.class.php";

	//End of conversion utilities
	////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////
	//Hardware related classes

	require	"class/hw/CPU.class.php";
	require	"class/hw/collection/CPU.class.php";
	require	"class/hw/Memory.class.php";
	require	"class/hw/disk/Partition.class.php";

	//End of hardware related classes
	////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////
	//OS ACL

	require	"interface/acl/os/User.interface.php";
	require	"interface/acl/os/Group.interface.php";

	require	"class/acl/os/common/Group.class.php";
	require	"class/acl/os/common/User.class.php";

	require	"class/acl/os/common/collection/Group.class.php";
	require	"class/acl/os/common/collection/User.class.php";

	//UNIX
	require	"class/acl/os/unix/Group.class.php";
	require	"class/acl/os/unix/User.class.php";

	//WIN
	require	"class/acl/os/win/Group.class.php";
	require	"class/acl/os/win/User.class.php";

	require	"class/acl/os/Group.class.php";
	require	"class/acl/os/User.class.php";

	////////////////////////////////////////////////////////////////////
	//Parsers

	require	"class/parser/math/Stack.class.php";
	require	"class/parser/math/expression/Terminal.class.php";
	require	"class/parser/math/expression/Parenthesis.class.php";
	require	"class/parser/math/expression/Number.class.php";

	require	"class/parser/math/operator/Common.class.php";
	require	"class/parser/math/operator/Addition.class.php";
	require	"class/parser/math/operator/Division.class.php";
	require	"class/parser/math/operator/Substraction.class.php";
	require	"class/parser/math/operator/Multiplication.class.php";

	require	"class/parser/Math.class.php";


	////////////////////////////////////////////////////////////////////
	//FILE

	//Interfaces
	require	"interface/io/parser/Permission.interface.php";
	require	"interface/io/File.interface.php";
	require	"interface/io/Directory.interface.php";
	require	"interface/io/util/File.interface.php";
	require	"interface/io/util/Directory.interface.php";
	require	"interface/io/validate/File.interface.php";
	require	"interface/io/validate/Directory.interface.php";

	//Exceptions

	require	"class/io/common/exception/file/CantCreate.class.php";
	require	"class/io/common/exception/file/NotARegularFile.class.php";
	require	"class/io/common/exception/file/NotFound.class.php";
	require	"class/io/common/exception/file/CouldNotCopy.class.php";
	require	"class/io/common/exception/file/CouldNotMove.class.php";
	require	"class/io/common/exception/file/NotWritable.class.php";
	require	"class/io/common/exception/file/NotReadable.class.php";
	require	"class/io/common/exception/directory/CantCreate.class.php";
	require	"class/io/common/exception/directory/NotFound.class.php";
	require	"class/io/common/exception/directory/NotWritable.class.php";
	require	"class/io/common/exception/directory/NotReadable.class.php";
	require	"class/io/common/exception/directory/Exists.class.php";
	require	"class/io/common/exception/directory/NotADirectory.class.php";

	//DATA SETS

	require	"interface/data/set/Charset.interface.php";
	require	"class/data/set/charset/exception/UndefinedCharacter.class.php";
	require	"class/data/set/Common.class.php";
	require	"class/data/set/Charset.class.php";
	require	"class/data/set/charset/Braille.class.php";
	require	"class/data/set/charset/Morse.class.php";

	//Common file classes

	require	"class/io/common/file/Handler.class.php";
	require	"class/io/common/parser/Permission.class.php";
	require	"class/io/common/directory/Handler.class.php";
	require	"class/io/common/util/File.class.php";
	require	"class/io/common/util/Directory.class.php";
	require	"class/io/common/validate/File.class.php";
	require	"class/io/common/validate/Directory.class.php";
	require	"class/io/common/File.class.php";
	require	"class/io/common/Directory.class.php";


	//OS specific classes

	//UNIX

	require	"class/io/os/unix/parser/Permission.class.php";
	require	"class/io/os/unix/file/Handler.class.php";
	require	"class/io/os/unix/directory/Handler.class.php";
	require	"class/io/os/unix/File.class.php";
	require	"class/io/os/unix/Directory.class.php";
	require	"class/io/os/unix/util/File.class.php";
	require	"class/io/os/unix/util/Directory.class.php";
	require	"class/io/os/unix/validate/Directory.class.php";
	require	"class/io/os/unix/validate/File.class.php";

	//Windows

	require	"class/io/os/win/parser/Permission.class.php";
	require	"class/io/os/win/file/Handler.class.php";
	require	"class/io/os/win/directory/Handler.class.php";
	require	"class/io/os/win/File.class.php";
	require	"class/io/os/win/Directory.class.php";
	require	"class/io/os/win/util/File.class.php";
	require	"class/io/os/win/util/Directory.class.php";
	require	"class/io/os/win/validate/Directory.class.php";
	require	"class/io/os/win/validate/File.class.php";


	//Aliasing (platform detection)

	require	"class/io/File.class.php";
	require	"class/io/Directory.class.php";
	require	"class/io/file/Handler.class.php";
	require	"class/io/directory/Handler.class.php";
	require	"class/io/util/File.class.php";
	require	"class/io/util/Directory.class.php";
	require	"class/io/validate/File.class.php";
	require	"class/io/validate/Directory.class.php";
	require	"class/io/parser/Permission.class.php";

