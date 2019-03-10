<?php
function epp_connect( $registry ) {
  # Lade Einstellungen für gewünschte Registry
  switch( $registry ) {
    # NIC.LV
    case 'lv':
      $epp_hostname = 'ssl://epp-sandbox.nic.lv';
      $epp_port = 700;
      $epp_username = 'master-sys';
      $epp_password = 'Piequo7eThuaph0';
      $epp_extensions = '<extURI>http://www.nic.lv/epp/schema/lvdomain-ext-1.0</extURI>
                <extURI>http://www.nic.lv/epp/schema/lvcontact-ext-1.0</extURI>';
      break;
  } # END switch registry
  # Stelle Verbindung her
    $epp = fsockopen( $epp_hostname, $epp_port );
} # END function epp_connect

function epp_command( $command, $arguments ) {
  global $epp;
  global $epp_extensions;
  # XML-String aufbauen
  $string = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <'.$command.'>';
  # Argumente ergänzen
  foreach( $arguments as $name => $value ) {
    $string .= '
      <'.$name.'>'.$value.'</'.$name.'>';
  }
  $string .= '
      <options>
        <version>1.0</version>
        <lang>en</lang>
      </options>
      <svcs>
        <objURI>urn:ietf:params:xml:ns:domain-1.0</objURI>
        <objURI>urn:ietf:params:xml:ns:contact-1.0</objURI>
        <svcExtension>
          '.$epp_extensions.'
        </svcExtension>
      </svcs>
    </'.$command.'>
    <clTRID>'..'</clTRID>
  </command>
</epp>';


  

} # END function epp_command