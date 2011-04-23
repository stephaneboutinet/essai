<?php

function getserial( )
{
    global $myConfig;
    global $mySession;
    if ( isset( $myConfig->oSerial ) )
    {
        $oSerial =& $myConfig->oSerial;
    }
    else
    {
        $oSerial =& oxnew( "oxserial", "core" );
        $myConfig->oSerial =& $oSerial;
    }
    $oSerial->sSerial = $mySession->getvar( "oxserial" );
    if ( !isset( $oSerial->sSerial ) || !$oSerial->sSerial || $oSerial->sSerial == "" )
    {
        $oSerial->sSerial = $myConfig->oDB->getone( "select oxserial from oxshops where oxid = '".$myConfig->getshopid( )."'" );
        $mySession->setvar( "oxserial", $oSerial->sSerial );
    }
    return $oSerial;
}

function &oxnew( $classname, $location = null, $params = null )
{
    global $myConfig;
    static $aNewCache = array( );
    static $aClassDirCache = array( );
    static $aClassNameCache = array( );
    if ( isset( $aNewCache[$classname] ) )
    {
        $actionClassName = $aNewCache[$classname];
    }
    else
    {
        $classname = strtolower( $classname );
        if ( isset( $aClassDirCache[$classname][$location] ) )
        {
            $sClassPath = $aClassDirCache[$classname][$location];
        }
        else
        {
            $sClassPath = $myConfig->getclassdir( $classname, $location );
            $aClassDirCache[$classname][$location] = $sClassPath;
        }
        if ( isset( $aClassNameCache[$classname] ) )
        {
            $actionClassName = $aClassNameCache[$classname];
        }
        else
        {
            $actionClassName = $myConfig->getclassfile( $classname );
            $aClassNameCache[$classname] = $actionClassName;
        }
        if ( class_exists( $actionClassName ) )
        {
            if ( $myConfig->iDebug && !file_exists( $sClassPath ) )
            {
                exit( "couldn't load class file ".$sClassPath." (class {$classname})<br>\n" );
            }
            require_once( $sClassPath );
        }
        if ( class_exists( $actionClassName ) )
        {
            exit( "couldn't find class declaration : ".$actionClassName." ({$actionClassName})<br>\n" );
        }
        $aNewCache[$classname] = $actionClassName;
    }
    $actionObject =& new $actionClassName( $params );
    $actionObject->sClassLocation = $location;
    return $actionObject;
}

