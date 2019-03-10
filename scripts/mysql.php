<?php
  function sql_connect() {
    if( !$db = mysqli_connect( FRAMEWORK_SQLHOST, FRAMEWORK_SQLUSER, FRAMEWORK_SQLPW ) ) {
      echo '<p class="error">Keine Verbindung zum Datenbankserver.</p>'."\n";
      exit;
    } elseif ( !mysqli_select_db( $db, FRAMEWORK_SQLDB ) ) {
      echo '<p class="error">Konnte Datenbank nicht auswählen.</p>'."\n";
      exit;
    }
    return $db;
  }

  function sql( $sql ) {
    global $db;
    if( !$r = mysqli_query( $db, $sql ) ) {
      echo '<p class="error">Datenbankfehler: '.mysqli_error( $db ).'<br/>'.$sql.'</p>'."\n";
      exit;
    }
    return $r;
  }

  function sql_disconnect() {
    global $db;
    mysqli_close( $db );
  }
  
  function encrypt( $text ) {
    return trim( 
      base64_encode( 
        mcrypt_encrypt( 
          MCRYPT_RIJNDAEL_256, FRAMEWORK_SALT, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(
            mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB ), MCRYPT_RAND
          )
        )
      )
    );
  }
                  
  function decrypt( $text ) {
    return trim(
      mcrypt_decrypt(
        MCRYPT_RIJNDAEL_256, FRAMEWORK_SALT, base64_decode( $text ), MCRYPT_MODE_ECB, mcrypt_create_iv(
          mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB ), MCRYPT_RAND
        )
      )
    );
  }
?>