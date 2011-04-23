<?php 

// encode
echo $val = str_man ('netbouti', 'fq45QS09_fqyx09239QQ');
echo " -> ";
// decode
echo $val = str_rem ('ox_BBpVRzY8UlE4EAcI', 'fq45QS09_fqyx09239QQ'); // 
echo "\n";
// decode
echo $val = str_rem ('ox_BBpSUiMwRVc+FF8WGlheRFJLNhESC1pDKH1AWyUQAA!!', 'fq45QS09_fqyx09239QQ');
echo "\n";

    function str_prot13( $str )
    {
        $from = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $to = "nopqrstuvwxyzabcdefghijklmNOPQRSTUVWXYZABCDEFGHIJKLM";
        return strtr( $str, $from, $to );
    }
    
    function str_man( $sVal, $sKey = null )
    {
        global $myConfig;
        /* if ( $sKey )
        {
            $sKey = "oxid123456789";
            echo "oui";
        } */
        $sVal = "ox".$sVal."id";
        $sKey = str_repeat( $sKey, strlen( $sVal ) / strlen( $sKey ) + 5 );
        $sVal = str_prot13( $sVal );
        $sVal ^= $sKey;
        $sVal = base64_encode( $sVal );
        $sVal = str_replace( "=", "!", $sVal );
        $sVal = "ox_".$sVal;
        return $sVal;
    }
    function str_rem( $sVal, $sKey = null )
    {
        global $myConfig;
        /* if ( $sKey )
        {
            $sKey = "oxid123456789";
            echo "oui";
        } */
        $sKey = str_repeat( $sKey, strlen( $sVal ) / strlen( $sKey ) + 5 );
        $sVal = substr( $sVal, 3 );
        $sVal = str_replace( "!", "=", $sVal );
        $sVal = base64_decode( $sVal );
        $sVal ^= $sKey;
        $sVal = str_prot13( $sVal );
        $sVal = substr( $sVal, 2, -2 );
        return $sVal;
    }
?>