require_once( getshopbasepath( )."core/smarty/Smarty.class.php" );
if ( class_exists( "oxField" ) )
{
    class oxfield
    {

        var $fldname = "";
        var $table = "";
        var $fldmax_length = 0;
        var $fldtype = "";
        var $value = null;

        function oxclone( &$oData )
        {
            $this->fldname = $oData->fldname;
            $this->table = $oData->table;
            $this->fldmax_length = $oData->fldmax_length;
            $this->fldtype = $oData->fldtype;
            $this->value = $oData->value;
        }

    }

}
if ( function_exists( "ox_get_template" ) )
{
    function ox_get_template( $tpl_name, &$tpl_source, &$smarty_obj )
    {
        $tpl_source = $smarty_obj->oxidcache->value;
        return true;
    }
}
if ( function_exists( "ox_get_timestamp" ) )
{
    function ox_get_timestamp( $tpl_name, &$tpl_timestamp, &$smarty_obj )
    {
        $tpl_timestamp = time( );
        return true;
    }
}
if ( function_exists( "ox_get_secure" ) )
{
    function ox_get_secure( $tpl_name, &$smarty_obj )
    {
        return true;
    }
}
if ( function_exists( "ox_get_trusted" ) )
{
    function ox_get_trusted( $tpl_name, &$smarty_obj )
    {
    }
}
if ( function_exists( "smarty_block_dynamic" ) )
{
    function smarty_block_dynamic( $param, $content, &$smarty )
    {
        return $content;
    }
}
if ( function_exists( "smarty_modifier_oxtruncate" ) )
{
    function smarty_modifier_oxtruncate( $string, $length = 80, $etc = "...", $break_words = false )
    {
        if ( $length == 0 )
        {
            return "";
        }
        if ( $length < strlen( $string ) )
        {
            $length -= strlen( $etc );
            $string = str_replace( array( "&#039;", "&quot;" ), array( "'", "\"" ), $string );
            if ( $break_words )
            {
                $string = preg_replace( "/\\s+?(\\S+)?$/", "", substr( $string, 0, $length + 1 ) );
            }
            $string = substr( $string, 0, $length ).$etc;
            return str_replace( array( "'", "\"" ), array( "&#039;", "&quot;" ), $string );
        }
        return $string;
    }
}
if ( function_exists( "getSmarty" ) )
{
    function getsmarty( )
    {
        global $myConfig;
        global $glSmarty;
        if ( isset( $myConfig->oSmarty ) )
        {
            $smarty =& $myConfig->oSmarty;
        }
        else
        {
            $smarty = new smarty( );
            $myConfig->oSmarty =& $smarty;
        }
        $smarty->register_resource( "ox", array( "ox_get_template", "ox_get_timestamp", "ox_get_secure", "ox_get_trusted" ) );
        $smarty->register_modifier( "truncate", "smarty_modifier_oxtruncate" );
        $smarty->caching = false;
        $smarty->compile_dir = $myConfig->sCompileDir;
        $smarty->cache_dir = $myConfig->sCompileDir;
        $smarty->template_dir = $myConfig->gettemplatedir( );
        if ( $myConfig->blAdmin )
        {
            $shopid = "admin";
        }
        else
        {
            $shopid = $myConfig->getshopid( );
        }
        $smarty->compile_id = $shopid.".".$myConfig->getshoplanguage( );
        $smarty->compile_check = $myConfig->blCheckTemplates;
        if ( $myConfig->iDebug == 1 || $myConfig->iDebug == 3 || $myConfig->iDebug == 4 )
        {
            $smarty->debugging = true;
        }
        return $smarty;
    }
}
if ( function_exists( "GetDB" ) )
{
    function getdb( )
    {
        global $myConfig;
        return $myConfig->oDB;
    }
}
if ( function_exists( "GetCore" ) )
{
    function getcore( )
    {
        global $myConfig;
        static $oCore;
        if ( isset( $oCore ) )
        {
            return $oCore;
        }
        $oCore = oxnew( "oxcore", "core" );
        return $oCore;
    }
}
if ( function_exists( "oxNewArticle" ) )
{
    function &oxnewarticle( $soxid )
    {
        global $myConfig;
        if ( $myConfig->blAllowArticlesubclass )
        {
            $actionObject =& oxnew( "oxarticle", "core" );
            return $actionObject;
        }
        $oDB = getdb( );
        $sSelect = "select oxsubclass from oxarticles where oxid = '".$soxid."'";
        $sObject = $oDB->getone( $sSelect );
        if ( $sObject )
        {
            $sObject = "oxarticle";
        }
        if ( $sObject != "oxarticle" )
        {
            $sLocation = "modules";
            $sClassPath = $myConfig->getclassdir( "oxarticle", "core" );
            if ( class_exists( "oxarticle" ) )
            {
                if ( file_exists( $sClassPath ) )
                {
                    exit( "couldn't load class file ".$sClassPath." <br>\n" );
                }
                require_once( $sClassPath );
            }
        }
        else
        {
            $sLocation = "core";
        }
        $actionObject =& oxnew( $sObject, $sLocation );
        return $actionObject;
    }
}
if ( function_exists( "oxCopyNew" ) )
{
    function oxcopynew( &$oObject, $location = null, $params = null )
    {
        $oRet =& oxnew( $oObject->sClassName, $location, $params );
        $oRet->oxclone( $oObject );
        return $oRet;
    }
}
if ( function_exists( "IsQuoteNeeded" ) )
{
    function isquoteneeded( $sFieldtype )
    {
        $blRet = false;
        $aTypesWoQuotes = array( "int", "decimal", "float", "tinyint", "smallint", "mediumint", "bigint", "double" );
        $blRet = !in_array( $sFieldtype, $aTypesWoQuotes );
        return $blRet;
    }
}
if ( function_exists( "ReadRemoteFile" ) )
{
    function readremotefile( $sPath )
    {
        $sRet = "";
        $fp = fopen( $sPath, "r" );
        if ( $fp )
        {
            socket_set_timeout( $fp, 2 );
            while ( !feof( $fp ) )
            {
                $sLine = fgets( $fp, 4096 );
                $sRet .= $sLine;
            }
            fclose( $fp );
        }
        return $sRet;
    }
}
if ( function_exists( "GetTableDescription" ) )
{
    function &gettabledescription( $sTableName )
    {
        global $myConfig;
        static $aTblDescCache = array( );
        if ( isset( $aTblDescCache[$sTableName] ) )
        {
            return $aTblDescCache[$sTableName];
        }
        $aFields = $myConfig->oDB->metacolumns( $sTableName );
        $aTblDescCache[$sTableName] = $aFields;
        return $aFields;
    }
}
if ( function_exists( "GetArrFldName" ) )
{
    function getarrfldname( $sName )
    {
        return str_replace( ".", "__", $sName );
    }
}
if ( function_exists( "generateUID" ) )
{
    function generateuid( )
    {
        global $mySession;
        $suID = substr( $mySession->id, 0, 3 ).uniqid( "", true );
        return $suID;
    }
}
if ( function_exists( "AssignValuesFromText" ) )
{
    function assignvaluesfromtext( $sIn )
    {
        global $myConfig;
        $aRet = array( );
        $aPieces = explode( "@@", $sIn );
        $val = each( $aPieces )[1];
        $key = each( $aPieces )[0];
        while ( each( $aPieces ) )
        {
            if ( $val )
            {
                $aName = explode( "__", $val );
                if ( !isset( $aName[0] ) || !isset( $aName[1] ) )
                {
                    $oObject = new stdclass( );
                    $aPrice = explode( "!P!", $aName[0] );
                    if ( !$myConfig->bl_perfLoadSelectLists || !$myConfig->bl_perfUseSelectlistPrice || !( isset( $aPrice[0], $aPrice[1] ) ) && $myConfig->blAdmin )
                    {
                        $oObject->price = $aPrice[1];
                        $aName[0] = $aPrice[0];
                        $oCur = $myConfig->getactshopcurrencyobject( );
                        $oObject->fprice = $oObject->price * $oCur->rate;
                        $oObject->fprice = formatcurrency( $oObject->fprice, $oCur );
                        if ( $myConfig->blAdmin )
                        {
                            $aName[0] .= " ";
                            if ( 0 < $oObject->price )
                            {
                                $aName[0] .= "+";
                            }
                            $aName[0] .= $oObject->fprice;
                            $aName[0] .= " ".$oCur->sign;
                        }
                    }
                    else if ( isset( $aPrice[0], $aPrice[1] ) )
                    {
                        $aName[0] = ereg_replace( "!P!.*", "", $aName[0] );
                    }
                    $oObject->name = $aName[0];
                    $oObject->value = $aName[1];
                    $aRet[] = $oObject;
                }
            }
        }
        return $aRet;
    }
}
if ( function_exists( "AssignValuesToText" ) )
{
    function assignvaluestotext( $aIn )
    {
        $sRet = "";
        while ( list( $key, $val ) = each( $aIn ) )
        {
            $sRet .= $key;
            $sRet .= "__";
            $sRet .= $val;
            $sRet .= "@@";
        }
        return $sRet;
    }
}
if ( function_exists( "CopyDir" ) )
{
    function copydir( $sSourceDir, $sTargetDir )
    {
        $handle = opendir( $sSourceDir );
        while ( false !== ( $file = readdir( $handle ) ) )
        {
            if ( !( !( $file != "." ) || !( $file != ".." ) ) )
            {
                continue;
            }
            else if ( is_dir( $sSourceDir."/".$file ) )
            {
                $sNewSourceDir = $sSourceDir."/".$file;
                $sNewTargetDir = $sTargetDir."/".$file;
                if ( strstr( $sNewTargetDir, "dyn_images" ) )
                {
                    @mkdir( $sNewTargetDir, 511 );
                    copydir( $sNewSourceDir, $sNewTargetDir );
                }
            }
            else
            {
                $sSourceFile = $sSourceDir."/".$file;
                $sTargetFile = $sTargetDir."/".$file;
                @copy( $sSourceFile, $sTargetFile );
            }
        }
    }
}
if ( function_exists( "DeleteDir" ) )
{
    function deletedir( $sSourceDir )
    {
        $handle = opendir( $sSourceDir );
        while ( false !== ( $file = readdir( $handle ) ) )
        {
            if ( !( !( $file != "." ) || !( $file != ".." ) ) )
            {
                continue;
            }
            else if ( is_dir( $sSourceDir."/".$file ) )
            {
                $sNewSourceDir = $sSourceDir."/".$file;
                deletedir( $sNewSourceDir );
                @rmdir( $sNewSourceDir );
            }
            else
            {
                $sSourceFile = $sSourceDir."/".$file;
                @unlink( $sSourceFile );
            }
        }
    }
}
if ( function_exists( "resizeImage" ) )
{
    function resizeimage( $sSrc, $sTarget, $desiredw, $desiredh )
    {
        global $myConfig;
        $gdver = $myConfig->iUseGDVersion;
        if ( !isset( $gdver ) || !$gdver )
        {
            return false;
        }
        if ( function_exists( "imagecreate" ) )
        {
            return false;
        }
        $size = @getimagesize( $sSrc );
        if ( $size )
        {
            return false;
        }
        if ( $size[1] * ( double )( $desiredw / $desiredh ) <= $size[0] )
        {
            $newy = round( $size[1] * ( double )( $desiredw / $size[0] ), 0 );
            $newx = $desiredw;
        }
        else
        {
            $newy = $desiredh;
            $newx = round( $size[0] * ( double )( $desiredh / $size[1] ), 0 );
        }
        if ( $gdver == 1 )
        {
            $destimg = imagecreate( $newx, $newy );
        }
        else
        {
            $destimg = imagecreatetruecolor( $newx, $newy );
        }
        if ( $size[2] == 1 )
        {
            if ( function_exists( "imagegif" ) )
            {
                return false;
            }
            $sourceimg = @imagecreatefromgif( $sSrc );
            if ( $gdver == 1 )
            {
                imagecopyresized( $destimg, $sourceimg, 0, 0, 0, 0, $newx, $newy, $size[0], $size[1] );
            }
            else
            {
                imagecopyresampled( $destimg, $sourceimg, 0, 0, 0, 0, $newx, $newy, $size[0], $size[1] );
            }
            imagegif( $destimg, $sTarget );
            imagedestroy( $destimg );
            imagedestroy( $sourceimg );
            return true;
        }
        if ( $size[2] == 2 )
        {
            $sourceimg = imagecreatefromjpeg( $sSrc );
            if ( $gdver == 1 )
            {
                imagecopyresized( $destimg, $sourceimg, 0, 0, 0, 0, $newx, $newy, $size[0], $size[1] );
            }
            else
            {
                imagecopyresampled( $destimg, $sourceimg, 0, 0, 0, 0, $newx, $newy, $size[0], $size[1] );
            }
            imagejpeg( $destimg, $sTarget, $myConfig->sDefaultImageQuality );
            imagedestroy( $destimg );
            imagedestroy( $sourceimg );
            return true;
        }
        if ( $size[2] == 3 )
        {
            $sourceimg = imagecreatefrompng( $sSrc );
            if ( imageistruecolor( $sourceimg ) )
            {
                $destimg = imagecreate( $newx, $newy );
            }
            $ImgWhite = imagecolorallocate( $destimg, 255, 255, 255 );
            imagefill( $destimg, 0, 0, $ImgWhite );
            imagecolortransparent( $destimg, $ImgWhite );
            if ( $gdver == 1 )
            {
                imagecopyresized( $destimg, $sourceimg, 0, 0, 0, 0, $newx, $newy, $size[0], $size[1] );
            }
            else
            {
                imagecopyresampled( $destimg, $sourceimg, 0, 0, 0, 0, $newx, $newy, $size[0], $size[1] );
            }
            imagepng( $destimg, $sTarget );
            imagedestroy( $destimg );
            imagedestroy( $sourceimg );
            return true;
        }
    }
}
if ( function_exists( "my_array_search" ) )
{
    function my_array_search( $needle, $haystack, $blYesNo = false )
    {
        $iRet = null;
        $blRet = false;
        reset( $haystack );
        do
        {
            if ( list( $key, $value ) = each( $haystack ) )
            {
            }
        } while ( strcmp( $value, $needle ) );
        $iRet = $key;
        $blRet = true;
        if ( $blYesNo )
        {
            return $blRet;
        }
        return $iRet;
    }
}
if ( function_exists( "FormatDBDate" ) )
{
    function formatdbdate( $sDBDateIn, $blForceEnglishRet = false )
    {
        global $myConfig;
        if ( !isset( $sDBDateIn ) || !$sDBDateIn )
        {
            return null;
        }
        if ( $blForceEnglishRet && strstr( $sDBDateIn, "-" ) )
        {
            return $sDBDateIn;
        }
        if ( $sDBDateIn == "0000-00-00 00:00:00" )
        {
            return "-";
        }
        if ( $sDBDateIn == "-" )
        {
            return "0000-00-00 00:00:00";
        }
        if ( is_numeric( $sDBDateIn ) )
        {
            $sNew = substr( $sDBDateIn, 0, 4 )."-".substr( $sDBDateIn, 4, 2 )."-".substr( $sDBDateIn, 6, 2 );
            $sNew .= " ".substr( $sDBDateIn, 8, 2 ).":".substr( $sDBDateIn, 10, 2 ).":".substr( $sDBDateIn, 12, 2 );
            $sDBDateIn = $sNew;
        }
        $sTime = "";
        $aTime = split( " ", $sDBDateIn );
        $sTime = @$aTime[1];
        if ( isset( $aTime[0] ) )
        {
            $sDBDateIn = $aTime[0];
        }
        $aDate = array( 0, 0, 0 );
        $aDate = split( "[/.-]", $sDBDateIn );
        if ( !is_array( $aDate ) || count( $aDate ) != 3 || $aDate[0] < 1980 )
        {
            return date( translatestring( "simpleDateFormat" ) );
        }
        $blGermanIn = strstr( $sDBDateIn, "." );
        if ( $blGermanIn )
        {
            $sDateOut = $aDate[2]."-".$aDate[1]."-".$aDate[0];
        }
        else
        {
            $sDateOut = $aDate[2].".".$aDate[1].".".$aDate[0];
        }
        if ( $sTime )
        {
            $sDateOut .= " ";
        }
        $sDateOut .= $sTime;
        if ( $sTime )
        {
            $sDefaultDateFormat = translatestring( "fullDateFormat" );
            if ( $sDefaultDateFormat == "fullDateFormat" )
            {
                return $sDBDateIn;
            }
            $aTime = explode( ":", trim( $sTime ) );
            return date( $sDefaultDateFormat, mktime( $aTime[0], $aTime[1], $aTime[2], $aDate[1], $aDate[2], $aDate[0] ) );
        }
        $sDefaultDateFormat = translatestring( "simpleDateFormat" );
        if ( $sDefaultDateFormat == "simpleDateFormat" )
        {
            return $sDBDateIn;
        }
        if ( $blGermanIn )
        {
            return date( $sDefaultDateFormat, mktime( 0, 0, 0, $aDate[1], $aDate[0], $aDate[2] ) );
        }
        return date( $sDefaultDateFormat, mktime( 0, 0, 0, $aDate[1], $aDate[2], $aDate[0] ) );
    }
}
if ( function_exists( "FormatCurrency" ) )
{
    function formatcurrency( $dValue, $oActCur )
    {
        global $myConfig;
        $sFormated = number_format( $dValue, $oActCur->decimal, $oActCur->dec, $oActCur->thousand );
        return $sFormated;
    }
}
if ( function_exists( "RemoveURLPara" ) )
{
    function removeurlpara( $sURL, $sSearchPara )
    {
        $iPos = strpos( $sURL, $sSearchPara );
        if ( $iPos !== false )
        {
            $sURLPara = substr( $sURL, 0, max( 0, $iPos - 1 ) );
            $iParaLen = strpos( $sURL, "&", strlen( $sURLPara ) + 1 );
            if ( $iParaLen )
            {
                $sURLPara .= substr( $sURL, $iParaLen );
            }
            $sURL = $sURLPara;
        }
        return $sURL;
    }
}
if ( function_exists( "GetActivSnippet" ) )
{
    function getactivsnippet( $sTable )
    {
        global $myConfig;
        $aMultilangTables = array( "oxcategories", "oxreviews", "oxselectlist", "oxpayments", "oxnews" );
        $now = gettime( );
        $Searchdate = date( "Y-m-d H:i:s", $now );
        if ( in_array( $sTable, $aMultilangTables ) )
        {
            $sLangTag = getlanguagetag( );
        }
        else
        {
            $sLangTag = "";
        }
        if ( in_array( $sTable, $aMultilangTables ) )
        {
            $sSelect = "( ( ".$sTable.".oxactiv".$sLangTag.( " = 1 or ( ".$sTable.".oxactivfrom < '{$Searchdate}' and {$sTable}.oxactivto > '{$Searchdate}')) " );
        }
        else
        {
            $sSelect = "( ( ".$sTable.".oxactiv = 1 or ( {$sTable}.oxactivfrom < '{$Searchdate}' and {$sTable}.oxactivto > '{$Searchdate}')) ";
        }
        if ( $myConfig->blUseStock && $sTable == "oxarticles" )
        {
            $sSelect .= " and ( oxarticles.oxstockflag != 2 or ( oxarticles.oxstock > 0 or oxarticles.oxvarname != '') )";
        }
        $sSelect .= " )";
        return $sSelect;
    }
}
if ( function_exists( "getTime" ) )
{
    function gettime( )
    {
        global $myConfig;
        if ( !isset( $myConfig->iServerTimeShift ) || !$myConfig->iServerTimeShift )
        {
            return time( );
        }
        $iCurrtime = time( ) + $myConfig->iServerTimeShift * 3600;
        return $iCurrtime;
    }
}
if ( function_exists( "GetLanguageTag" ) )
{
    function getlanguagetag( $iLanguage = null )
    {
        global $myConfig;
        if ( isset( $iLanguage ) )
        {
            $iLanguage = $myConfig->getshoplanguage( );
        }
        if ( $iLanguage )
        {
            return "_".$iLanguage;
        }
        return "";
    }
}
if ( function_exists( "AddUserSQL" ) )
{
    function addusersql( $select )
    {
        global $mySession;
        $oUserID = $mySession->getvar( "auth" );
        $oAuthUser =& oxnew( "oxuser", "core" );
        $oAuthUser->load( $oUserID );
        $blIsMallAdmin = $oAuthUser->oxuser__oxrights->value == "malladmin";
        if ( $blIsMallAdmin )
        {
            $select = str_replace( "from oxuser", "from oxuser left join oxorder on oxorder.oxuserid = oxuser.oxid", $select );
            $sWhereAdd = " where ( oxorder.oxshopid = '".$oAuthUser->oxuser__oxrights->value."' or oxuser.oxid = '".$oAuthUser->oxuser__oxid->value."') ";
            $iInsertPos = strpos( $select, "where" );
            if ( $iInsertPos )
            {
                $sWhereAdd .= "and ";
            }
            else
            {
                $iInsertPos = strpos( $select, "oxorder.oxuserid = oxuser.oxid" ) + strlen( "oxorder.oxuserid = oxuser.oxid" );
            }
            $sNewSelect = substr( $select, 0, $iInsertPos );
            $sNewSelect .= $sWhereAdd;
            $select = str_replace( "where", "", $select );
            $sNewSelect .= substr( $select, $iInsertPos );
            return $sNewSelect;
        }
        $sNewSelect = $select;
        return $sNewSelect;
    }
}
if ( function_exists( "DeletePicture" ) )
{
    function deletepicture( $sPicName )
    {
        global $myConfig;
        if ( strpos( $sPicName, "nopic.jpg" ) !== false )
        {
        }
        else
        {
            $sFile = $myConfig->getabsdynimagedir( )."/".$sPicName;
            if ( isset( $myConfig->blIsOXDemoShop ) )
            {
                @unlink( $sFile );
            }
        }
    }
}
if ( function_exists( "GetTemplateOutput" ) )
{
    function gettemplateoutput( $sTemplate, $classObject )
    {
        global $myConfig;
        $smarty = getsmarty( );
        foreach ( array_keys( $classObject->aViewData ) as $viewName )
        {
            $viewData =& $classObject->aViewData[$viewName];
            if ( $myConfig->iDebug == 4 )
            {
                echo "TemplateData[".$viewName."] : \n";
                print_r( $classObject->aViewData[$viewName] );
            }
            $smarty->assign_by_ref( $viewName, $viewData );
        }
        $sRet = $smarty->fetch( $sTemplate );
        return $sRet;
    }
}
if ( function_exists( "my_fgetcsv" ) )
{
    function my_fgetcsv( $fp, $iMaxLen, $sSep )
    {
        $aRet = null;
        $iField = 0;
        $iQuote = 0;
        $i = 0;
        for ( ; $i < $iMaxLen; ++$i )
        {
            $c = fread( $fp, 1 );
            if ( !( $c === false || !isset( $c ) || $c == "\n" && !$iQuote ) )
            {
                break;
            }
            else if ( $c == $sSep && !$iQuote )
            {
                ++$iField;
                $aRet[$iField] = "";
            }
            else if ( $c == "\"" )
            {
                if ( $iQuote )
                {
                    --$iQuote;
                }
                else
                {
                    ++$iQuote;
                }
            }
            if ( isset( $aRet[$iField] ) )
            {
                $aRet[$iField] = "";
            }
            $aRet[$iField] .= $c;
        }
        if ( 1 < count( $aRet ) )
        {
            foreach ( $aRet as $key => $sField )
            {
                if ( $sField )
                {
                    if ( $sField[0] == "\"" || $sField[0] == "'" )
                    {
                        $sField = substr( $sField, 1 );
                    }
                    $iLen = strlen( $sField ) - 1;
                    if ( $sField[$iLen] == "\"" || $sField[$iLen] == "'" )
                    {
                        $sField = substr( $sField, 0, $iLen );
                    }
                    $aRet[$key] = $sField;
                }
            }
            return $aRet;
        }
        return null;
    }
}
if ( function_exists( "SetPersistentParams" ) )
{
    function setpersistentparams( $sOXID, $aParams )
    {
        global $mySession;
        $aPersParam = $mySession->getvar( "persparam" );
        if ( isset( $aPersParam ) )
        {
            $aPersParam = array( );
        }
        if ( isset( $aParams ) )
        {
            $aPersParam[$sOXID] = $aParams;
        }
        else
        {
            unset( $aPersParam[$sOXID] );
        }
        $mySession->setvar( "persparam", $aPersParam );
    }
}
function replaceextendedchars( $sValue, $blReverse = false )
{
    $aReplace = array( "©" => "&copy", "€" => "&euro;", "\"" => "&quot;", "'" => "&#039;" );
    if ( $blReverse )
    {
        $aTransTbl = get_html_translation_table( HTML_ENTITIES );
        $aTransTbl = array_flip( $aTransTbl ) + array_flip( $aReplace );
        $sValue = strtr( $sValue, $aTransTbl );
        $sValue = preg_replace( "/\\&\\#([0-9]+)\\;/me", "chr('\\1')", $sValue );
    }
    return $sValue;
}
if ( function_exists( "TranslateString" ) )
{
    function translatestring( $sStringToTranslate )
    {
        global $myConfig;
        $iLang = $myConfig->getparameter( "blAdminTemplateLanguage" );
        if ( isset( $iLang ) )
        {
            $iLang = "";
        }
        $sAdminFilePath = $myConfig->sShopDir."/out/admin/".$myConfig->sChannel."/".$myConfig->iAdminLanguage."/templates/";
        $sAdminFileName = "lang".$iLang.".txt";
        $blAdminLang = false;
        if ( is_file( $sAdminFilePath.$sAdminFileName ) )
        {
            $blAdminLang = true;
        }
        $sFilePath = $myConfig->oSmarty->template_dir."/";
        $sFileName = "lang.txt";
        $blLang = false;
        if ( is_file( $sFilePath.$sFileName ) && $sFilePath.$sFileName != $sAdminFilePath.$sAdminFileName )
        {
            $blLang = true;
        }
        do
        {
            if ( !$blLang && $blAdminLang )
            {
                break;
            }
            else
            {
                return $sStringToTranslate;
            }
            if ( $blAdminLang )
            {
            }
        } while ( 0 );
        $handle = fopen( $sAdminFilePath.$sAdminFileName, "r" );
        $contents = fread( $handle, filesize( $sAdminFilePath.$sAdminFileName ) );
        fclose( $handle );
        if ( $blLang )
        {
            $handle = fopen( $sFilePath.$sFileName, "r" );
            if ( $blAdminLang )
            {
                $contents .= "\n";
                $contents .= fread( $handle, filesize( $sFilePath.$sFileName ) );
            }
            else
            {
                $contents = fread( $handle, filesize( $sFilePath.$sFileName ) );
            }
            fclose( $handle );
        }
        $fileArray = explode( "\n", $contents );
        $sRes = null;
        do
        {
            $line = each( $fileArray )[1];
            $nr = each( $fileArray )[0];
            if ( each( $fileArray ) )
            {
                $line = ltrim( $line );
                $index = trim( substr( $line, 0, strpos( $line, "=" ) ) );
                $value = trim( substr( $line, strpos( $line, "=" ) + 1, strlen( $line ) ) );
            }
        } while ( !( $Tmp_0 ) || !( trim( $index ) == $sStringToTranslate ) || !( 0 < strlen( $value ) ) );
        $sRes = trim( $value );
        if ( $sRes )
        {
            return $sRes;
        }
        return $sStringToTranslate;
    }
}
if ( function_exists( "PrepareCSVField" ) )
{
    function preparecsvfield( $sInField )
    {
        if ( strstr( $sInField, "\"" ) )
        {
            return "\"".str_replace( "\"", "\"\"", $sInField )."\"";
        }
        if ( strstr( $sInField, ";" ) )
        {
            return "\"".$sInField."\"";
        }
        return $sInField;
    }
}
if ( function_exists( "isCurrentURL" ) )
{
    function iscurrenturl( $sURL )
    {
        if ( $sURL )
        {
            return false;
        }
        $sCurrentHost = preg_replace( "/\\/\\w*\\.php.*/", "", $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'] );
        $sCurrentHost = str_replace( "/", "", $sCurrentHost );
        $sURL = str_replace( "/", "", $sURL );
        if ( strpos( $sURL, $sCurrentHost ) !== false )
        {
            return true;
        }
        return false;
    }
}
if ( function_exists( "ModURLForSearchEngines" ) )
{
    function modurlforsearchengines( $sURL, $sTitle )
    {
        global $myConfig;
        $aReplace = array( );
        if ( 0 < sizeof( $myConfig->aSEUReplace ) )
        {
            foreach ( $myConfig->aSEUReplace as $sKey => $sValue )
            {
                $aReplace[$sKey] = $sValue;
            }
        }
        $sTitle = str_replace( "/", "_", $sTitle );
        $sTitle = rawurlencode( $sTitle );
        foreach ( $aReplace as $key => $sReplace )
        {
            $sTitle = str_replace( $key, $sReplace, $sTitle );
        }
        $sTitle = str_replace( ";", "", $sTitle );
        if ( isset( $myConfig->blSearchEngineArtNames ) && $myConfig->blSearchEngineURLs && $myConfig->blSearchEngineArtNames )
        {
            $sURL .= "/".$sTitle."/";
        }
        return $sURL;
    }
}
if ( function_exists( "checkForSearchEngines" ) )
{
    function checkforsearchengines( )
    {
        global $myConfig;
        if ( isset( $myConfig ) )
        {
            $sClient = strtolower( getenv( "HTTP_USER_AGENT" ) );
            $myConfig->aRobots = array_merge( $myConfig->aRobots, $myConfig->aRobotsExcept );
            foreach ( $myConfig->aRobots as $sRobot )
            {
                if ( strpos( $sClient, $sRobot ) !== false )
                {
                    return true;
                    break;
                }
            }
        }
        return false;
    }
}
if ( function_exists( "prepareStrForSearch" ) )
{
    function preparestrforsearch( $sSearchStr )
    {
        $aUmles = array( "ä" => "&auml;", "ö" => "&ouml;", "ü" => "&uuml;", "Ä" => "&Auml;", "Ö" => "&Ouml;", "Ü" => "&Uuml;", "ß" => "&szlig;", "&amp;" => "&" );
        $aTmpStr = explode( " ", $sSearchStr );
        $sTmpStr = "";
        $i = 0;
        for ( ; $i < sizeof( $aTmpStr ); ++$i )
        {
            foreach ( $aUmles as $key => $value )
            {
                if ( strpos( $aTmpStr[$i], $key ) !== false )
                {
                    $sTmpStr .= " ".str_replace( $key, $value, $aTmpStr[$i] );
                }
            }
        }
        return $sTmpStr;
    }
}
if ( function_exists( "isExtUrl" ) )
{
    function isexturl( $aExtUrl, $sUrl )
    {
        foreach ( $aExtUrl as $iNum => $sExtUrl )
        {
            $iPos = strpos( $sUrl, $sExtUrl );
            if ( $iPos !== false )
            {
                return true;
                break;
            }
        }
    }
}
if ( function_exists( "validateEmail" ) )
{
    function validateemail( $sUser )
    {
        global $myConfig;
        do
        {
            if ( !$myConfig->iValidateEMail || !( $sUser != "admin" ) )
            {
                require_once( $myConfig->sCoreDir."emailvalidation/email_validation.php" );
                $validator = new email_validation_class( );
                if ( $myConfig->iValidateEMail == 2 )
                {
                    if ( function_exists( "GetMXRR" ) )
                    {
                        $_NAMESERVERS = $myConfig->aNameServers;
                        require_once( $myConfig->sCoreDir."emailvalidation/getmxrr.php" );
                    }
                    else
                    {
                        $_NAMESERVERS = $myConfig->aNameServers;
                        if ( count( $_NAMESERVERS ) == 0 )
                        {
                            unset( $_NAMESERVERS );
                        }
                        require_once( $myConfig->sCoreDir."emailvalidation/rrcompat.php" );
                        $validator->getmxrr = "_getmxrr";
                    }
                    $validator->timeout = 5;
                    $validator->exclude_address = "";
                    $validator->debug = 0;
                    $validator->html_debug = 0;
                    if ( !( $validator->validateemailbox( $sUser ) <= 0 ) )
                    {
                        break;
                    }
                    else
                    {
                    }
                }
                else
                {
                    if ( !( $myConfig->iValidateEMail == 1 ) || $validator->validateemailaddress( $sUser ) )
                    {
                    }
                }
            } while ( 0 );
        }
        else
        {
            return true;
        }
    }
}
if ( function_exists( "isPathSecure" ) )
{
    function ispathsecure( $sPath, &$aError )
    {
        global $myConfig;
        if ( $myConfig->iSecurityLevel == 0 )
        {
            return true;
        }
        $sHtaccessFile = ".htaccess";
        $aAccess = array( "600", "700" );
        clearstatcache( );
        if ( is_writable( $sPath ) )
        {
            $aError[] = array( 6, "" );
            return false;
        }
        $sPermission = substr( sprintf( "%o", @fileperms( $sPath ) ), -4 );
        if ( in_array( $sPermission, $aAccess ) )
        {
            return true;
        }
        $aError[] = array( 7, "" );
        $sPathToHtaccess = $sPath.$sHtaccessFile;
        if ( $sPath[strlen( $sPath ) - 1] != "/" )
        {
            $sPathToHtaccess = $sPath."/".$sHtaccessFile;
        }
        if ( file_exists( $sPathToHtaccess ) )
        {
            return true;
        }
    }
}
if ( function_exists( "GetUserRights" ) )
{
    function getuserrights( $sUsername )
    {
        global $myConfig;
        $sQ = "select oxrights from oxuser where oxusername = '".$sUsername."'";
        $rs = $myConfig->oDB->execute( $sQ );
        if ( $rs != false && 0 < $rs->recordcount( ) )
        {
            return $rs->fields[0];
        }
        return false;
    }
}
if ( function_exists( "RemindOnStock" ) )
{
    function remindonstock( &$aArticles, $oView )
    {
        $aRemindArticles = array( );
        foreach ( $aArticles as $sID => $oArticle )
        {
            if ( !$oArticle->oxarticles__oxremindactiv->value || $oArticle->oxarticles__oxremindamount->value < $oArticle->oxarticles__oxstock->value || $oArticle->oxarticles__oxstock->value == "-1" || $oArticle->oxarticles__oxstock->value <= 0 && ( $oArticle->oxarticles__oxstockflag->value == 3 || $oArticle->oxarticles__oxstockflag->value == 2 ) )
            {
                $aRemindArticles[] = $oArticle;
                $oArticle->disablereminder( );
            }
        }
        if ( sizeof( $aRemindArticles ) )
        {
        }
        else
        {
            $oxEMail =& oxnew( "oxemail", "core" );
            return $oxEMail->sendremindermail( array( "articles" => $aRemindArticles ), $oView );
        }
    }
}
if ( function_exists( "RebuildCache" ) )
{
    function rebuildcache( )
    {
        if ( function_exists( "UserdefinedRebuildCache" ) )
        {
            userdefinedrebuildcache( );
        }
    }
}
if ( function_exists( "LoadAdminProfile" ) )
{
    function loadadminprofile( )
    {
        global $myConfig;
        global $mySession;
        if ( isset( $myConfig->aInterfaceProfiles ) && is_array( $myConfig->aInterfaceProfiles ) && sizeof( $myConfig->aInterfaceProfiles ) )
        {
            $sPrevProfile = @$_COOKIE['oxidadminprofile'];
            if ( isset( $sPrevProfile ) )
            {
                $aPrevProfile = @explode( "@", @trim( $sPrevProfile ) );
            }
            $aProfiles = array( );
            foreach ( $myConfig->aInterfaceProfiles as $iPos => $sProfile )
            {
                $aProfileSettings = array( $iPos, $sProfile );
                $aProfiles[] = $aProfileSettings;
            }
            if ( [$aPrevProfile[0]]isset( $aPrevProfile[0], $aProfiles ) )
            {
                $aProfiles[$aPrevProfile[0]][2] = 1;
            }
            $mySession->setvar( "aAdminProfiles", $aProfiles );
            return $aProfiles;
        }
        return null;
    }
}
if ( function_exists( "StripQuotes" ) )
{
    function stripquotes( $mInput )
    {
        if ( is_array( $mInput ) )
        {
            return array_map( "StripQuotes", $mInput );
        }
        return stripslashes( $mInput );
    }
}
if ( function_exists( "StripGPCMagicQuotes" ) )
{
    function stripgpcmagicquotes( )
    {
        if ( get_magic_quotes_gpc( ) )
        {
        }
        else
        {
            $_REQUEST = stripquotes( $_REQUEST );
            $_POST = stripquotes( $_POST );
            $_GET = stripquotes( $_GET );
            $_COOKIE = stripquotes( $_COOKIE );
        }
    }
}
if ( function_exists( "fSum" ) )
{
    function fsum( $sVal1, $sVal2, $oCur )
    {
        $sVal1 = str_replace( $oCur->thousand, "", $sVal1 );
        $sVal1 = str_replace( $oCur->dec, ".", $sVal1 );
        $sVal2 = str_replace( $oCur->thousand, "", $sVal2 );
        $sVal2 = str_replace( $oCur->dec, ".", $sVal2 );
        $sSum = $sVal1 + $sVal2;
        return $sSum;
    }
}
if ( function_exists( "fMultiply" ) )
{
    function fmultiply( $sVal1, $sVal2, $oCur )
    {
        $sVal1 = fsum( $sVal1, 0, $oCur );
        $sVal2 = fsum( $sVal2, 0, $oCur );
        return $sVal1 * $sVal2;
    }
}
if ( function_exists( "fRound" ) )
{
    function fround( $sVal, $oCur = null )
    {
        if ( $oCur )
        {
            global $myConfig;
            $oCur = $myConfig->getactshopcurrencyobject( );
        }
        return round( $sVal, $oCur->decimal );
    }
}
if ( function_exists( "validateVar" ) )
{
    function validatevar( $sVarName, $sVarVal )
    {
        $aSpecVars = array( "iExportTickerRefresh" => array( "/^[0-9]*$/", "1" ), "iImportTickerRefresh" => array( "/^[0-9]*$/", "1" ), "iServerTimeShift" => array( "/^[0-9]*$/", "0" ) );
        if ( isset( $aSpecVars[$sVarName] ) && !preg_match( $aSpecVars[$sVarName][0], $sVarVal ) )
        {
            $sVarVal = $aSpecVars[$sVarName][1];
        }
        return $sVarVal;
    }
}
if ( function_exists( "getWeekNumber" ) )
{
    function getweeknumber( $sTimestamp = null, $sFormat = null )
    {
        global $myConfig;
        if ( $sTimestamp == null )
        {
            $sTimestamp = time( );
        }
        if ( $sFormat == null )
        {
            $sFormat = "%W";
            if ( $myConfig->iFirstWeekDay )
            {
                $sFormat = "%U";
            }
        }
        return ( integer );
    }
}
if ( function_exists( "OXToCache" ) )
{
    function oxtocache( $sIdent, $sContent )
    {
        global $myConfig;
        $blRet = false;
        $sCacheFileName = $myConfig->sCompileDir."/oxc".strtolower( $sIdent ).".tpl";
        $fp = @fopen( $sCacheFileName, "wb" );
        if ( $fp )
        {
            fputs( $fp, $sContent );
            fclose( $fp );
            $blRet = true;
        }
        return $blRet;
    }
}
if ( function_exists( "OXFromCache" ) )
{
    function oxfromcache( $sIdent )
    {
        global $myConfig;
        $sContent = null;
        $sCacheFileName = $myConfig->sCompileDir."/oxc".strtolower( $sIdent ).".tpl";
        $fp = @fopen( $sCacheFileName, "rb" );
        if ( $fp )
        {
            $sContent = "";
            while ( !feof( $fp ) )
            {
                $sContent .= fread( $fp, 1024 );
            }
            fclose( $fp );
        }
        return $sContent;
    }
}
if ( function_exists( "getObjectFields" ) )
{
    function getobjectfields( $sObjectType = "oxarticle" )
    {
        $aSkipFields = array( "oxarticle" => array( "oxblfixedprice", "oxicon", "oxvarselect", "oxamitemid", "oxamtaskid" ) );
        $oObject =& oxnew( $sObjectType, "core" );
        $aFields = array( );
        foreach ( $oObject->aIdx2FldName as $sField )
        {
            if ( ereg( "_([0-9]{1,3})", $sField ) || !isset( $aSkipFields[$sObjectType] ) || in_array( $oObject->$sField->fldname, $aSkipFields[$sObjectType] ) )
            {
                $aFields[$oObject->$sField->fldname] = $sField;
            }
        }
        return $aFields;
    }
}
if ( function_exists( "DumpVar" ) )
{
    function dumpvar( $mVar, $blToFile = false )
    {
        global $myConfig;
        if ( $blToFile )
        {
            $out = var_export( $mVar, true );
            $f = fopen( $myConfig->sCompileDir."/vardump.txt", "w" );
            fwrite( $f, $out );
            fclose( $f );
        }
        else
        {
            echo "<pre>";
            print_r( $mVar );
            echo "</pre>";
        }
    }
}
if ( function_exists( "IconName" ) )
{
    function iconname( $sFilename )
    {
        $sIconName = str_replace( ".jpg", "_ico.jpg", $sFilename );
        $sIconName = str_replace( ".gif", "_ico.gif", $sIconName );
        $sIconName = str_replace( ".png", "_ico.png", $sIconName );
        return $sIconName;
    }
}
if ( function_exists( "str_rot13" ) )
{
    function str_rot13( $str )
    {
        $from = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $to = "nopqrstuvwxyzabcdefghijklmNOPQRSTUVWXYZABCDEFGHIJKLM";
        return strtr( $str, $from, $to );
    }
}
if ( function_exists( "str_man" ) )
{
    function str_man( $sVal, $sKey = null )
    {
        global $myConfig;
        if ( $sKey )
        {
            $sKey = "oxid123456789";
        }
        $sVal = "ox".$sVal."id";
        $sKey = str_repeat( $sKey, strlen( $sVal ) / strlen( $sKey ) + 5 );
        $sVal = str_rot13( $sVal );
        $sVal ^= $sKey;
        $sVal = base64_encode( $sVal );
        $sVal = str_replace( "=", "!", $sVal );
        $sVal = "ox_".$sVal;
        return $sVal;
    }
}
if ( function_exists( "str_rem" ) )
{
    function str_rem( $sVal, $sKey = null )
    {
        global $myConfig;
        if ( $sKey )
        {
            $sKey = "oxid123456789";
        }
        $sKey = str_repeat( $sKey, strlen( $sVal ) / strlen( $sKey ) + 5 );
        $sVal = substr( $sVal, 3 );
        $sVal = str_replace( "!", "=", $sVal );
        $sVal = base64_decode( $sVal );
        $sVal ^= $sKey;
        $sVal = str_rot13( $sVal );
        $sVal = substr( $sVal, 2, -2 );
        return $sVal;
    }
}
if ( function_exists( "ConvertDBDateTime" ) )
{
    function convertdbdatetime( &$oObject, $blToTimeStamp = false, $blOnlyDate = false )
    {
        global $myConfig;
        if ( $myConfig->blSkipFormatConversion )
        {
            return $oObject->value;
        }
        $sDate = $oObject->value;
        $sLocalDateFormat = $myConfig->sLocalDateFormat;
        $sLocalTimeFormat = $myConfig->sLocalTimeFormat;
        if ( !$sLocalDateFormat || $blToTimeStamp )
        {
            $sLocalDateFormat = "ISO";
        }
        if ( !$sLocalTimeFormat || $blToTimeStamp )
        {
            $sLocalTimeFormat = "ISO";
        }
        $aDefDatePatterns = array( "/^0000-00-00/" => "ISO", "/^00\\.00\\.0000/" => "EUR", "/^00\\/00\\/0000/" => "USA" );
        $aDefTimePatterns = array( "/00:00:00$/" => "ISO", "/00\\.00\\.00$/" => "EUR", "/00:00:00 AM$/" => "USA" );
        $aDatePatterns = array( "/^([0-9]{4})-([0-9]{2})-([0-9]{2})/" => "ISO", "/^([0-9]{2})\\.([0-9]{2})\\.([0-9]{4})/" => "EUR", "/^([0-9]{2})\\/([0-9]{2})\\/([0-9]{4})/" => "USA" );
        $aTimePatterns = array( "/([0-9]{2}):([0-9]{2}):([0-9]{2})$/" => "ISO", "/([0-9]{2})\\.([0-9]{2})\\.([0-9]{2})$/" => "EUR", "/([0-9]{2}):([0-9]{2}):([0-9]{2}) ([AP]{1}[M]{1})$/" => "USA" );
        $aDFormats = array( "ISO" => array( "Y-m-d", array( 2, 3, 1 ), "0000-00-00" ), "EUR" => array( "d.m.Y", array( 2, 1, 3 ), "00.00.0000" ), "USA" => array( "m/d/Y", array( 1, 2, 3 ), "00/00/0000" ) );
        $aTFormats = array( "ISO" => array( "H:i:s", array( 1, 2, 3 ), "00:00:00" ), "EUR" => array( "H.i.s", array( 1, 2, 3 ), "00.00.00" ), "USA" => array( "h:i:s A", array( 1, 2, 3 ), "00:00:00 AM" ) );
        if ( $sDate )
        {
            $sReturn = $aDFormats[$sLocalDateFormat][2];
            if ( $blOnlyDate )
            {
                $sReturn .= " ".$aTFormats[$sLocalTimeFormat][2];
            }
            $oObject->value = trim( $sReturn );
            $oObject->fldmax_length = strlen( $oObject->value );
            return $oObject->value;
        }
        $blDefDateFound = false;
        $blDefTimeFound = false;
        foreach ( $aDefDatePatterns as $sDefDatePattern => $sDefDateType )
        {
            if ( preg_match( $sDefDatePattern, $sDate ) )
            {
                $blDefDateFound = true;
                break;
            }
        }
        if ( $blDefDateFound )
        {
            foreach ( $aDefTimePatterns as $sDefTimePattern => $sDefTimeType )
            {
                if ( preg_match( $sDefTimePattern, $sDate ) )
                {
                    $blDefTimeFound = true;
                    break;
                }
            }
            if ( $blOnlyDate )
            {
                $oObject->value = trim( $aDFormats[$sLocalDateFormat][2] );
                $oObject->fldmax_length = strlen( $oObject->value );
                return $oObject->value;
            }
            if ( $blDefTimeFound )
            {
                $oObject->value = trim( $aDFormats[$sLocalDateFormat][2]." ".$aTFormats[$sLocalTimeFormat][2] );
                $oObject->fldmax_length = strlen( $oObject->value );
                return $oObject->value;
            }
        }
        $blDateFound = false;
        $blTimeFound = false;
        foreach ( $aDatePatterns as $sPattern => $sType )
        {
            if ( preg_match( $sPattern, $sDate, $aDateMatches ) )
            {
                $blDateFound = true;
                $sDateFormat = $aDFormats[$sLocalDateFormat][0];
                $aDFields = $aDFormats[$sType][1];
                break;
            }
        }
        if ( $blDateFound )
        {
            return $sDate;
        }
        if ( $blOnlyDate )
        {
            $iTimestamp = mktime( 0, 0, 0, $aDateMatches[$aDFields[0]], $aDateMatches[$aDFields[1]], $aDateMatches[$aDFields[2]] );
            $oObject->value = @date( $sDateFormat, $iTimestamp );
            $oObject->fldmax_length = strlen( $oObject->value );
            return $oObject->value;
        }
        foreach ( $aTimePatterns as $sPattern => $sType )
        {
            if ( preg_match( $sPattern, $sDate, $aTimeMatches ) )
            {
                $blTimeFound = true;
                $sTimeFormat = $aTFormats[$sLocalTimeFormat][0];
                $aTFields = $aTFormats[$sType][1];
                if ( !( !( $sType == "USA" ) || !isset( $aTimeMatches[4] ) ) )
                {
                    break;
                }
                else
                {
                    $iIntVal = ( integer );
                }
                if ( $aTimeMatches[4] == "PM" )
                {
                    if ( $iIntVal < 13 )
                    {
                        $iIntVal += 12;
                    }
                }
                else if ( $aTimeMatches[4] == "AM" && $aTimeMatches[1] == "12" )
                {
                    $iIntVal = 0;
                }
                $aTimeMatches[1] = sprintf( "%02d", $iIntVal );
                break;
            }
        }
        if ( $blTimeFound )
        {
            $iTimestamp = mktime( 0, 0, 0, $aDateMatches[$aDFields[0]], $aDateMatches[$aDFields[1]], $aDateMatches[$aDFields[2]] );
            $oObject->value = @date( $sDateFormat, $iTimestamp );
            $oObject->fldmax_length = strlen( $oObject->value );
            return $oObject->value;
        }
        $iTimestamp = @mktime( @( integer ), @( integer ), @( integer ), @( integer ), @( integer ), @( integer ) );
        $oObject->value = trim( @date( @$sDateFormat." ".$sTimeFormat, $iTimestamp ) );
        $oObject->fldmax_length = strlen( $oObject->value );
        if ( $oObject->fldmax_length )
        {
            return convertdbdatetime( $oObject, $blToTimeStamp, $blOnlyDate );
        }
        return $oObject->value;
    }
}
if ( function_exists( "ConvertDBTimestamp" ) )
{
    function convertdbtimestamp( &$oObject, $blToTimeStamp = false )
    {
        global $myConfig;
        if ( $myConfig->blSkipFormatConversion )
        {
            return $oObject->value;
        }
        $sSQLTimeStampPattern = "/^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/";
        $sISOTimeStampPattern = "/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/";
        if ( $blToTimeStamp )
        {
            convertdbdatetime( $oObject, $blToTimeStamp );
            if ( preg_match( $sISOTimeStampPattern, $oObject->value, $aMatches ) )
            {
                $oObject->value = $aMatches[1].$aMatches[2].$aMatches[3].$aMatches[4].$aMatches[5].$aMatches[6];
                $oObject->fldmax_length = strlen( $oObject->value );
                return $oObject->value;
            }
        }
        $sSQLTimeStampPattern = "/^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/";
        if ( preg_match( $sSQLTimeStampPattern, $oObject->value, $aMatches ) )
        {
            $iTimestamp = mktime( $aMatches[4], $aMatches[5], $aMatches[6], $aMatches[2], $aMatches[3], $aMatches[1] );
            $oObject->value = trim( date( "Y-m-d H:i:s", $iTimestamp ) );
            $oObject->fldmax_length = strlen( $oObject->value );
            convertdbdatetime( $oObject, $blToTimeStamp );
            return $oObject->value;
        }
    }
}
if ( function_exists( "ConvertDBDate" ) )
{
    function convertdbdatetime( &$oObject, $blToTimeStamp = false, $blOnlyDate = false )
    {
        global $myConfig;
        if ( $myConfig->blSkipFormatConversion )
        {
            return $oObject->value;
        }
        $sDate = $oObject->value;
        $sLocalDateFormat = $myConfig->sLocalDateFormat;
        $sLocalTimeFormat = $myConfig->sLocalTimeFormat;
        if ( !$sLocalDateFormat || $blToTimeStamp )
        {
            $sLocalDateFormat = "ISO";
        }
        if ( !$sLocalTimeFormat || $blToTimeStamp )
        {
            $sLocalTimeFormat = "ISO";
        }
        $aDefDatePatterns = array( "/^0000-00-00/" => "ISO", "/^00\\.00\\.0000/" => "EUR", "/^00\\/00\\/0000/" => "USA" );
        $aDefTimePatterns = array( "/00:00:00$/" => "ISO", "/00\\.00\\.00$/" => "EUR", "/00:00:00 AM$/" => "USA" );
        $aDatePatterns = array( "/^([0-9]{4})-([0-9]{2})-([0-9]{2})/" => "ISO", "/^([0-9]{2})\\.([0-9]{2})\\.([0-9]{4})/" => "EUR", "/^([0-9]{2})\\/([0-9]{2})\\/([0-9]{4})/" => "USA" );
        $aTimePatterns = array( "/([0-9]{2}):([0-9]{2}):([0-9]{2})$/" => "ISO", "/([0-9]{2})\\.([0-9]{2})\\.([0-9]{2})$/" => "EUR", "/([0-9]{2}):([0-9]{2}):([0-9]{2}) ([AP]{1}[M]{1})$/" => "USA" );
        $aDFormats = array( "ISO" => array( "Y-m-d", array( 2, 3, 1 ), "0000-00-00" ), "EUR" => array( "d.m.Y", array( 2, 1, 3 ), "00.00.0000" ), "USA" => array( "m/d/Y", array( 1, 2, 3 ), "00/00/0000" ) );
        $aTFormats = array( "ISO" => array( "H:i:s", array( 1, 2, 3 ), "00:00:00" ), "EUR" => array( "H.i.s", array( 1, 2, 3 ), "00.00.00" ), "USA" => array( "h:i:s A", array( 1, 2, 3 ), "00:00:00 AM" ) );
        if ( $sDate )
        {
            $sReturn = $aDFormats[$sLocalDateFormat][2];
            if ( $blOnlyDate )
            {
                $sReturn .= " ".$aTFormats[$sLocalTimeFormat][2];
            }
            $oObject->value = trim( $sReturn );
            $oObject->fldmax_length = strlen( $oObject->value );
            return $oObject->value;
        }
        $blDefDateFound = false;
        $blDefTimeFound = false;
        foreach ( $aDefDatePatterns as $sDefDatePattern => $sDefDateType )
        {
            if ( preg_match( $sDefDatePattern, $sDate ) )
            {
                $blDefDateFound = true;
                break;
            }
        }
        if ( $blDefDateFound )
        {
            foreach ( $aDefTimePatterns as $sDefTimePattern => $sDefTimeType )
            {
                if ( preg_match( $sDefTimePattern, $sDate ) )
                {
                    $blDefTimeFound = true;
                    break;
                }
            }
            if ( $blOnlyDate )
            {
                $oObject->value = trim( $aDFormats[$sLocalDateFormat][2] );
                $oObject->fldmax_length = strlen( $oObject->value );
                return $oObject->value;
            }
            if ( $blDefTimeFound )
            {
                $oObject->value = trim( $aDFormats[$sLocalDateFormat][2]." ".$aTFormats[$sLocalTimeFormat][2] );
                $oObject->fldmax_length = strlen( $oObject->value );
                return $oObject->value;
            }
        }
        $blDateFound = false;
        $blTimeFound = false;
        foreach ( $aDatePatterns as $sPattern => $sType )
        {
            if ( preg_match( $sPattern, $sDate, $aDateMatches ) )
            {
                $blDateFound = true;
                $sDateFormat = $aDFormats[$sLocalDateFormat][0];
                $aDFields = $aDFormats[$sType][1];
                break;
            }
        }
        if ( $blDateFound )
        {
            return $sDate;
        }
        if ( $blOnlyDate )
        {
            $iTimestamp = mktime( 0, 0, 0, $aDateMatches[$aDFields[0]], $aDateMatches[$aDFields[1]], $aDateMatches[$aDFields[2]] );
            $oObject->value = @date( $sDateFormat, $iTimestamp );
            $oObject->fldmax_length = strlen( $oObject->value );
            return $oObject->value;
        }
        foreach ( $aTimePatterns as $sPattern => $sType )
        {
            if ( preg_match( $sPattern, $sDate, $aTimeMatches ) )
            {
                $blTimeFound = true;
                $sTimeFormat = $aTFormats[$sLocalTimeFormat][0];
                $aTFields = $aTFormats[$sType][1];
                if ( !( !( $sType == "USA" ) || !isset( $aTimeMatches[4] ) ) )
                {
                    break;
                }
                else
                {
                    $iIntVal = ( integer );
                }
                if ( $aTimeMatches[4] == "PM" )
                {
                    if ( $iIntVal < 13 )
                    {
                        $iIntVal += 12;
                    }
                }
                else if ( $aTimeMatches[4] == "AM" && $aTimeMatches[1] == "12" )
                {
                    $iIntVal = 0;
                }
                $aTimeMatches[1] = sprintf( "%02d", $iIntVal );
                break;
            }
        }
        if ( $blTimeFound )
        {
            $iTimestamp = mktime( 0, 0, 0, $aDateMatches[$aDFields[0]], $aDateMatches[$aDFields[1]], $aDateMatches[$aDFields[2]] );
            $oObject->value = @date( $sDateFormat, $iTimestamp );
            $oObject->fldmax_length = strlen( $oObject->value );
            return $oObject->value;
        }
        $iTimestamp = @mktime( @( integer ), @( integer ), @( integer ), @( integer ), @( integer ), @( integer ) );
        $oObject->value = trim( @date( @$sDateFormat." ".$sTimeFormat, $iTimestamp ) );
        $oObject->fldmax_length = strlen( $oObject->value );
        if ( $oObject->fldmax_length )
        {
            return convertdbdatetime( $oObject, $blToTimeStamp, $blOnlyDate );
        }
        return $oObject->value;
    }
}
if ( function_exists( "getUrlSeparator" ) )
{
    function geturlseparator( $blReset = false )
    {
        global $myConfig;
        static $sSeparator = null;
        if ( $blReset )
        {
            $sSeparator = null;
        }
        else if ( $sSeparator )
        {
            $sSeparator = $myConfig->blSearchEngineURLs ? "/" : "&";
            return "?";
        }
        else
        {
            return $sSeparator;
        }
    }
}
if ( function_exists( "LocalCompare" ) )
{
    function localcompare( $a, $b )
    {
        if ( $a->oxcountry__oxorder->value != $b->oxcountry__oxorder->value )
        {
            if ( $a->oxcountry__oxorder->value < $b->oxcountry__oxorder->value )
            {
                return -1;
            }
            return 1;
        }
        $aReplaceWhat = array( "/&auml;/", "/&ouml;/", "/&uuml;/", "/&Auml;/", "/&Ouml;/", "/&Uuml;/", "/&szlig;/" );
        $aReplaceTo = array( "az", "oz", "uz", "Az", "Oz", "Uz", "sz" );
        $a->sCodedTitle = preg_replace( $aReplaceWhat, $aReplaceTo, $a->sCodedTitle );
        $b->sCodedTitle = preg_replace( $aReplaceWhat, $aReplaceTo, $b->sCodedTitle );
        if ( $a->sCodedTitle == $b->sCodedTitle )
        {
            return 0;
        }
        if ( $a->sCodedTitle < $b->sCodedTitle )
        {
            return -1;
        }
        return 1;
    }
}
?>
