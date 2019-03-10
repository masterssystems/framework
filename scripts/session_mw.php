<?php
## Datenstruktur initialisieren #
  unset( $session );
  session_set_cookie_params( 0, '/', '.'.$request['domain'].'.'.$request['tld'], false, false );
  session_start();
  
  # Parameter für Sessionmanagement
  $gstime = 600;		# 600 Sek. = 5 Minuten - Guest Session Timeout
  $lstime = 3600;		# 3600 Sek. = 1 Stunde - Logged in Session Timeout

## Login - falls Daten übermittelt
  # Login durchführen - nur mit gültiger Gast-Session (jünger als $gstime)
  if( isset( $get['login'] ) && isset( $_SESSION['sid'] ) && $_SESSION['uid'] == 0 ) {
    # prüfen ob Session gültig / nicht älter als $gstime
    $r = sql(' SELECT sid FROM tbsession WHERE 
      uid = "'.$_SESSION['uid'].'" AND 
      sid = "'.$_SESSION['sid'].'" AND 
      ( UNIX_TIMESTAMP() - timestamp ) < '.$gstime.' AND
      browser = "'.substr( $request['browser'], 0, 255 ).'" AND
      ip = "'.$request['ip'].'";' );
    # Session gefunden, Login prüfen
    if( mysqli_affected_rows( $db ) ) {
      $r = sql( 'SELECT * FROM tbuser WHERE username = "'.$get['username'].'" AND password = "'.$get['password'].'";' );
      # Prüfen, ob Account gefunden wurde
      if( $_SESSION = mysqli_fetch_assoc( $r ) ) {
        # Login OK
        # alte Session löschen
        sql( 'DELETE FROM tbsession WHERE sid = "'.$_SESSION['sid'].'";' );
        # neue Session erzeugen
        $_SESSION['sid'] = md5( time().$_SESSION['uid'] );
        sql( 'INSERT INTO tbsession SET 
          uid = "'.$_SESSION['uid'].'", 
          sid = "'.$_SESSION['sid'].'", 
          domain = "'.$request['domain'].'", 
          timestamp = UNIX_TIMESTAMP(), 
          page = "'.$get['page'].'", 
          url = "'.$request['url'].'", 
          browser = "'.$request['browser'].'", 
          ip = "'.$request['ip'].'",
          host = "'.$request['host'].'",
          referer = "'.$request['referer'].'";' );
        $_SESSION['login'] = true;
      } else {
        # Kein Account gefunden:
        $msg .= 'Benutzername oder Passwort falsch';
        $_SESSION['login'] = false;
      }
    } # Ende Login prüfen
    # In jedem Fall, in dem der Login nicht erfolgreich war betroffene Session löschen und erneuern:
    if( !$_SESSION['login'] ) {
      sql( 'DELETE FROM tbsession WHERE sid = "'.$_SESSION['sid'].'";' );
      $_SESSION['login'] = false;
      session_unset();
    } # Ende Löschen
  } # Ende Login
  
## Prüfung, Erzeugen oder Aktualisieren der Session
  # Session in Datenbank suchen
  $r = sql(' SELECT * FROM tbsession WHERE 
    uid = "'.$_SESSION['uid'].'" AND 
    sid = "'.$_SESSION['sid'].'" AND 
    ( UNIX_TIMESTAMP() - timestamp ) < '.$lstime.' AND
    browser = "'.substr( $request['browser'], 0, 255 ).'" AND
    ip = "'.$request['ip'].'";' );
  if( $data = mysqli_fetch_assoc( $r ) ) {
    # Session gefunden
    # User eingeloggt?
    if( $_SESSION['uid'] != 0 ) {
      # User eingeloggt
      $_SESSION['login'] = true;
      # Berechtigungen einlesen
      $r = sql( 'SELECT privilege FROM tbpermission WHERE uid = '.$_SESSION['uid'].';' );
      while( $data = mysqli_fetch_assoc( $r ) ) {
        $_SESSION['permission'][$data['privilege']] = true;
      }
      # Userdaten einlesen
      $r = sql( 'SELECT * FROM tbuser WHERE uid = '.$_SESSION['uid'].';' );
      $user = mysqli_fetch_assoc( $r ) + $user;
      # Ende eingeloggter Nutzer
    } else {
      # User nicht eingeloggt
      $_SESSION['login'] = false;
      $_SESSION['uid'] = 0;
    } # Ende nicht eingeloggter Nutzer
    # Sessiondaten in Datenbank aktualisieren
    sql( 'UPDATE tbsession SET 
      domain = "'.$request['domain'].'", 
      timestamp = UNIX_TIMESTAMP(), 
      page = "'.$get['page'].'", 
      url = "'.$request['url'].'"
      WHERE sid = "'.$_SESSION['sid'].'";' );
    # Ende gefundene Session
  } else {
    # Session ungültig
    sql( 'DELETE FROM tbsession WHERE sid = "'.$_SESSION['sid'].'";' );
    # SessionID generieren
    $_SESSION['sid'] = md5( uniqid( rand(), true ) );
    # Session-Array herstellen
    $_SESSION['uid'] = 0;
    $_SESSION['login'] = false;
    $_SESSION['timestamp'] = time();
    $_SESSION['ip'] = $request['ip'];
    $_SESSION['page'] = $get['page'];
    # Sessiondaten in Datenbank speichern
    sql( 'INSERT INTO tbsession SET 
      uid = "'.$_SESSION['uid'].'", 
      sid = "'.$_SESSION['sid'].'", 
      domain = "'.$request['domain'].'", 
      timestamp = "'.$_SESSION['timestamp'].'", 
      page = "'.$get['page'].'", 
      url = "'.$request['url'].'", 
      browser = "'.$request['browser'].'", 
      ip = "'.$request['ip'].'",
      host = "'.$request['host'].'",
      referer = "'.$request['referer'].'";' );
  } # Ende ungültige Session

  $session = $_SESSION;

## alte Sessions löschen
  sql( 'DELETE FROM tbsession WHERE ( UNIX_TIMESTAMP() - timestamp > '.$gstime.' ) AND uid = 0;' );
  sql( 'DELETE FROM tbsession WHERE ( UNIX_TIMESTAMP() - timestamp > '.$lstime.' ) AND uid > 0;' );
?>