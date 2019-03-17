<?php
  # Zeichensatz festlegen #
  header( 'Content-type: text/html;charset=utf-8' );
#### Variablen einlesen ####
  include( 'scripts/getvars.php' );
#### User-Variablen einlesen ####
  include( 'scripts/uservars.php' );
#### Konfigurationen laden ####
  include( 'config/config.php' );
  ## Debug-Modus ##
  error_reporting( FRAMEWORK_DEBUG );
#### Datenbank initialisieren ####
  if( FRAMEWORK_DBI ) {
    include( 'scripts/'.FRAMEWORK_DBI.'.php' );
    # Datenbankverbindung öffnen #
    $db = sql_connect();
    # Seite definiert? #
  }
#### Session initialisieren ####
  if( FRAMEWORK_AUTHMETHOD && FRAMEWORK_SESSIONMGR ) {
  	include( 'scripts/'.FRAMEWORK_AUTHMETHOD.'.php' );
    include( 'scripts/'.FRAMEWORK_SESSIONMGR.'.php' );
    # Login erfolgreich? #
    if( FRAMEWORK_FORCELOGIN && !$session['login'] ) {
      # Fehlerseite setzen #
      $get['page'] = FRAMEWORK_FORCELOGIN;
    } elseif( FRAMEWORK_FORCELOGIN && $session['login'] && ( $get['page'] == FRAMEWORK_FORCELOGIN ) ) {
    	unset( $get['page'] );
    }
  }
