<?php
/*
 * admin_acl.php
 * September 29, 2006
 * author: Kristen G. Thorson
 *
 *
 * Released under the GNU General Public License
 *
 */

  class admin_acl {

  	var $user_name, $table_name, $selected_options, $all_options;

    function admin_acl( $user_name ) {
    	//check that the option is not null or emtpy
    	if( tep_not_null( $user_name ) ) {
    		$this->user_name = tep_db_input( $user_name );
			} else {
				return false;
			}
			$this->selected_options = array();
			$this->all_options = array();
		}

		function save( $selected_options = array() ) {
			tep_db_query( $sql = 'DELETE FROM administrators_acl WHERE user_name="'.$this->user_name.'"' );
			
			if( is_array( $selected_options ) && count( $selected_options ) > 0 ) {
				foreach( $selected_options as $ids ) {
					$pieces = explode(",", $ids);
					tep_db_query( $sql = 'INSERT INTO administrators_acl VALUES ( "'.$this->user_name.'", "'.$pieces[0].'", "'.$pieces[1].'", "'.$pieces[2].'" )' );
				}
			} else return false;
		}
		
		function tep_sort_admin_boxes($a, $b) {
		  return strcasecmp($a['heading'], $b['heading']);
		}

		function tep_sort_admin_boxes_links($a, $b) {
		  return strcasecmp($a['title'], $b['title']);
		}

  }
?>
