<?php

	function search_box( $name, $query ) {

		echo '<p><input id="'.$name.'" onkeyup="ajaxsearch_'.$name.'( this.value )" size="20" autofocus="autofocus"/></p>';
		echo '<div class="ajaxsearch" id="ajaxsearch_'.$name.'"></div>';
		echo '<script type="text/javascript">
			document.getElementById("'.$name.'").focus();
		
			function ajaxsearch_'.$name.'( search ) {
				if( search.length < 2 ) {
					document.getElementById( "ajaxsearch_'.$name.'" ).innerHTML = "";
					document.getElementById( "ajaxsearch_'.$name.'" ).style.display = "none";
					return;
				}
				if( window.XMLHttpRequest ) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject( "Microsoft.XMLHTTP" );
				}
				xmlhttp.onreadystatechange = function() {
					if( ( this.readyState == 4 ) && ( this.status == 200 ) ) {
						if( this.responseText == "" ) {
							document.getElementById( "ajaxsearch_'.$name.'" ).innerHTML = "";
							document.getElementById( "ajaxsearch_'.$name.'" ).style.display = "none";
						} else {
							document.getElementById( "ajaxsearch_'.$name.'" ).innerHTML = this.responseText;
							document.getElementById( "ajaxsearch_'.$name.'" ).style.display = "block";
						}
					}
				}
				xmlhttp.open( "GET", "'.FRAMEWORK_ROOT.'/raw,Search/query,'.$query.'/search," + search, true );
				xmlhttp.send();
				}

			</script>';

	} # function search_box

	function search_query( $search, $col_id, $col_string, $from, $where, $order ) {
		if( isset( $search ) ) {
			# do search by multiple parameters
			$term = explode( ' ', $search );
			$having = array();
			foreach( $term as $word ) {
				# ignore words smaller than 3 characters
				if( strlen( $word ) > 1 ) {
					$having[] = 'string LIKE "%'.$word.'%"'."\n";
				}
			}
			# build having clause
			if( count( $having ) > 0 ) {
				# build HAVING clause
				$having = implode( 'AND ', $having );
				
				# build sql query
				$sql = '
					SELECT 
						'.$col_id.',
						'.$col_string.' AS string
					FROM '.$from.' ';
				# where if set
				if( $where ) {
					# check if FROM already includes a WHERE clause
					if( stripos( $from, 'where' ) ) $sql .= 'AND '.$where.' ';
					else $sql .= 'WHERE '.$where.' ';
				} # if where
				# having
				$sql .= 'HAVING '.$having.' ';
				# order if set
				if( $order ) $sql .= 'ORDER BY '.$order.' ';
				
				# run sql
				$r = sql( $sql.';' );
				
				# prepare storage
				$list = array();
				
				# iterate over search results
				while( list( $id, $string ) = mysqli_fetch_row( $r ) ) {
					$list[$id] = $string;
					# mark search results in string
					foreach( $term as $word ) {
						# ignore words smaller than 3 characters
						if( strlen( $word ) > 1 ) $list[$id] = preg_replace( '/'.$word.'/i', '<b>${0}</b>', $list[$id] );
						#else $list[$id] = $string;
					} # foreach term
				} # while fetch result
				
				return( $list );
			} # if having
		} # if search
	} # function search_query
?>