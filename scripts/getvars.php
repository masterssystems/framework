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
    $vteile[0] = urldecode( $vteile[0] );
    
    if( isset( $vteile[1] ) ) {
    	# split variable name into name and array key
    	preg_match( '/(.*)\[(.*)\]/', $vteile[0], $variable );
    	# set array variable
    	if( isset( $variable[2] ) ) $get[$variable[1]][$variable[2]] = addslashes( strip_tags( urldecode( $vteile[1] ) ) );
    	# set regular variable
    	else $get[$vteile[0]] = addslashes( strip_tags( urldecode( $vteile[1] ) ) );
    	
    } else $get[$vteile[0]] = true;
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
  foreach( $_POST as $key1 => $variable1 ) {
    # Eintrag ist Array? #
    if( is_array( $variable1 ) ) {
      # Eintrag gefunden? #
      foreach( $variable1 as $key2 => $variable2 ) {
				# Eintrag ist Array? #
				if( is_array( $variable2 ) ) {
					# Eintrag gefunden? #
					foreach( $variable2 as $key3 => $variable3 ) {
						# HTML-Tags entfernen, Sonderzeichen umwandeln, Befehlszeichen escapen #
						$get[$key1][$key2][$key3] = addslashes( strip_tags( $variable3 ) );
					}
				} else {
					# HTML-Tags entfernen, Sonderzeichen umwandeln, Befehlszeichen escapen #
					$get[$key1][$key2] = addslashes( strip_tags( $variable2 ) );
				}
      }
    } else {
      # HTML-Tags entfernen, Sonderzeichen umwandeln, Befehlszeichen escapen #
      $get[$key1] = addslashes( strip_tags( $variable1 ) );
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
  