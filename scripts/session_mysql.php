<?php
## Datenstruktur initialisieren #
  unset( $session );
  $session['sid'] = $get['sid'];
  $session['uid'] = $get['uid'];
  
  # Parameter für Sessionmanagement
  $gstime = 600;		# 600 Sek. = 5 Minuten - Guest Session Timeout
  $lstime = 3600;		# 3600 Sek. = 1 Stunde - Logged in Session Timeout

## Login - falls Daten übermittelt
  # Login durchführen - nur mit gültiger Gast-Session (jünger als $gstime)
  if( isset( $get['login'] ) && isset( $session['sid'] ) && $session['uid'] == 0 ) {
    # prüfen ob Session gültig / nicht älter als $gstime
    $r = sql(' SELECT sid FROM tbsession WHERE 
      uid = "'.$session['uid'].'" AND 
      sid = "'.$session['sid'].'" AND 
      ( UNIX_TIMESTAMP() - timestamp ) < '.$gstime.' AND
      browser = "'.substr( $request['browser'], 0, 99 ).'" AND
      ip = "'.$request['ip'].'";' );
    # Session gefunden, Login prüfen
    if( mysqli_affected_rows( $db ) ) {
      $r = sql( 'SELECT * FROM tbuser WHERE username = "'.$get['username'].'" AND password = "'.$get['password'].'";' );
      # Prüfen, ob Account gefunden wurde
      if( $session = mysqli_fetch_assoc( $r ) ) {
        # Login OK
        # alte Session löschen
        sql( 'DELETE FROM tbsession WHERE sid = "'.$session['sid'].'";' );
        # neue Session erzeugen
        if( $request['robot'] ) $session['sid'] = 0;
        else $session['sid'] = md5( time().$session['uid'] );
        sql( 'INSERT INTO tbsession SET 
          uid = "'.$session['uid'].'", 
          sid = "'.$session['sid'].'", 
          domain = "'.$request['domain'].'", 
          timestamp = UNIX_TIMESTAMP(), 
          page = "'.$get['page'].'", 
          url = "'.$request['url'].'", 
          browser = "'.substr( $request['browser'], 0, 99 ).'", 
          ip = "'.$request['ip'].'",
          host = "'.$request['host'].'",
          referer = "'.$request['referer'].'";' );
        $session['login'] = true;
      } else {
        # Kein Account gefunden:
        $msg .= 'Benutzername oder Passwort falsch';
        $session['login'] = false;
      }
    } # Ende Login prüfen
    # In jedem Fall, in dem der Login nicht erfolgreich war betroffene Session löschen und erneuern:
    if( !$session['login'] ) {
      sql( 'DELETE FROM tbsession WHERE sid = "'.$get['sid'].'";' );
      $session['login'] = false;
    } # Ende Löschen
  } # Ende Login
  
## Prüfung, Erzeugen oder Aktualisieren der Session
  # Session in Datenbank suchen
  $r = sql(' SELECT * FROM tbsession WHERE 
    uid = "'.$session['uid'].'" AND 
    sid = "'.$session['sid'].'" AND 
    ( UNIX_TIMESTAMP() - timestamp ) < '.$lstime.' AND
    browser = "'.substr( $request['browser'], 0, 99 ).'" AND
    ip = "'.$request['ip'].'";' );
  if( $session = mysqli_fetch_assoc( $r ) ) {
    # Session gefunden
    # User eingeloggt?
    if( $session['uid'] != 0 ) {
      # User eingeloggt
      $session['login'] = true;
      # Berechtigungen einlesen
      $r = sql( 'SELECT privilege FROM tbpermission WHERE uid = '.$session['uid'].';' );
      while( $data = mysqli_fetch_assoc( $r ) ) {
        $session['permission'][$data['privilege']] = true;
      }
      # Userdaten einlesen
      $r = sql( 'SELECT * FROM tbuser WHERE uid = '.$session['uid'].';' );
      $user = mysqli_fetch_assoc( $r );
      # Ende eingeloggter Nutzer
    } else {
      # User nicht eingeloggt
      $session['login'] = false;
      $session['uid'] = 0;
    } # Ende nicht eingeloggter Nutzer
    # Sessiondaten in Datenbank aktualisieren
    sql( 'UPDATE tbsession SET 
      domain = "'.$request['domain'].'", 
      timestamp = UNIX_TIMESTAMP(), 
      page = "'.$get['page'].'", 
      url = "'.$request['url'].'"
      WHERE sid = "'.$get['sid'].'";' );
    # Ende gefundene Session
  } else {
    # Session ungültig
    sql( 'DELETE FROM tbsession WHERE sid = "'.$get['sid'].'";' );
    # SessionID generieren
    if( $request['robot'] ) $session['sid'] = 0;
    else $session['sid'] = md5( uniqid( rand(), true ) );
    # Session-Array herstellen
    $session['uid'] = 0;
    $session['login'] = false;
    $session['timestamp'] = time();
    $session['ip'] = $request['ip'];
    $session['page'] = $get['page'];
    # Sessiondaten in Datenbank speichern
    sql( 'INSERT INTO tbsession SET 
      uid = "'.$session['uid'].'", 
      sid = "'.$session['sid'].'", 
      domain = "'.$request['domain'].'", 
      timestamp = UNIX_TIMESTAMP(), 
      page = "'.$get['page'].'", 
      url = "'.$request['url'].'", 
      browser = "'.substr( $request['browser'], 0, 99 ).'", 
      ip = "'.$request['ip'].'",
      host = "'.$request['host'].'",
      referer = "'.$request['referer'].'";' );
  } # Ende ungültige Session

  # Sessionlink erzeugen
  $session['link'] = 'sid,'.$session['sid'].'/uid,'.$session['uid'];

## alte Sessions löschen
  sql( 'DELETE FROM tbsession WHERE ( UNIX_TIMESTAMP() - timestamp > '.$gstime.' ) AND uid = 0;' );
  sql( 'DELETE FROM tbsession WHERE ( UNIX_TIMESTAMP() - timestamp > '.$lstime.' ) AND uid > 0;' );
?>