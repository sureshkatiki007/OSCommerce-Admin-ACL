<?php
/*
 * admin_acl.php
 * September 26, 2006
 * author: Kristen G. Thorson
 * ot_discount_coupon_codes version 3.0
 *
 *
 * Released under the GNU General Public License
 */

  /**********************************************/

	require( 'includes/application_top.php' );
	require( DIR_WS_CLASSES.'admin_acl.php' );
	
	if( tep_not_null( $error ) ) {
		$messageStack->add( $error, 'error' );
	}
	if( tep_not_null( $message ) ) {
		$messageStack->add( $message, 'success' );
	}
	
	$action = ( isset( $HTTP_POST_VARS['action'] ) ? $HTTP_POST_VARS['action'] : '' );
	
	if( isset( $HTTP_GET_VARS['aID'] ) && $HTTP_GET_VARS['aID'] != '' )
		$user_name = tep_db_input( $HTTP_GET_VARS['aID'] );
	else
		tep_redirect( tep_href_link( 'admin_acl.php', 'error='.ERROR_ADMIN_ACL_SAVE ) );

	if( !( $admin_acl = new admin_acl( $user_name ) ) ) tep_redirect( tep_href_link( 'admin_acl.php', 'aID='.$user_name.'&error='.ERROR_ADMIN_ACL_INVALID ) );

	if( tep_not_null( $action ) )
	{
		switch( $action ) {
			case 'Save':
su				$admin_acl->save( $HTTP_POST_VARS['selected_options'] );
				tep_redirect( tep_href_link( 'admin_acl.php', 'aID='.$user_name.'&message='.MESSAGE_ADMIN_ACL_SAVED ) );
				break;
			case 'Cancel':
				break;
		}
		tep_redirect( tep_href_link( 'admin_acl.php', 'aID='.$user_name ) );
	}
	else
	{

		//	get_selected_options
		$selected_options = '';
		$selected_ids = array();
		$sql_selected = 'SELECT * FROM administrators_acl WHERE user_name="'.$user_name.'" group by menu_heading,page_name ';
 		$result = tep_db_query( $sql_selected );
		$selected_urls = array();
  		if( tep_db_num_rows( $result ) > 0 )
		{
			$menu_heading = '';
			$h = 0;
			while( $row = tep_db_fetch_array( $result ) )
			{
				if($menu_heading != $row['menu_heading'])
				{
					$menu_heading = $row['menu_heading'];
					if($h) $selected_options .= '</optgroup>';
					$selected_options .= '<optgroup label="'.$row['menu_heading'].'">';
				}
					
				$selected_ids[] = $row['blocked_url'];
				$selected_options .= '<option value="'.$row['menu_heading'].','.$row['page_name'].','.$row['blocked_url'].'">'.$row['page_name'].'</option>';
			}
			if($h) $selected_options .= '</optgroup>';
		}
		
			
		
		//	get_all_options
		$cl_box_groups = array();
		$menu_options = '';

		if ($dir = @dir(DIR_FS_ADMIN . 'includes/boxes'))
		{
			$files = array();

			while ($file = $dir->read()) {
				if (!is_dir($dir->path . '/' . $file)) {
					if (substr($file, strrpos($file, '.')) == '.php') {
						$files[] = $file;
					}
				}
			}
		
			$dir->close();

			natcasesort($files);

			foreach ( $files as $file ) {
				if ( file_exists(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/boxes/' . $file) ) {
					include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/boxes/' . $file);
				}
				include($dir->path . '/' . $file);
			}
		}

		usort($cl_box_groups, array("admin_acl", "tep_sort_admin_boxes"));

		foreach ( $cl_box_groups as &$group ) {
		  usort($group['apps'], array("admin_acl", "tep_sort_admin_boxes_links"));
		}
		
		foreach ($cl_box_groups as $groups)
		{
			$menu_options .= ' <optgroup label="'.$groups['heading'].'">';
			
			foreach ($groups['apps'] as $app) {
				if(!(in_array($app['link'], $selected_ids)) )
				$menu_options .= '<option value="'.$groups['heading'].','.$app['title'].','.$app['link'].'">'.$app['title'].'</option>';
			}
			
			$menu_options .= '</optgroup>';
		}
			
	}
	
	require(DIR_WS_INCLUDES . 'template_top.php');
	
?>

    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="5" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo sprintf( HEADING_TITLE, $user_name ); ?></td>
            <td class="specialPrice" align="right"></td>
          </tr>
          <tr>
          	<td colspan="2">
			<?php
				echo tep_draw_form( 'choose'.$admin['username'], 'admin_acl.php', 'aID='.$user_name, 'post', 'onsubmit="form_submission( document.getElementById(\'selected_optons\') )"' ).'
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td align="center" class="main">'.HEADING_AVAILABLE.'</td>
						<td align="center">&nbsp;</td>
						<td align="center" class="main">'.HEADING_SELECTED.'</td>
					</tr>
					<tr>
						<td rowspan="5" align="center">
							<select name="available_options[]" size="20" multiple="" style="width: 300px" id="available_optons">
							'.$menu_options.'
							</select>
						</td>
						<td align="center"><input name="choose_all" type="button" id="choose_all" value="Choose All &gt;" onclick="selectAll( document.getElementById(\'selected_optons\'), document.getElementById(\'available_optons\') )"></td>
						<td rowspan="5" align="center">
							<select name="selected_options[]" size="20" multiple="" style="width: 300px" id="selected_optons">
							'.$selected_options.'
							</select>
						</td>
					</tr>
					<tr>
						<td align="center"><input name="add" type="button" id="add" value="&gt; &gt;" onclick="updateSelect( document.getElementById(\'selected_optons\'), document.getElementById(\'available_optons\') )"></td>
					</tr>
					<tr>
						<td align="center"><input name="subtract" type="button" id="subtract" value="&lt; &lt;" onclick="updateSelect( document.getElementById(\'available_optons\'), document.getElementById(\'selected_optons\') )"></td>
					</tr>
					<tr>
						<td align="center"><input name="remove_all" type="button" id="remove_all" value="&lt; Remove All" onclick="selectAll( document.getElementById(\'available_optons\'), document.getElementById(\'selected_optons\') )"></td>
					</tr>
					<tr>
						<td align="center"><input name="action" type="submit" id="action" value="Save"> <input name="action" type="submit" id="action" value="Cancel"></td>
					</tr>
				</table>
				</form>';
				
				echo tep_draw_button('Back', 'document', tep_href_link(FILENAME_ADMINISTRATORS));
			?>
			</td>
          </tr>
        </table></td>
      </tr>
    </table>
	
<script language="javascript" type="text/javascript"><!--

	function updateSelect( to_select, from_select ) {
		 for( var i = 0; i < from_select.options.length; i++ ) {
			  if( from_select.options[i].selected ) {
				var newOption = new Option( from_select.options[i].text, from_select.options[i].value )
				to_select.options[ to_select.options.length ] = newOption;
			  }
		 }
		 deleteOptions( from_select );
	}

	function deleteOptions( delete_select ) {
	  for( var i = 0; i < delete_select.options.length; i++ ) {
		if( delete_select.options[i].selected ) {
		  delete_select.options[i] = null;
		  i=-1;
		}
	  }
	}

	function selectAll( to_select, from_select ) {
		for( var i=0; i < from_select.options.length; i++ ) {
		  from_select.options[i].selected = true;
		}
		updateSelect( to_select, from_select );
	}

	function form_submission( to_select ) {
	  for( var i=0; i < to_select.options.length; i++ ) {
		  to_select.options[i].selected = true;
		}
	}

//--></script>

<?php 
require(DIR_WS_INCLUDES . 'template_bottom.php');
require(DIR_WS_INCLUDES . 'application_bottom.php'); 
?>
