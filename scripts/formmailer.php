<?
  $ip=md5("$_SERVER[REMOTE_ADDR]:$_SERVER[SERVER_NAME]");
    
  function form_submit($refer, $hash) {
    include("validator.php");
    $method=$_SERVER[REQUEST_METHOD];
    $variablen=explode("/", $_SERVER[HTTP_REFERER]);
    foreach ($variablen as $variable) {
      $vteile=explode(",", $variable);
      $referer[$vteile[0]]=htmlentities(strip_tags($vteile[1]));
    }
    unset($variablen);
    unset($variable);
    unset($vteile);
    echo $referer[page]." ".$method;
    if (($method=="POST")&&($referer[page]==$refer)&&($hash==$ip)) return 1;
    else return 0;
  }
  
  function form_send($from, $to, $subject, $text) {
    if (val_email($from, 1)) $sender=$from;
    else $sender="webmaster@$_SERVER[SERVER_NAME]";
    mail("$to", "$subject", "$text", "FROM: $sender");
    return 1;
  }
?>