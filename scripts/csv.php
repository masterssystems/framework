<?php
	function getcsvline( $fh, $delim = ',', $encl = '"' ) {

		$line = '';					# text read from file
		$in = false;				# flag if we are in an enclosure
		$column = array();	# result
		$temp = '';					# temp variable to hold data of one column until we found the end
		$eol = false;

		# continue as long as we a) haven't read anything yet or b) are inside enclosure
		while( ( !$eol ) || ( $in ) ) {
			if( $line = fgets( $fh ) ) {
				# iterate over every character in string
				$line = str_split( $line );
				$i = 0;
				foreach( $line as $char ) {

					switch( $char ) {

						# ignore carriage return
						case "\r":
							break;

						# newline
						case "\n":
							# protected, handle like regular character
							if( $in ) $temp .= $char;
							# end of line
							else {
								$column[] = $temp;
								$temp = '';
								$eol = true;
							}
							break;

						# enclosure
						case $encl:
							# swap flag if enclosure come as first or last character of a column - otherwise handle like regular character
							if( !$in && ( strlen( $temp ) == 0 ) ) $in = true;
							elseif( $in && ( ( $line[$i + 1] == $delim ) || ( $line[$i + 1] == "\n" ) ) ) $in = false;
							else $temp .= $char;
							break;

						# delimiter - new column
						case $delim:
							# protected, handle like regular character
							if( $in ) $temp .= $char;
							# end of column
							else {
								$column[] = $temp;
								$temp = '';
							}
							break;

						default:
							$temp .= $char;

					} # switch char
					$i++;
				} # foreach line
			} else {
				$column[] = $temp;
				$eol = true;
			} # if fgets line
		} # while fgets line
		
		# clean up fields
		foreach( $column as $id => $data ) {
			$column[$id] = trim( $data );
		}

		if( count( $column ) > 1 ) return( $column );
		else return( false );
	}
	
	function seekcsv( $fh, $skip ) {
		rewind( $fh );
		for( $i = 0; $i < $skip; $i++ ) {
			fgets( $fh );
		}
	}
?>