#### Seite initialisieren ####
  # Druckversion initialisieren
  if( $get['print'] && !$get['page'] ) {
    $get['page'] = $get['print'];
    $get['print'] = true;
  } # Ende Druckversion
  # Rohversion initialisieren
  if( $get['raw'] && !$get['page'] ) {
    $get['page'] = $get['raw'];
    $get['raw'] = true;
  } # Ende Rohversion
  if( !$get['page'] ) {
    # Startseite setzen
    $get['page'] = FRAMEWORK_TOP_LINK;
    # Falls ein GET-String ohne Seitenangabe übergeben wurde Fehler 404 auslösen
#    if( $_SERVER['REQUEST_URI'] != '/' && $_SERVER['REQUEST_URI'] != '' ) {
      # Fehlerseite setzen #
#      $get['page'] = '404';
      # Status-Header 404 senden #
#      header( 'HTTP/1.0 404 Not Found' );
#    }
  } else {
    # Seite existiert? #
    if( !file_exists( 'body/'.$get['page'].'.php' ) && !file_exists( 'meta/'.$get['page'].'.php' ) ) {
      # Fehlerseite setzen #
      $get['page'] = '404';
      # Status-Header 404 senden #
      header( 'HTTP/1.0 404 Not Found' );
    }
  }
  
	#### Sprache initialisieren ####
	if( FRAMEWORK_MULTILANG ) include( 'scripts/language.php' );
	else $get['lang'] = FRAMEWORK_DEFAULTLANG;
	
	#### Kopfdaten laden
	# Kopfdatei existiert?
	if( file_exists( 'meta/'.$get['page'].'.php' ) ) {
	  #### Kopfdaten initialisieren ####
	  include( 'meta/'.$get['page'].'.php' );
	}
	# Rückfall falls benötigte Konstanten nicht definiert sind
	if( !defined( 'TITLE' ) ) define( 'TITLE', $get['page'] );
	
	# Ausgabe - normal (nicht Rohversion)
	if( !$get['raw'] ) {
	  #### HTTP-Kopf ausgeben ####
	  echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"'."\n";
	  echo '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
	  echo '<html xmlns="http://www.w3.org/1999/xhtml" lang="'.$get['lang'].'" xml:lang="'.$get['lang'].'">'."\n";
	  echo '<head>'."\n";
    if( defined( 'DESCRIPTION' ) ) echo '<meta name="description" content="'.strip_tags( DESCRIPTION ).'"/>'."\n";
    if( defined( 'KEYWORDS' ) ) echo '<meta name="keywords" content="'.KEYWORDS.'"/>'."\n";
    echo '<meta name="DC.Title" content="'.TITLE.'"/>'."\n";
    if( defined( 'DESCRIPTION' ) ) echo '<meta name="DC.Description" content="'.strip_tags( DESCRIPTION ).'"/>'."\n";
    echo '<title>'.TITLE.' - '.FRAMEWORK_TITLE.'</title>'."\n";
	  echo '<meta name="DC.Language" content="'.$get['lang'].'"/>'."\n";
	  echo '<meta name="language" content="'.$get['lang'].'"/>'."\n";
	  echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>'."\n";
	  echo '<meta name="author" content="'.FRAMEWORK_AUTHOR.'"/>'."\n";
	  echo '<meta name="publisher" content="'.FRAMEWORK_COMPANY.'"/>'."\n";
	  echo '<meta name="copyright" content="'.FRAMEWORK_COPYRIGHT.'"/>'."\n";
	  echo '<meta name="DC.Creator" content="'.FRAMEWORK_AUTHOR.'"/>'."\n";
	  echo '<meta name="DC.Subject" content="'.FRAMEWORK_SUBJECT.'"/>'."\n";
	  echo '<meta name="DC.Publisher" content="'.FRAMEWORK_COMPANY.'"/>'."\n";
	  echo '<meta name="DC.Coverage" content="'.FRAMWORKD_COVERAGE.'"/>'."\n";
	  echo '<meta name="DC.Rights" content="'.FRAMEWORK_COPYRIGHT.'"/>'."\n";
	  echo '<meta http-equiv="expires" content="'.FRAMEWORK_EXPIRES.'"/>'."\n";
	  echo '<meta name="page-topic" content="'.FRAMEWORK_SUBJECT.'"/>'."\n";
	  echo '<meta name="audience" content="'.FRAMEWORK_AUDIENCE.'"/>'."\n";
	  echo '<meta name="page-type" content="'.FRAMEWORK_SUBJECT.'"/>'."\n";
	  echo '<meta name="robots" content="'.FRAMEWORK_ROBOTS.'"/>'."\n";
	  echo '<meta name="revisit-after" content="'.FRAMEWORK_REVISIT.'"/>'."\n";
	  echo '<meta http-equiv="cache-control" content="'.FRAMEWORK_CACHE.'"/>'."\n";
	  # Druckversion lädt andere CSS-Datei
	  if( $get['print'] === true ) echo '<link rel="stylesheet" type="text/css" href="'.FRAMEWORK_ROOT.'/print.css"/>';
	  else echo '<link rel="stylesheet" type="text/css" media="screen" href="'.FRAMEWORK_ROOT.'/page.css"/>';
	  echo '<link rel="stylesheet" type="text/css" media="print" href="'.FRAMEWORK_ROOT.'/print.css"/>';
	  # Ende CSS
	  echo '<link rel="top" href="'.FRAMEWORK_ROOT.'/page,'.FRAMEWORK_TOP_LINK.'" title="'.FRAMEWORK_TOP_LINK.'"/>'."\n";
	  echo '<link rel="copyright" href="'.FRAMEWORK_ROOT.'/page,'.FRAMEWORK_COPYRIGHT_LINK.'" title="'.FRAMEWORK_COPYRIGHT.'"/>'."\n";
	  echo '<link rel="icon" href="'.FRAMEWORK_ROOT.'/favicon.ico" type="image/x-icon"/>'."\n";
	  echo '<link rel="shortcut icon" href="'.FRAMEWORK_ROOT.'/favicon.ico" type="image/x-icon"/>'."\n";
	}
	# Meta-Indexdatei laden
	include( 'meta/index.php' );
	if( !$get['raw'] ) {
	  #### ENDE HTTP-Kopf ####
	  echo '</head>'."\n";
	
	  #### Inhalt ausgeben ####
	  echo '<body>'."\n";
	  if( $get['print'] === true ) include( 'body/'.$get['page'].'.php' );
	  else include( 'body/index.php' );
	  echo '</body>'."\n";
	  echo '</html>'."\n";
	  #### ENDE Inhalt ####
	} # ENDE Ausgabe
	if( $get['raw'] && file_exists( 'body/'.$get['page'].'.php' ) ) include( 'body/'.$get['page'].'.php' );
	#### Datenbankverbindung schliessen ####
	if( FRAMEWORK_DBI ) sql_disconnect();
	