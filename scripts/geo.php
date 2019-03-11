<?php
	function ch1903towgs84( $x, $y = false ) {
		# function accepts parameters either as array( x, y ) or a x, y variable pair
		if( is_array( $x ) ) {
			$y = $x[1];
			$x = $x[0];
		}
		
		$x = ( $x - 600000 ) / 1000000;
    $y = ( $y - 200000 ) / 1000000;
 
    $lon 	= 2.6779094
    			+ 4.728982	* $x
					+ 0.791484	* $x						* $y
					+ 0.1306		* $x						* pow( $y, 2 )
					- 0.0436		* pow( $x, 3 );
    
		$lat	= 16.9023892
					+ 3.238272									* $y
					- 0.270978	* pow( $x, 2 )
					- 0.002528									* pow( $y, 2 )
					- 0.0447		* pow( $x, 2 )	* $y
					- 0.0140										* pow( $y, 3 );
		
		$lon = $lon * 100 / 36;
		$lat = $lat * 100 / 36;

		return( array( $lat, $lon ) );
	} # function ch1903towgs84

	function osmmap( $lat, $lon = false, $zoom = 15 ) {
		# function accepts parameters either as array( lat, lon ) or a lat, lon variable pair
		if( is_array( $lat ) ) {
			$lon = $lat[1];
			$lat = $lat[0];
		}
		
		$offset = 1 / $zoom;
		$bb1 = ( $lon - $offset ).'%2C'.( $lat + $offset );
		$bb2 = ( $lon + $offset ).'%2C'.( $lat - $offset );
		
		return( 'https://www.openstreetmap.org/export/embed.html?bbox='.$bb1.'%2C'.$bb2.'&marker='.$lat.'%2C'.$lon );
	} # function osmmap

?>