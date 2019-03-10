<?
  require_once( 'config.php' );
  if( !MWAPI_BOTNAME ) define( MWAPI_BOTNAME, 'MWAPIlib in PHP, by Manuel Schneider' );
  
  function sendcmd( $get, $post = false ) {
    if( is_array( $get ) ) {
      $action = '?';
      foreach( $get as $name => $value ) {
        $action .= $name.'='.$value.'&';
      }
      $action .= 'format=php';
    } elseif( $get ) {
      $action = '?action='.$get.'&format=php';
    } else {
      $action = '?format=php';
    }
  
    if( MWAPI_WMMODE ) {
      if( is_array( $post ) ) {
        $poststr = false;
        foreach( $post as $name => $value ) {
          if( $poststr ) $poststr .= '&';
          $poststr .= $name.'='.$value;
        }
      }
    }
    $poststr = $post;
    
    # bereite Cookie-Datei vor
    $file = realpath( 'cookies.txt' );
    # Setze Parameter zum HTTP-Aufruf
    $c = curl_init( URL.$action );
    curl_setopt( $c, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $c, CURLOPT_ENCODING, 'UTF-8' );
    curl_setopt( $c, CURLOPT_USERAGENT, MWAPI_BOTNAME );
    curl_setopt( $c, CURLOPT_POST, true );
    curl_setopt( $c, CURLOPT_POSTFIELDS, $poststr );
    curl_setopt( $c, CURLOPT_CONNECTTIMEOUT, 10 );
    curl_setopt( $c, CURLOPT_COOKIEJAR, $file );
    curl_setopt( $c, CURLOPT_COOKIEFILE, $file );
#    curl_setopt( $c, CURLOPT_VERBOSE, true );
#    curl_setopt( $c, CURLOPT_HEADER, true );
#    curl_setopt( $c, CURLOPT_HEADER_OUT, true );
#    echo URL.$action.' ('.$poststr.')'."\n";
    $r = curl_exec( $c );
#    var_dump( $r );
    curl_close( $c );
    # Führe Aufruf durch
    return unserialize( $r );
  }
  
  # Standard ist leer, dh. false. Damit startet der Login-Vorgang.
  function login( $r = false ) {
    if( !$r ) {
      echo 'LOGIN: fetch token'."\n";
      return login( sendcmd( 'login', array( 'lgname' => MWAPI_USERNAME, 'lgpassword' => MWAPI_PASSWORD ) ) );
    } else {
      switch( $r['login']['result'] ) {
        case 'NeedToken':
          echo 'LOGIN: NeedToken - confirming with token'."\n";
          return login( sendcmd( 'login', array( 'lgname' => MWAPI_USERNAME, 'lgpassword' => MWAPI_PASSWORD, 'lgtoken' => $r['login']['token'] ) ) );
          break;
        case 'Success':
          echo 'LOGIN: Success'."\n";
          return $r;
          break;
        default:
          echo 'LOGIN: Error '.$r['login']['result']."\n";
          return false;
      }
    }
  }
  
  function logout() {
    echo 'LOGOUT:'."\n";
    return sendcmd( 'logout' );
  }
  
  function get_article( $article = 'Main Page' ) {
    $r = sendcmd( '', array( 'action' => 'query', 'titles' => $article, 'prop' => 'revisions', 'rvprop' => 'content' ) );
    if( $r['query']['pages'][-1] ) {
      echo 'READ: Error - article does not exist'."\n";
      return false;
    } elseif( $r['query']['pages'] ) {
      $page = array_keys( $r['query']['pages'] );
      echo 'READ: Success'."\n";
      return $r['query']['pages'][$page[0]]['revisions'][0]['*'];
    }
    return $r;
  }
  
  function get_info( $article = 'Main Page' ) {
    $r = sendcmd( '', array( 'action' => 'query', 'titles' => $article, 'prop' => 'info' ) );
    if( $r['query']['pages'][-1] ) {
      echo 'INFO: Error - article does not exist'."\n";
      return false;
    } elseif( $r['query']['pages'] ) {
      $page = array_keys( $r['query']['pages'] );
      echo 'INFO: Success'."\n";
      return $r['query']['pages'][$page[0]];
    }
    return $r;
  }
  
  function put_article( $article = 'Main Page', $text = '', $summary = '', $r = false ) {
    if( !$r ) {
      echo 'EDIT: fetch token'."\n";
      return put_article( $article, $text, $summary, sendcmd( 'query', array( 'titles' => $article, 'prop' => 'info|revisions', 'intoken' => 'edit' ) ) );
    } elseif( $r['warnings'] ) {
      echo 'EDIT: warnings '.$r['warnings']['info']['*']."\n";
      return false;
    } elseif( $r['error'] ) {
      echo 'EDIT: error '.$r['error']['info']."\n";
      return false;
    } elseif( $r['query']['pages'] ) {
      $page = array_keys( $r['query']['pages'] );
      echo 'EDIT: '.$r['query']['pages'][$page[0]]['edittoken']."\n";
      return put_article( $article, $text, $summary, sendcmd( 'edit', array( 'title' => $article, 'text' => $text, 'summary' => $summary, 'token' => $r['query']['pages'][$page[0]]['edittoken'] ) ) );
    } elseif( $r['edit']['result'] == 'Failure' ) {
      echo 'EDIT: Error '.$r['edit']['result']."\n";
      return false;
    } elseif( $r['edit']['result'] == 'Success' ) {
      echo 'EDIT: Success'."\n";
      return $r;
    }
  }

  function rem_article( $article = 'Main Page', $reason = '', $r = false ) {
    if( !$r ) {
      echo 'DELETE: fetch token'."\n";
      return rem_article( $article, $reason, sendcmd( 'query', array( 'titles' => $article, 'prop' => 'info', 'intoken' => 'delete' ) ) );
    } elseif( $r['warnings'] ) {
      echo 'DELETE: warnings '.$r['warnings']['info']['*']."\n";
      return false;
    } elseif( $r['error'] ) {
      echo 'DELETE: error '.$r['error']['info']."\n";
      return false;
    } elseif( $r['query']['pages'] ) {
      $page = array_keys( $r['query']['pages'] );
      echo 'DELETE: '.$r['query']['pages'][$page[0]]['deletetoken']."\n";
      return rem_article( $article, $reason, sendcmd( 'delete', array( 'title' => $article, 'reason' => $reason, 'token' => $r['query']['pages'][$page[0]]['deletetoken'] ) ) );
    } elseif( $r['delete']['result'] == 'Failure' ) {
      echo 'DELETE: Error '.$r['edit']['result']."\n";
      return false;
    } elseif( $r['delete']['result'] == 'Success' ) {
      echo 'DELETE: Success'."\n";
      return $r;
    }
  }
  
  function check_api() {
    sendcmd( false, false );
  }
?>