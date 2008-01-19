<?php

$OPENQRM_SERVER_CONFIG_FILE="/opt/openqrm/etc/openqrm-server.conf";


// function to get infos from the openqrm-server.conf
function openqrm_parse_conf ( $filepath ) {
    $ini = file( $filepath );
    if ( count( $ini ) == 0 ) { return array(); }
    $sections = array();
    $values = array();
    $globals = array();
    $i = 0;
    foreach( $ini as $line ){
        $line = trim( $line );
        // Comments
        if ( $line == '' || $line{0} == '#' ) { continue; }
        // Key-value pair
        list( $key, $value ) = explode( '=', $line, 2 );
        $key = trim( $key );
        $value = trim( $value );
        $value = str_replace("\"", "", $value );
        $globals[ $key ] = $value;
    }
    return $globals;
}


$store = openqrm_parse_conf($OPENQRM_SERVER_CONFIG_FILE);
extract($store);
global $OPENQRM_SERVER_CONFIG_FILE;

?>
