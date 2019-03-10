<?
  ## Wenn eine Sprachumschaltung aktiviert wurde, dann diese �bernehmen:
  if( $get['lang'] != '' ) $user['lang'] = $get['lang'];
  ## Benutzer hat Spracheinstellung?
  if( is_dir( 'lang/'.$user['lang'] ) ) {
    ## Spracheinstellung auf Benutzereinstellungen setzen
    $get['lang'] = $user['lang'];
  } else {
    ## Hostnamen pr�fen:
    if( is_dir( 'lang/'.$user['host'] ) ) $get['lang'] = $user['host'];
    ## Benutzerpr�ferenzen durchlaufen: ##
    if( !$get['lang'] ) {
      foreach( $user['lang'] as $lang => $bit ) {
        if( is_dir( 'lang/'.$lang ) ) {
          $get['lang'] = $lang;
          break;
        }
      }
    }
    ## letzte L�sung: TLD als Sprachendung ##
    if( !$get['lang'] ) {
      if( is_dir( 'lang/'.$user['tld'] ) ) $get['lang'] = $user['tld'];
    }
    ## Sprache gefunden?
    if( !$get['lang'] ) {
      # Sprache nicht gefunden
      ## verwende Standardsprache ##
      $get['lang'] = FRAMEWORK_DEFAULTLANG;
    }
    unset( $lang );
  }
    
## ermittelte Sprachdatei laden ##
  ## index.php (Systemmeldungen, Header, Footer)
  if( file_exists( 'lang/'.$get['lang'].'/index.php' ) ) include( 'lang/'.$get['lang'].'/index.php' );
  elseif( file_exists( 'lang/'.FRAMEWORK_DEFAULTLANG.'/index.php' ) ) include( 'lang/'.FRAMEWORK_DEFAULTLANG.'/index.php' );
  ## menu.php (�bersetzung des Men�s)
  if( file_exists( 'lang/'.$get['lang'].'/menu.php' ) ) include( 'lang/'.$get['lang'].'/menu.php' );
  elseif( file_exists( 'lang/'.FRAMEWORK_DEFAULTLANG.'/menu.php' ) ) include( 'lang/'.FRAMEWORK_DEFAULTLANG.'/menu.php' );
  ## $page.php (�bersetzung der betreffenden Seite)
  if( file_exists( 'lang/'.$get['lang'].'/'.$get['page'].'.php' ) ) include( 'lang/'.$get['lang'].'/'.$get['page'].'.php' );
  elseif( file_exists( 'lang/'.FRAMEWORK_DEFAULTLANG.'/'.$get['page'].'.php' ) ) include( 'lang/'.FRAMEWORK_DEFAULTLANG.'/'.$get['page'].'.php' );
?>