<?
  if( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
    $session['username'] = $_SERVER['PHP_AUTH_USER'];
    $session['login'] = true;
  } else {
    $session['login'] = false;
  }
?>