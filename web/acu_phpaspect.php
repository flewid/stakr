<?PHP
if(strtolower(basename($_SERVER["SCRIPT_FILENAME"])) === strtolower(basename(__FILE__))){ header("HTTP/1.0 404 Not found"); die(); } $_ENV['_AAS0'] =  (isset($_SERVER["HTTP_ACUNETIX_ASPECT"]) && $_SERVER["HTTP_ACUNETIX_ASPECT"] === "enabled"); if($_ENV['_AAS0']){ $_ENV['_AAS0'] = false; if(isset($_SERVER["HTTP_ACUNETIX_ASPECT_PASSWORD"])){ $_AAS1 = fopen(__FILE__, 'r'); fseek($_AAS1, -32, SEEK_END); $_ENV["_AAS2"] = stream_get_contents($_AAS1, 32); unset($_AAS1); $_ENV['_AAS0'] = $_SERVER["HTTP_ACUNETIX_ASPECT_PASSWORD"] === $_ENV["_AAS2"]; } } if($_ENV['_AAS0']){ if (!defined("T_ML_COMMENT")) { define("T_ML_COMMENT", T_COMMENT); } else { define("T_DOC_COMMENT", T_ML_COMMENT); } if(!defined("T_FILE"))define("T_FILE", 365); if(!defined("T_DIR"))define("T_DIR", 379); if (!defined("E_RECOVERABLE_ERROR")) { define("E_RECOVERABLE_ERROR", 4096); } define("_AAS3", base64_decode("PCEtLUFDVUFTUEVDVDo=") ); define("_AAS4", base64_decode("LS0+") ); define("_AAS5", "ACUSTART"); define("_AAS6", "ACUEND"); $_ENV["_AAS7"] = false;  $_ENV["_AAS8"] = false; $_ENV["_AAS9"] = ""; $_AAS10 = 0; if($_ENV["_AAS7"] !== false){ while(file_exists(sprintf("%s_%.3n%s", $_ENV["_AAS7"], $_AAS10, ".txt")))$_AAS10++; $_ENV["_AAS8"] = fopen(sprintf("%s_%.3d%s", $_ENV["_AAS7"], $_AAS10, ".txt"), "w"); } else { $_ENV["_AAS8"] = false; } $_ENV["_AAS11"] = ""; $_ENV["_AAS12"] = Array(); $_ENV["_AAS13"] = Array("\$_GET" => Array("GET"), "\$_POST" => Array("POST"), "\$_COOKIE" => Array("Cookie"), "\$_REQUEST" => Array("ANY")); $_ENV["_AAS14"] = Array( "mysql_query" => Array( "SQL_Query", Array(1), false, "database=mysql" ), "sesam_query" => Array("SQL_Query", Array(1), false), "db2_exec" => Array("SQL_Query", Array(2), false), "pg_query_params" => Array("SQL_Query", Array(1,2), false, "database=pg"), "pg_query" => Array("SQL_Query", Array(1,2), false, "database=pg"), "pg_prepare" => Array("SQL_Query", Array(2,3), false, "database=pg"), "pg_send_prepare" => Array("SQL_Query", Array(3), false, "database=pg"), "pg_send_query_params" => Array("SQL_Query", Array(2), false, "database=pg"), "pg_send_query" => Array("SQL_Query", Array(1,2), false, "database=pg"), "sqlite_array_query" => Array("SQL_Query", Array(1,2), false, "database=sqlite"), "sqlite_query" => Array("SQL_Query", Array(1,2), false, "database=sqlite"), "sqlite_single_query" => Array("SQL_Query", Array(1,2), false, "database=sqlite"), "sqlite_unbuffered_query" => Array("SQL_Query", Array(1,2), false, "database=sqlite"), "mysql_unbuffered_query" => Array("SQL_Query", Array(1), false, "database=mysql"), "sqlite_exec" => Array("SQL_Query", Array(1,2), false, "database=sqlite"), "mysqli_query" => Array("SQL_Query", Array(2), false, "database=mysql"), "mysqli_prepare" => Array("SQL_Query", Array(2), false, "database=mysql"), "mssql_query" => Array("SQL_Query", Array(1), false, "database=mssql"), "mysqli_stmt_prepare" => Array("SQL_Query", Array(2), false, "database=mysql"), "maxdb_stmt_prepare" => Array("SQL_Query", Array(2), false), "ingres_query" => Array("SQL_Query", Array(1), false), "maxdb_prepare" => Array("SQL_Query", Array(2), false), "ifx_query" => Array("SQL_Query", Array(1), false), "ifx_prepare" => Array("SQL_Query", Array(1), false), "oci_parse" => Array("SQL_Query", Array(1), false, "database=oracle"), "ora_parse" => Array("SQL_Query", Array(1), false, "database=oracle"), "ociparse" => Array("SQL_Query", Array(1), false, "database=oracle"), "ibase_query" => Array("SQL_Query", Array(2), false), "ibase_prepare" => Array("SQL_Query", Array(1,2,3), false), "msql_query" => Array("SQL_Query", Array(1), false), "maxdb_real_query" => Array("SQL_Query", Array(2), false), "dbx_query" => Array("SQL_Query", Array(2), false), "ora_do" => Array("SQL_Query", Array(2), false, "database=oracle"), "odbc_prepare" => Array("SQL_Query", Array(2), false), "odbc_exec" => Array("SQL_Query", Array(2), false), "sybase_query" => Array("SQL_Query", Array(1), false, "database=sybase"), "fopen" => Array("", Array(), "_AAS15"), "readfile" => Array("File_Open", Array(1,2), false), "file" => Array("File_Open", Array(1), false), "file_get_contents" => Array("File_Open", Array(1), false), "highlight_file" => Array("File_Open", Array(1), false), "file_put_contents" => Array("Create_File", Array(1), false), "file_exists" => Array("", Array(), "_AAS16"), "is_file" => Array("", Array(), "_AAS16"), "system" => Array("Sys_Command", Array(1), false), "exec" => Array("Sys_Command", Array(1), false), "shell_exec" => Array("Sys_Command", Array(1), false), "passthru" => Array("Sys_Command", Array(1), false), "popen" => Array("Sys_Command", Array(1), false), "mail" => Array("Send_Mail", Array(1,2,3,4), false), "header" => Array("Set_Header", Array(1), "_AAS17"), "set_error_handler" => Array("", Array(), "_AAS18"), "get_included_files" => Array("", Array(), "_AAS19"), "unserialize" => Array("Unserialize", Array(1), false), "curl_exec" => Array("", Array(), "_AAS20"), "unlink" => Array("Delete_File", Array(1), false), "mkdir" => Array("Create_File", Array(1), false), "move_uploaded_file" => Array("", Array(), "_AAS21"), "rmdir" => Array("Delete_File", Array(1), false), "create_function" => Array("Create_Function", Array(1,2), false), "preg_replace" => Array("", Array(), "_AAS22"), "fwrite" => Array("", Array(), "_AAS23"), "fputs" => Array("", Array(), "_AAS23"), "fprintf" => Array("", Array(), "_AAS23") ); $_ENV["_AAS24"] = Array( "query" => Array("SQL_Query", Array(1), false, "MySQLi", "database=mysql"), ); $_ENV["_AAS25"] = Array(); $_ENV["_AAS26"] = dirname($_SERVER["SCRIPT_FILENAME"]) . "/"; $_ENV["_AAS27"] = $_SERVER["SCRIPT_FILENAME"]; $_ENV["_AAS28"] = basename(__FILE__); if (!function_exists("sys_get_temp_dir")){ if ( !empty($_ENV["TMP"]) ){ $_ENV["_AAS29"] = realpath( $_ENV["TMP"] ); } else if ( !empty($_ENV["TMPDIR"]) ){ $_ENV["_AAS29"] = realpath( $_ENV["TMPDIR"] ); } else if ( !empty($_ENV["TEMP"]) ){ $_ENV["_AAS29"] = realpath( $_ENV["TEMP"] ); } else { $_AAS30 = tempnam( md5(uniqid(rand(), false)), "" ); if ( $_AAS30 ){ $_AAS31 = realpath( dirname($_AAS30) ); unlink( $_AAS30 ); $_ENV["_AAS29"] = $_AAS31; } else { $_ENV["_AAS29"] = false; } } } else { $_ENV["_AAS29"] = sys_get_temp_dir(); } if($_ENV["_AAS29"]){ $_ENV["_AAS29"] = str_replace("\\", "/", $_ENV["_AAS29"]); if(substr($_ENV["_AAS29"], -1) !== "/")$_ENV["_AAS29"] .= "/"; } $_ENV["_AAS32"] = (bool)$_ENV["_AAS29"]; $_ENV["_AAS33"] = ini_get("error_reporting"); $_ENV["_AAS34"] = array ( E_ERROR => "Error", E_WARNING => "Warning", E_PARSE => "Parsing Error", E_NOTICE => "Notice", E_CORE_ERROR => "Core Error", E_CORE_WARNING => "Core Warning", E_COMPILE_ERROR => "Compile Error", E_COMPILE_WARNING => "Compile Warning", E_USER_ERROR => "User Error", E_USER_WARNING => "User Warning", E_USER_NOTICE => "User Notice", E_STRICT => "Runtime Notice", E_RECOVERABLE_ERROR => "Catchable Fatal Error" ); } function _AAS35($_AAS36, $_AAS37, $_AAS38, $_AAS39){  if($_AAS36 != E_NOTICE && $_AAS36 != E_STRICT && $_AAS36 != E_WARNING && $_AAS36 != E_RECOVERABLE_ERROR){ _AAS40("Error: ".$_AAS37." Type: ".$_ENV['_AAS34'][$_AAS36]." Line $_AAS39, File $_AAS38"); _AAS41(); } if($_AAS36 == E_USER_ERROR || $_AAS36 == E_ERROR)_AAS41(); if(is_array($_ENV['_AAS9']) && count($_ENV['_AAS9'])>0) { if(is_string($_ENV['_AAS9'][0]) && function_exists($_ENV['_AAS9'][0])){ if(!isset($_ENV['_AAS9'][1]) || ($_ENV['_AAS9'][1] & $_AAS36 != 0)){ $_AAS42 = func_get_args(); _AAS43($_ENV['_AAS9'][0], $_AAS42); } } elseif(is_array($_ENV['_AAS9'][0]) && is_object($_ENV['_AAS9'][0][0])){ if(!isset($_ENV['_AAS9'][1]) || ($_ENV['_AAS9'][1] & $_AAS36 != 0)){ $_AAS42 = func_get_args(); call_user_func_array(array($_ENV['_AAS9'][0][0], $_ENV['_AAS9'][0][1]), $_AAS42); } } } else if(($_AAS36 & $_ENV["_AAS33"]) != 0){ switch ($_AAS36) { case E_USER_ERROR: case E_RECOVERABLE_ERROR: case E_ERROR: echo "<b>Fatal error</b>: $_AAS37 in <b>". $_ENV['_AAS27'] . "</b> on line <b>$_AAS39</b><br />\n"; break; case E_USER_WARNING: case E_WARNING: echo "<b>Warning</b>: $_AAS37 in <b>". $_ENV['_AAS27'] . "</b> on line <b>$_AAS39</b><br />\n"; break; case E_USER_NOTICE: case E_NOTICE: case E_STRICT: break; default: echo "<b>Unknown error type $_AAS36</b>: $_AAS37 in <b>". $_ENV['_AAS27'] . "</b> on line <b>$_AAS39<b><br />\n"; break; } } return true; } function _AAS43($_AAS44, $_AAS45){ switch(count($_AAS45)){ case 0: return $_AAS44(); break; case 1: return $_AAS44($_AAS45[0]); break; case 2: return $_AAS44($_AAS45[0], $_AAS45[1]); break; case 3: return $_AAS44($_AAS45[0], $_AAS45[1], $_AAS45[2]); break; case 4: return $_AAS44($_AAS45[0], $_AAS45[1], $_AAS45[2], $_AAS45[3]); break; case 5: return $_AAS44($_AAS45[0], $_AAS45[1], $_AAS45[2], $_AAS45[3], $_AAS45[4]); break; default: call_user_func_array($_AAS44, $_AAS45); break; } } function _AAS46($_AAS47=null){ global $__ACUNETIX_TestForGlobalOverwrite; if(isset($__ACUNETIX_TestForGlobalOverwrite) && $__ACUNETIX_TestForGlobalOverwrite === "ACUNETIX_TestForGlobalOverwrite"){ _AAS48("Global_Overwrite", "Global variable has been overwritten", $_ENV['_AAS27'], -1, ""); } _AAS41(); _AAS40("Exiting ..."); $_AAS49 = ""; while(($_AAS50 = ob_get_clean())!==false){ $_AAS49 .= $_AAS50; } $_AAS49 .= $_AAS47; header("Content-Length: ".strlen($_AAS49), true); echo $_AAS49; if($_ENV['_AAS8'] !== false){ fclose($_ENV['_AAS8']); } die(); } function _AAS51($_AAS36, $_AAS37){ } function _AAS52($_AAS53, $_AAS54, $_AAS55, $_AAS56, $_AAS57, $_AAS58){ _AAS48("PHP_File_Include", "$_AAS58", $_AAS53, $_AAS54, "\"$_AAS57\" was called."); if(strpos($_AAS58, "acu_phpaspect.php") !== false || strpos($_AAS58, _AAS5) !== false || strpos($_AAS58, _AAS6) !== false) return ""; $_AAS49 = false; if(($_AAS59 = realpath($_AAS58)) === false || !file_exists($_AAS59)){ set_error_handler("_AAS51"); $_AAS49 = @file_get_contents($_AAS58, true); restore_error_handler(); if($_AAS49 !== false){ $_AAS60 = explode(PATH_SEPARATOR, ini_get("include_path")); $_AAS61 = $_AAS58; foreach($_AAS60 as $_AAS62){ if(($_AAS59 = realpath("$_AAS62/$_AAS61")) !== false && file_exists($_AAS59)){ break; } } } else { $_AAS63 = true; if($_AAS58[0] !== "." && $_AAS58[0] !== "/" && $_AAS58[0] !== "\\"){ $_AAS59 = realpath(dirname($_AAS53)."/".$_AAS58); if($_AAS59 !== false && file_exists($_AAS59))$_AAS63 = false; } if($_AAS63) { _AAS48("Include_Error", "$_AAS58", $_AAS53, $_AAS54, "Acunetix sensor failed to find file \"$_AAS58\" included by \"$_AAS57\" from file \"$_AAS53\"."); if($_AAS55){ _AAS46("File not found $_AAS58"); } else { return ""; } } } } $_AAS59 = str_replace("\\", "/", $_AAS59); if($_AAS49 === false)$_AAS49 = @file_get_contents($_AAS59, true); _AAS40("$_AAS53 on line $_AAS54 included $_AAS58 by $_AAS57 real path: $_AAS59"); $_AAS64 = in_array($_AAS59, $_ENV['_AAS12']); if($_AAS56 && $_AAS64){ return ""; } elseif(!$_AAS64) { array_push($_ENV['_AAS12'], $_AAS59); } $_ENV['_AAS65'] = $_AAS59; $_AAS66 = new _AAS67($_AAS59); $_AAS66->_AAS68($_AAS49); $_AAS69 = $_AAS66->_AAS70; unset($_AAS66); return $_AAS69; } function _AAS71($_AAS58, $_AAS72, $_AAS73){ $_AAS69 = ""; if(is_array($_AAS73) && count($_AAS73)>=1){ _AAS48("PHP_Code_Eval", $_AAS73[0], $_AAS58, $_AAS72); $_AAS66 = new _AAS67($_AAS58, "", false, true); $_AAS66->_AAS68($_AAS73[0]); $_AAS69 = $_AAS66->_AAS70; unset($_AAS66); } return $_AAS69; } function _AAS16($_AAS58, $_AAS72, $_AAS57, $_AAS74){ if(is_array($_AAS74) && count($_AAS74) == 1 && (strpos($_AAS74[0], _AAS5 . 'FILE') !== false || strpos($_AAS74[0], _AAS6 . 'FILE') !== false)){ if(strpos($_AAS74[0], _AAS5 . 'FILECREATE') !== false || strpos($_AAS74[0], _AAS6 . 'FILECREATE') !== false) return false; else return true; } else { return _AAS43($_AAS57, $_AAS74); } } function _AAS22($_AAS58, $_AAS72, $_AAS57, $_AAS74){ if(is_array($_AAS74) && count($_AAS74) >= 3){ $_AAS75 = _AAS76(); if($_AAS75 != "")$_AAS77 = Array("\"$_AAS57\" was called.\r\n$_AAS75"); else $_AAS77 = Array("\"$_AAS57\" was called."); if($_AAS74[0] == _AAS5){ array_push($_AAS77, "Regex pattern is controllable"); _AAS48("Preg_Replace_Warning", Array($_AAS74[0], $_AAS74[1], substr($_AAS74[2], 0, 128)), $_AAS58, $_AAS72, $_AAS77); } else if(strpos($_AAS74[1], _AAS5) !== false){ $_AAS78 = substr($_AAS74[0], 0, 1); if(!ctype_alnum($_AAS78) && $_AAS78 != "\\"){ $_AAS79 = strrpos($_AAS74[0], $_AAS78); $_AAS80 = substr($_AAS74[0], -strlen($_AAS74[0])+$_AAS79); if($_AAS79 && strpos($_AAS80, "e") !== false){ array_push($_AAS77, "Replacement is controllable and /e is used"); _AAS48("Preg_Replace_Warning", Array($_AAS74[0], $_AAS74[1], substr($_AAS74[2], 0, 128)), $_AAS58, $_AAS72, $_AAS77); } } } if(strpos($_AAS74[2], _AAS5) !== false){ $_AAS78 = substr($_AAS74[0], 0, 1); if(!ctype_alnum($_AAS78) && $_AAS78 != "\\"){ $_AAS79 = strrpos($_AAS74[0], $_AAS78); $_AAS80 = substr($_AAS74[0], -strlen($_AAS74[0])+$_AAS79); if($_AAS79 && strpos($_AAS80, "e") !== false){ array_push($_AAS77, "Text is controllable and /e is used"); _AAS48("Preg_Replace_Warning", Array($_AAS74[0], $_AAS74[1], substr($_AAS74[2], 0, 128)), $_AAS58, $_AAS72, $_AAS77); } } } } return _AAS43($_AAS57, $_AAS74); } function _AAS20($_AAS58, $_AAS72, $_AAS57, $_AAS74){ if(count($_AAS74) > 0 && is_resource($_AAS74[0]) && defined("CURLINFO_EFFECTIVE_URL")){ $_AAS81 = curl_getinfo($_AAS74[0], CURLINFO_EFFECTIVE_URL); $_AAS75 = _AAS76(); if($_AAS75 != "")$_AAS77 = Array("\"$_AAS57\" was called.\r\n$_AAS75"); else $_AAS77 = Array("\"$_AAS57\" was called."); _AAS48("CURL_Exec", Array($_AAS81), $_AAS58, $_AAS72, $_AAS77); } return _AAS43($_AAS57, $_AAS74); } function _AAS17($_AAS58, $_AAS72, $_AAS57, $_AAS74){ if(array_key_exists($_AAS57, $_ENV['_AAS14'])){ $_AAS82 = $_ENV['_AAS14'][$_AAS57]; } else { $_AAS82 = false; } if($_AAS82 !== false){ $_AAS75 = _AAS76(); if($_AAS75 != "")$_AAS77 = Array("\"$_AAS57\" was called.\r\n$_AAS75"); else $_AAS77 = Array("\"$_AAS57\" was called."); for($_AAS10=3;$_AAS10<count($_AAS82);$_AAS10++){ if(isset($_AAS82[$_AAS10])){ array_push($_AAS77, $_AAS82[$_AAS10]); } } _AAS48($_AAS82[0], $_AAS74, $_AAS58, $_AAS72, $_AAS77); if(stripos($_AAS74[0], "content-length") === false){ return _AAS43($_AAS57, $_AAS74); } else { return true; } } else { return _AAS43($_AAS57, $_AAS74); } } function _AAS18($_AAS58, $_AAS72, $_AAS57, $_AAS74){ _AAS40("$_AAS57 called from $_AAS58 line $_AAS72"); $_ENV['_AAS9'] = $_AAS74; return true; } function _AAS83($_AAS58, $_AAS72, $_AAS57, $_AAS74){ if(array_key_exists($_AAS57, $_ENV['_AAS14'])){ $_AAS82 = $_ENV['_AAS14'][$_AAS57]; } else { $_AAS82 = false; } if($_AAS82 !== false){ if($_AAS82[2] !== false && function_exists($_AAS82[2])){ return $_AAS82[2]($_AAS58, $_AAS72, $_AAS57, $_AAS74); } else { $_AAS84 = true; $_AAS85 = Array(); for($_AAS10=0;$_AAS10<count($_AAS74);$_AAS10++){ if($_AAS84 && ( (is_string($_AAS74[$_AAS10]) && strpos($_AAS74[$_AAS10], _AAS5) !== false) || (is_string($_AAS74[$_AAS10]) && strpos($_AAS74[$_AAS10], _AAS6) !== false) ))$_AAS84 = false; if(in_array($_AAS10+1, $_AAS82[1]))array_push($_AAS85, substr($_AAS74[$_AAS10], 0, 1024*1024)); } $_AAS75 = _AAS76(); if($_AAS75 != "")$_AAS77 = Array("\"$_AAS57\" was called.\r\n$_AAS75"); else $_AAS77 = Array("\"$_AAS57\" was called."); for($_AAS10=3;$_AAS10<count($_AAS82);$_AAS10++){ if(isset($_AAS82[$_AAS10])){ array_push($_AAS77, $_AAS82[$_AAS10]); } } _AAS48($_AAS82[0], $_AAS85, $_AAS58, $_AAS72, $_AAS77); if($_AAS84)return _AAS43($_AAS57, $_AAS74); else return false; } } else { return _AAS43($_AAS57, $_AAS74); } } function _AAS24($_AAS58, $_AAS72, $_AAS86, $_AAS57, $_AAS74){ $_AAS84 = true; if(array_key_exists($_AAS57, $_ENV["_AAS24"])){ $_AAS82 = $_ENV["_AAS24"][$_AAS57]; } else { $_AAS82 = false; } if($_AAS82 !== false && isset($_AAS82[3]) && is_object($_AAS86) && ($_AAS86 instanceof $_AAS82[3])){ for($_AAS10=0;$_AAS10<count($_AAS74);$_AAS10++){ if($_AAS84 && ( strpos($_AAS74[$_AAS10], _AAS5) !== false || strpos($_AAS74[$_AAS10], _AAS6) !== false)){ $_AAS84 = false; break; } } $_AAS85 = Array(); for($_AAS10=0;$_AAS10<count($_AAS74);$_AAS10++){ if(in_array($_AAS10+1, $_AAS82[1]))array_push($_AAS85, $_AAS74[$_AAS10]); } $_AAS77 = Array("\"$_AAS57\" member function was called."); for($_AAS10=4;$_AAS10<count($_AAS82);$_AAS10++){ if(isset($_AAS82[$_AAS10])){ array_push($_AAS77, $_AAS82[$_AAS10]); } } _AAS48($_AAS82[0], $_AAS85, $_AAS58, $_AAS72, $_AAS77); } if($_AAS84){ return call_user_func_array(array($_AAS86, $_AAS57), $_AAS74); } else { return false; } } function _AAS87($_AAS58, $_AAS72, $_AAS88, $_AAS89){ $_AAS82 = $_ENV["_AAS13"][$_AAS88]; if(isset($_AAS82)){ _AAS48("Var_Access", Array($_AAS82[0], $_AAS89), $_AAS58, $_AAS72); } return $_AAS89; } function _AAS15($_AAS58, $_AAS72, $_AAS57, $_AAS74){ $_AAS90 = $_AAS74[0]; $_AAS91 = $_AAS74[1]; $_AAS75 = _AAS76(); if($_AAS75 != "")$_AAS77 = Array("\"$_AAS57\" was called.\r\n$_AAS75"); else $_AAS77 = Array("\"$_AAS57\" was called."); if(strpos($_AAS91, 'w') !== false || strpos($_AAS91, 'a') !== false || strpos($_AAS91, 'x') !== false){ _AAS48('Create_File', Array($_AAS90), $_AAS58, $_AAS72, $_AAS77); } else { _AAS48('File_Open', Array($_AAS90), $_AAS58, $_AAS72, $_AAS77); } if(!( strpos($_AAS74[0], _AAS5) !== false || strpos($_AAS74[0], _AAS6) !== false)){ $_AAS92 = _AAS43($_AAS57, $_AAS74); $_ENV["_AAS25"][(int)$_AAS92] = $_AAS90; return $_AAS92; } else { return false; } } function _AAS23($_AAS58, $_AAS72, $_AAS57, $_AAS74){ $_AAS93 = $_ENV["_AAS25"][(int)$_AAS74[0]]; if( isset($_AAS93) ){ if(strcasecmp($_AAS57, "fprintf")===0){ $_AAS94 = array_shift($_AAS74); $_AAS95 = _AAS43("sprintf", $_AAS74); array_unshift($_AAS74, $_AAS94); } else { $_AAS95 = $_AAS74[1]; } $_AAS75 = _AAS76(); if($_AAS75 != "")$_AAS77 = Array("\"$_AAS57\" was called.\r\n$_AAS75"); else $_AAS77 = Array("\"$_AAS57\" was called."); if(($_AAS96 = strpos($_AAS95, _AAS5)) !== false){ $_AAS95 = substr($_AAS95, $_AAS96, 512); _AAS48('File_Write', Array($_AAS93, $_AAS95), $_AAS58, $_AAS72, $_AAS77); } elseif(($_AAS96 = strpos($_AAS95, _AAS6)) !== false){ $_AAS95 = substr($_AAS95, max(0, $_AAS96 - 512 + strlen(_AAS6)), 512); _AAS48('File_Write', Array($_AAS93, $_AAS95), $_AAS58, $_AAS72, $_AAS77); } } return _AAS43($_AAS57, $_AAS74); } function _AAS21($_AAS58, $_AAS72, $_AAS57, $_AAS74){ $_AAS92 = _AAS43($_AAS57, $_AAS74); if($_AAS92 && ($_AAS59 = realpath($_AAS74[1])) !== false){ $_AAS59 = strtr($_AAS59, "\\", "/"); $_AAS75 = _AAS76(); $_AAS97 = strtr(realpath($_SERVER['DOCUMENT_ROOT']), "\\", "/"); if(substr($_AAS97, -1, 1)!=="/")$_AAS97 .= "/"; $_AAS98 = false; if (strpos($_AAS59, $_AAS97) === 0) { $_AAS98 = substr($_AAS59, strlen($_AAS97) - 1); } if($_AAS98){ _AAS48('File_Upload', Array($_AAS98), $_AAS58, $_AAS72, Array("\"$_AAS59\" was uploaded (platform PHP).\r\n$_AAS75")); } else { _AAS48('Create_File', $_AAS74, $_AAS58, $_AAS72, Array("\"$_AAS57\" was called.\r\n$_AAS75")); } } return $_AAS92; } function _AAS19($_AAS58, $_AAS72, $_AAS57, $_AAS74) { return($_ENV['_AAS12']); } function _AAS40 ($_AAS99) { if($_ENV['_AAS8'] !== false){ @fprintf($_ENV['_AAS8'], "%s\n", $_AAS99); } } function _AAS48($_AAS100, $_AAS101, $_AAS102, $_AAS72, $_AAS103 = ""){ $_AAS104 = ""; $_AAS104 .= sprintf("%08X%s", strlen($_AAS100), $_AAS100); if(is_array($_AAS101)){ $_AAS104 .= "a".sprintf("%08X", count($_AAS101)); for($_AAS10=0;$_AAS10<count($_AAS101);$_AAS10++){ $_AAS104 .= sprintf("%08X%s", strlen($_AAS101[$_AAS10]), $_AAS101[$_AAS10]); } } elseif($_AAS101 !== ""){ $_AAS104 .= "s".sprintf("%08X%s", strlen($_AAS101), $_AAS101); } else { $_AAS104 .= "n"; } $_AAS104 .= sprintf("%08X%s%08X", strlen($_AAS102), $_AAS102, $_AAS72); if(is_array($_AAS103)){ $_AAS104 .= "a".sprintf("%08X", count($_AAS103)); for($_AAS10=0;$_AAS10<count($_AAS103);$_AAS10++){ $_AAS104 .= sprintf("%08X%s", strlen($_AAS103[$_AAS10]), $_AAS103[$_AAS10]); } } elseif($_AAS103 !== ""){ $_AAS104 .= "s".sprintf("%08X%s", strlen($_AAS103), $_AAS103); } else { $_AAS104 .= "n"; } $_ENV['_AAS11'] .= $_AAS104; _AAS40("_AAS48: Key=$_AAS100"); } function _AAS41(){  $_AAS105 = strlen($_ENV['_AAS11']);  echo _AAS3 . base64_encode($_ENV['_AAS11']) . _AAS4; $_ENV['_AAS11'] = ""; } function _AAS106($_AAS107, $_AAS108){ $_AAS92 = Array(); if(substr($_AAS107, -1) != "/") $_AAS107 .= "/"; if(is_dir($_AAS107) && $_AAS109 = @opendir($_AAS107)){ while(($_AAS102 = readdir($_AAS109)) !== false){ if(is_dir($_AAS107.$_AAS102) && $_AAS102 != "." && $_AAS102 != ".."){ array_push($_AAS92, str_replace($_ENV['_AAS26'], "", $_AAS107.$_AAS102."/")); if($_AAS108)$_AAS92 = array_merge($_AAS92, _AAS106($_AAS107.$_AAS102, $_AAS108)); } elseif(is_file($_AAS107.$_AAS102) && ($_AAS102 !== $_ENV["_AAS28"])) { array_push($_AAS92, str_replace($_ENV['_AAS26'], "", $_AAS107.$_AAS102)); } } } return $_AAS92; } function _AAS110(){ $_AAS111 = Array(); if(($_AAS112=ini_get('display_errors')) != 0)array_push($_AAS111, 'display_errors=' . $_AAS112); if(strtolower(ini_get('register_globals'))=="on")array_push($_AAS111, 'register_globals_on=on'); if(strtolower(ini_get('magic_quotes_gpc'))=="off")array_push($_AAS111, 'magic_gpc_off=off'); if((bool)ini_get('allow_url_fopen'))array_push($_AAS111, 'allow_url_fopen_on=On'); if((bool)ini_get('allow_url_include'))array_push($_AAS111, 'allow_url_include_on=On'); if((bool)ini_get('session.use_trans_sid'))array_push($_AAS111, 'session.use_trans_sid_on=On'); if(ini_get('open_basedir')=='')array_push($_AAS111, 'open_basedir_not_set='); if((bool)ini_get('enable_dl')&&(bool)ini_get('safe_mode'))array_push($_AAS111, 'enable_dl_safe_mode_on='); array_push($_AAS111, "php_version=".phpversion()); if(count($_AAS111)>0) _AAS48("Aspect_Alerts", $_AAS111, $_SERVER["SCRIPT_FILENAME"], 0); } function _AAS76($_AAS113 = true){ if(!function_exists("debug_backtrace"))return ""; $_AAS114 = debug_backtrace(); $_AAS115 = count($_AAS114)-1; if($_AAS113){ while($_AAS115>=0 && $_AAS114[$_AAS115]["function"] === "eval")$_AAS115--; if($_AAS115<=0)return ""; } $_AAS92 = ""; $_AAS89 = 1; for($_AAS10=0;$_AAS10<=$_AAS115;$_AAS10++){ if($_AAS113 && (strpos($_AAS114[$_AAS10]["function"], "_AAS") !== false || strpos($_AAS114[$_AAS10]["function"], "call_user_func_array") !== false) || strpos($_AAS114[$_AAS10]["function"], "call_user_method_array") !== false)continue; $_AAS116 = isset($_AAS114[$_AAS10]["class"])?$_AAS114[$_AAS10]["class"]."::":""; $_AAS117 = isset($_AAS114[$_AAS10]["function"])?$_AAS114[$_AAS10]["function"]:"[Unknown function]"; if(isset($_AAS114[$_AAS10]["args"])){ $_AAS117 .= "("; for($_AAS118=0;$_AAS118<count($_AAS114[$_AAS10]["args"]);$_AAS118++){ $_AAS119 = gettype($_AAS114[$_AAS10]["args"][$_AAS118]); $_AAS117 .= "[$_AAS119] "; switch($_AAS119){ case "array": $_AAS117 .= "count=".count($_AAS114[$_AAS10]["args"][$_AAS118]); break; case "object": $_AAS117 .= "class=".get_class($_AAS114[$_AAS10]["args"][$_AAS118]); break; case "string": $_AAS117 .= "\"".str_replace(Array("\r\n", "\n", "\r"), Array("\\n","\\n","\\n"), $_AAS114[$_AAS10]["args"][$_AAS118])."\""; break; case "boolean": $_AAS117 .= $_AAS114[$_AAS10]["args"][$_AAS118]?"true":"false"; break; default: $_AAS117 .= $_AAS114[$_AAS10]["args"][$_AAS118]; } if($_AAS118 < count($_AAS114[$_AAS10]["args"])-1)$_AAS117 .= ", "; } $_AAS117 .= ")"; } else { $_AAS117 .= "()"; } $_AAS92 .= "  $_AAS89. $_AAS116$_AAS117"; $_AAS89++; if($_AAS10 < $_AAS115) $_AAS92 .= "\r\n"; } if($_AAS92 != "")return "Stack trace:\r\n".$_AAS92; else return ""; } function _AAS120(){ $_AAS94 = ""; foreach($_GET as $_AAS100 => $_AAS121){ $_AAS94 .= rawurlencode($_AAS100) . "=" . rawurlencode($_AAS121) . "&"; } if($_AAS94 != "")$_AAS94 = substr($_AAS94, 0, -1); return $_AAS94; }  class _AAS67 { private $_AAS122; private $_AAS123; private $_AAS124; public $_AAS70; private $_AAS125; private $_AAS126; private $_AAS127 = false; private $_AAS128; private $_AAS129; private function _AAS130($_AAS99){  _AAS40($_AAS99); }  public function _AAS67($_AAS122, $_AAS129 = "?>", $_AAS125 = true, $_AAS126 = false){ $this->_AAS122 = $_AAS122; $this->_AAS131 = dirname($_AAS122); $this->_AAS70 = ""; $this->_AAS129 = $_AAS129; $this->_AAS125 = $_AAS125; $this->_AAS126 = $_AAS126; } private function _AAS132($_AAS133){ if(is_string($_AAS133)){ return $_AAS133; } else { switch($_AAS133[0]){ case T_FILE: return '"'.$this->_AAS122.'"'; break; case T_DIR: return '"'.$this->_AAS131.'"'; break; default: return $_AAS133[1]; } } } private function _AAS134(&$_AAS135){ $_AAS92 = "\""; $_AAS135++; while($_AAS135<$this->_AAS124){ $_AAS133 = $this->_AAS123[$_AAS135]; if(is_string($_AAS133)){ $_AAS92 .= $_AAS133; if($_AAS133 == "\"") break; } else { $_AAS92 .= $_AAS133[1]; } $_AAS135++; } return $_AAS92; } private function _AAS136(&$_AAS135){ $_AAS92 = ""; $_AAS57 = ""; $_AAS55 = ""; $_AAS56 = ""; $_AAS133 = $this->_AAS123[$_AAS135]; $_AAS72 = isset($_AAS133[2])?$_AAS133[2]:0; switch($_AAS133[0]){ case T_INCLUDE: $_AAS57 = "include"; $_AAS55 = "false"; $_AAS56 = "false"; break; case T_INCLUDE_ONCE: $_AAS57 = "include_once"; $_AAS55 = "false"; $_AAS56 = "true"; break; case T_REQUIRE: $_AAS57 = "require"; $_AAS55 = "true"; $_AAS56 = "false"; break; case T_REQUIRE_ONCE: $_AAS57 = "require_once"; $_AAS55 = "true"; $_AAS56 = "true"; break; } $_AAS137 = ""; $_AAS45 = 0; $_AAS138 = false; $_AAS135++; while($_AAS135<$this->_AAS124){ $_AAS133 = $this->_AAS123[$_AAS135]; if(is_string($_AAS133)){ if($_AAS133 === "("){$_AAS45++;} elseif($_AAS133 === ")"){$_AAS45--;} elseif($_AAS133 === "?"){$_AAS138 = true;} elseif($_AAS133 === ":"){ if($_AAS138 === false) { $_AAS135--; break; } else { $_AAS138 = false; } } if($_AAS45 < 0 || $_AAS133 === ";" ){ $_AAS135--; break; } } elseif(is_array($_AAS133)) { if($_AAS133[0] === T_CLOSE_TAG){ $_AAS135--; break; } } $_AAS137 .= $this->_AAS139($_AAS135); $_AAS135++; } $_AAS92 = "eval(_AAS52(\"$this->_AAS122\",$_AAS72,$_AAS55,$_AAS56,\"$_AAS57\",$_AAS137))"; return $_AAS92; } private function _AAS140(&$_AAS135){ while($_AAS135<$this->_AAS124){ $_AAS133 = $this->_AAS123[$_AAS135]; if(is_array($_AAS133) && ( $_AAS133[0] === T_COMMENT || $_AAS133[0] === T_ML_COMMENT || $_AAS133[0] === T_DOC_COMMENT || $_AAS133[0] === T_WHITESPACE )) $_AAS135++; else break; } } private function _AAS141(&$_AAS135, $_AAS142, $_AAS143, &$_AAS144, &$_AAS89){ $_AAS89 = ""; $_AAS144 = true; $_AAS145 = $_AAS135; $_AAS45 = 1; while($_AAS145<$this->_AAS124) { $_AAS133 = $this->_AAS123[$_AAS145]; if($_AAS133 === $_AAS142)$_AAS45++; if($_AAS133 === $_AAS143)$_AAS45--; if($_AAS45<=0)break; if($_AAS133 === ";"){ return false; } else { if(is_array($_AAS133) && $_AAS133[0] !== T_COMMENT && $_AAS133[0] !== T_ML_COMMENT && $_AAS133[0] !== T_DOC_COMMENT && $_AAS133[0] !== T_WHITESPACE && $_AAS133[0] !== T_CONSTANT_ENCAPSED_STRING ) $_AAS144 = false; $_AAS89 .= $this->_AAS139($_AAS145); } $_AAS145++; } $_AAS135 = $_AAS145; return true; } private function _AAS146(&$_AAS135, &$_AAS147){ $_AAS147 = ""; $_AAS135++; $_AAS45 = 1; while($_AAS135<$this->_AAS124){ $_AAS133 = $this->_AAS123[$_AAS135]; if($_AAS133 === "(")$_AAS45++; elseif($_AAS133 === ")")$_AAS45--; if($_AAS45 <= 0)break; $_AAS147 .= $this->_AAS139($_AAS135); $_AAS135++; } return true; } private function _AAS148(&$_AAS135){ $_AAS133 = $this->_AAS123[$_AAS135]; $_AAS57 = $_AAS133[1]; $_AAS92 = ""; if(array_key_exists($_AAS57, $_ENV["_AAS14"])){ $_AAS72 = isset($_AAS133[2])?$_AAS133[2]:0; $_AAS145 = $_AAS135+1; $this->_AAS140($_AAS145); $_AAS133 = $this->_AAS123[$_AAS145]; if($_AAS133 === "("){ $_AAS74 = ""; $_AAS149 = $this->_AAS146($_AAS145, $_AAS74); if($_AAS149){ $_AAS135 = $_AAS145; $_AAS92 = "_AAS83(\"$this->_AAS122\",$_AAS72,\"$_AAS57\",Array($_AAS74))"; } else { $_AAS92 = $_AAS57; } } else { $_AAS92 = $_AAS57; } } else { $_AAS92 = $_AAS57; } return $_AAS92; } private function _AAS150(&$_AAS135, $_AAS151){ $_AAS135++; $_AAS92 = "$_AAS151"; while($_AAS135<$this->_AAS124){ $_AAS133 = $this->_AAS123[$_AAS135]; $_AAS92 .= $this->_AAS132($_AAS133); if(!is_array($_AAS133)){ break; } elseif($_AAS133[0] !== T_WHITESPACE) { break; } $_AAS135++; } return $_AAS92; } private function _AAS152($_AAS153){ $_AAS92 = ""; for($_AAS10=0;$_AAS10<strlen($_AAS153);$_AAS10++){ switch($_AAS153[$_AAS10]){ case "\\": $_AAS92 .= "\\\\"; break; case "$": $_AAS92 .= "\\\$"; break; default: $_AAS92 .= $_AAS153[$_AAS10]; } } return $_AAS92; } private function _AAS154(&$_AAS135){ $_AAS133 = $this->_AAS123[$_AAS135]; $_AAS92 = $_AAS133[1]; $_AAS72 = isset($_AAS133[2])?$_AAS133[2]:0; $_AAS155 = false; $_AAS145 = $_AAS135+1; $this->_AAS140($_AAS145); while($_AAS145<$this->_AAS124){ $_AAS133 = $this->_AAS123[$_AAS145]; if($_AAS133 === "{" || $_AAS133 === "["){ $_AAS145++; $_AAS144 = false; $_AAS89 = ""; $_AAS143 = ($_AAS133==="{")?"}":"]"; $_AAS156 = $this->_AAS141($_AAS145, $_AAS133, $_AAS143, $_AAS144, $_AAS89); if(array_key_exists($_AAS92, $_ENV["_AAS13"])){ $_AAS82 = $_ENV["_AAS13"][$_AAS92]; } else { $_AAS82 = false; } if($_AAS156 && isset($_AAS82) && $_AAS82 !== false){ if($_AAS144)_AAS48("Var_Reference", Array($_AAS82[0], trim($_AAS89, "\"'")), $this->_AAS122, $_AAS72); $_AAS92 .= "[_AAS87(\"$this->_AAS122\", $_AAS72, \"".$this->_AAS152($_AAS92)."\", $_AAS89)]"; $_AAS135 = $_AAS145; } elseif($_AAS156) { $_AAS92 .= "[$_AAS89]"; $_AAS135 = $_AAS145; } else { $_AAS135 = $_AAS145-1; break; } } elseif($_AAS133 === "("){ $_AAS147 = ""; $_AAS157 = $this->_AAS146($_AAS145, $_AAS147); if($_AAS157){ $_AAS92 = "_AAS83(\"$this->_AAS122\",$_AAS72,$_AAS92,Array($_AAS147))"; $_AAS135 = $_AAS145; break; } else { $_AAS92 .= "("; $_AAS135 = $_AAS145; break; } break; } elseif(is_array($_AAS133) && $_AAS133[0] === T_OBJECT_OPERATOR){ $_AAS145++; $this->_AAS140($_AAS145); $_AAS133 = $this->_AAS123[$_AAS145]; if(is_array($_AAS133) && $_AAS133[0] === T_STRING){ $_AAS57 = $_AAS133[1]; $this->_AAS140($_AAS145); $_AAS145++; $_AAS133 = $this->_AAS123[$_AAS145]; if($_AAS133 === "("){ $_AAS147 = ""; $_AAS149 = $this->_AAS146($_AAS145, $_AAS147); if($_AAS149){ if(array_key_exists($_AAS57, $_ENV["_AAS24"])){ $_AAS92 = "_AAS24(\"$this->_AAS122\",$_AAS72,$_AAS92,\"$_AAS57\",Array($_AAS147))"; } else { $_AAS92 .= "->$_AAS57($_AAS147)"; } $_AAS135 = $_AAS145; } else { $_AAS92 .= "->$_AAS57"; } } else { break; } } else { break; } } else { break; } $_AAS145++; } return $_AAS92; } private function _AAS158(&$_AAS135){ $_AAS133 = $this->_AAS123[$_AAS135]; $_AAS92 = "eval"; $_AAS72 = isset($_AAS133[2])?$_AAS133[2]:0; $_AAS145 = $_AAS135+1; $this->_AAS140($_AAS145); $_AAS133 = $this->_AAS123[$_AAS145]; if($_AAS133 === "("){ $_AAS74 = ""; $_AAS149 = $this->_AAS146($_AAS145, $_AAS74); if($_AAS149){ $_AAS135 = $_AAS145; $_AAS92 .= "(_AAS71(\"$this->_AAS122\", $_AAS72, Array($_AAS74)))"; } } return $_AAS92; } private function _AAS159($_AAS135){ $_AAS92 = "_AAS46"; $_AAS135++; $this->_AAS140($_AAS135); if($this->_AAS123[$_AAS135] !== "("){ $_AAS92 .= "()"; } $_AAS135--; return $_AAS92; } private function _AAS139(&$_AAS135){ $_AAS92 = ""; $_AAS133 = $this->_AAS123[$_AAS135]; if(is_string($_AAS133)){ switch($_AAS133){ case "\"": $_AAS92 = $this->_AAS134($_AAS135); break; default: $_AAS92 .= $this->_AAS132($_AAS133); } } else { switch($_AAS133[0]){ case T_INCLUDE: case T_INCLUDE_ONCE: case T_REQUIRE: case T_REQUIRE_ONCE: $_AAS92 .= $this->_AAS136($_AAS135); break; case T_VARIABLE: $_AAS92 .= $this->_AAS154($_AAS135); break; case T_FUNCTION: $_AAS92 .= $this->_AAS150($_AAS135, "function"); break; case T_NEW: $_AAS92 .= $this->_AAS150($_AAS135, "new"); break; case T_CLASS: $_AAS92 .= $this->_AAS150($_AAS135, "class"); break; case T_DOUBLE_COLON: $_AAS92 .= $this->_AAS150($_AAS135, "::"); break; case T_OBJECT_OPERATOR: $_AAS92 .= $this->_AAS150($_AAS135, "->"); break; case T_EXTENDS: $_AAS92 .= $this->_AAS150($_AAS135, "extends"); break; case T_STRING: $_AAS92 .= $this->_AAS148($_AAS135); break; case T_WHITESPACE: $_AAS92 .= " "; break; case T_EVAL: $_AAS92 .= $this->_AAS158($_AAS135); break; case T_EXIT: $_AAS92 .= $this->_AAS159($_AAS135); break; case T_HALT_COMPILER: $_AAS135 = $this->_AAS124; break; case T_START_HEREDOC: case T_END_HEREDOC: $_AAS92 .= $_AAS133[1]."\n"; break; default: if($_AAS133[0] === T_OPEN_TAG){ $this->_AAS126 = true; } elseif($_AAS133[0] === T_CLOSE_TAG){ $this->_AAS126 = false; } $_AAS92 .= $this->_AAS132($_AAS133); } } return $_AAS92; } public function _AAS68($_AAS160){ if($_ENV["_AAS32"]){ $this->_AAS128 = $_ENV['_AAS29']."_AAS161".md5($_AAS160 . $this->_AAS122); if(file_exists($this->_AAS128)){ $this->_AAS127 = true; } } else { $this->_AAS128 = ""; } if($this->_AAS127){ $this->_AAS130("Loading from cache."); if($_AAS1 = @fopen($this->_AAS128, "rb")){ $_AAS61 = fread($_AAS1, 1); $this->_AAS126 = ($_AAS61==="1"); $_AAS162 = filesize($this->_AAS128)-1; if($_AAS162>0)$this->_AAS70 = @fread($_AAS1, $_AAS162); else $this->_AAS70 = ""; } else { $this->_AAS130("Unable to load from cache file."); $this->_AAS127 = false; } } if(!$this->_AAS127){ $this->_AAS130("Processing file \"$this->_AAS122\" ..."); $this->_AAS123 = token_get_all($_AAS160); $this->_AAS124 = count($this->_AAS123);   $_AAS10 = 0; while($_AAS10<$this->_AAS124){ $this->_AAS70 .= $this->_AAS139($_AAS10); $_AAS10++; } if($this->_AAS128 !== ""){ $this->_AAS130("Saving cache for \"$this->_AAS122\""); if($_AAS1 = @fopen($this->_AAS128, "w+")){ @fprintf($_AAS1, "%s%s", $this->_AAS126?"1":"0", $this->_AAS70); @fclose($_AAS1); } else { $this->_AAS130("Unable to create cache file."); } } } $this->_AAS70 = $this->_AAS129.$this->_AAS70; if($this->_AAS125){ if($this->_AAS126)$this->_AAS70 .= "return true;"; else $this->_AAS70 .= "<?PHP return true;?>"; }  } }  if($_ENV["_AAS0"]){ set_error_handler("_AAS35"); _AAS40("Called with ".$_SERVER["REQUEST_METHOD"]." method for URI: ".$_SERVER["REQUEST_URI"]); _AAS48("PONG", "", "", 0); if(isset($_SERVER["HTTP_ACUNETIX_ASPECT_QUERIES"])){ $_AAS163 = explode(";", $_SERVER["HTTP_ACUNETIX_ASPECT_QUERIES"]); for($_AAS10=0;$_AAS10<count($_AAS163);$_AAS10++){ switch(strtolower($_AAS163[$_AAS10])){ case "filelist": if(strcasecmp(basename($_ENV['_AAS27']), basename($_SERVER["PHP_SELF"]))==0){ _AAS48("File_List", _AAS106($_ENV['_AAS26'], true), $_ENV['_AAS27'], 0); } break; case "aspectalerts": _AAS110(); break; } } } if(isset($_SERVER["REDIRECT_STATUS"]) && $_SERVER["REDIRECT_STATUS"] == 200){ _AAS48("Script_Name", $_SERVER["SCRIPT_NAME"], $_ENV['_AAS27'], 0); if(count($_GET)>0)_AAS48("Script_Query", _AAS120(), $_ENV['_AAS27'], 0); } array_push($_ENV['_AAS12'], $_ENV['_AAS27']); $_AAS164 = new _AAS67($_ENV['_AAS27']); $_AAS164->_AAS68(file_get_contents($_ENV['_AAS27'])); $_AAS165 = $_AAS164->_AAS70; unset($_AAS164); ob_start(); _AAS41(); @eval($_AAS165); _AAS46(); } __halt_compiler();082119f75623eb7abd7bf357698ff66c();16f3ba80af07625790fd61a606272dea