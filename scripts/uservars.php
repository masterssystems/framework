<?php
  # Datenstruktur initialisieren #
  unset( $request );
## Hostname zerlegen ##
  $host = explode( '.', $_SERVER['HTTP_HOST'] );
  ## Host, Domain, TLD in Array abspeichern
  $host = array_reverse( $host );
    ## TLD ##
    $request['tld'] = $host[0];
    ## Domain-Name ##
    $request['domain'] = $host[1];
    ## Host-Name ##
    $request['host'] = $host[2];
  unset( $host );
## IP-Adresse abspeichern ##
  $request['ip'] = $_SERVER['REMOTE_ADDR'];
## Request-URI abspeichern ##
  $request['get'] = $_SERVER['REQUEST_URI'];  
## Request-URL zusammenbauen und abspeichern ##
  $request['url'] = urlencode( $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );
## Sprachpräferenzen zerlegen ##
  # Eintrag gefunden? #
  foreach( explode( ',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) as $langk ) {
    # Eintrag zerlegen #
    $langs = explode( ';', $langk );
    # Sprachkürzel speichern #
    $request['lang'][$langs[0]] = true;
  }
  unset( $langk );
  unset( $langs );
## Referer abspeichern ##
  $request['referer'] = $_SERVER['HTTP_REFERER'];
## Browser abspeichern ##
  $request['browser'] = $_SERVER['HTTP_USER_AGENT'];
  if( stripos( $request['browser'], 'bot' ) ) $request['robot'] = true;
  if( stripos( $request['browser'], 'crawl' ) ) $request['robot'] = true;
?>
