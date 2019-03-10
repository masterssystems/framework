<?php
  # Datenstruktur initialisieren #
  unset( $get );

## URI zerlegen ##
  $variablen = explode( '/', $_SERVER['REQUEST_URI'] );
  # Eintrag gefunden? #
  foreach( $variablen as $variable ) {
    # Eintrag zerlegen #
    $vteile = explode( ',', $variable );
    # HTML-Tags entfernen, Sonderzeichen umwandeln, Befehlszeichen escapen #
    if( isset( $vteile[1] ) ) $get[$vteile[0]] = addslashes( strip_tags( urldecode( $vteile[1] ) ) );
    else $get[$vteile[0]] = true;
  }
## GET-Variablen einlesen ##
  # Eintrag gefunden? #
  foreach( $_GET as $variable => $key ) {
    # HTML-Tags entfernen, Sonderzeichen umwandeln, Befehlszeichen escapen #
    $get[$variable] = addslashes( strip_tags( $key ) );
    unset( $$variable );
  }
## POST-Variablen einlesen ##
  # Eintrag gefunden? #
  foreach( $_POST as $variable => $key ) {
    # Eintrag ist Array? #
    if( is_array( $key ) ) {
      # Eintrag gefunden? #
      foreach( $key as $index => $subkey ) {
        # HTML-Tags entfernen, Sonderzeichen umwandeln, Befehlszeichen escapen #
        $get[$variable][$index] = addslashes( strip_tags( $subkey ) );
        unset( $$variable );
      }
    } else {
      # HTML-Tags entfernen, Sonderzeichen umwandeln, Befehlszeichen escapen #
      $get[$variable] = addslashes( strip_tags( $key ) );
      unset( $$variable );
    }
  }

	function urlsetparameter( $parameter ) {
		global $get;
		
		# build array from get string (we don't use $get as to not get a lot of internal variables added)
		$temp = array();
		$pair = explode( '/', $_SERVER['REQUEST_URI'] );
		# iterate over variable => value pairs
		foreach( $pair as $variable ) {
			$variable = explode( ',', $variable );
			if( isset( $variable[1] ) ) $temp[$variable[0]] = addslashes( strip_tags( urldecode( $variable[1] ) ) );
		}

		# add given parameters, overwriting existing ones
		foreach( $parameter as $key => $value ) {
			$temp[$key] = $value;
		}
		# get page mode
		$mode = 'page';
		if( isset( $get['raw'] ) ) {
			$mode = 'raw';
			unset( $temp['raw'] );
		} elseif( isset( $get['print'] ) ) {
			$mode = 'print';
			unset( $temp['print'] );
		} elseif( isset( $get['ajax'] ) ) {
			$mode = 'ajax';
			unset( $temp['ajax'] );
		} # if isset raw
		
		# start url string
		$url = FRAMEWORK_ROOT;
		# add page mode as first
		if( isset( $get['page'] ) && ( $get['page'] != '' ) ) $url .= '/'.$mode.','.$get['page'];

		# iterate over all variables
		foreach( $temp as $key => $value ) {
			# add variable to url string
			if( ( $key != 'page' ) && ( $key != '' ) ) $url .= '/'.$key.','.$value;
		} # foreach temp
		
		return( $url );
	}

  unset( $variablen );
  unset( $variable );
  unset( $vteile );
  unset( $key );
?>
