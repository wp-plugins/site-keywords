<?php
/*
Plugin Name: Site Keywords
Plugin URI: 
Description: Site Keywords allows you to create a list of Keywords and assign them to a link. If anyone types in a specific keyword into the searchbox it will take them directly to the page. If not it will take them to the WP search page and display results. Includes JQuery Auto-complete based on the list of keywords and the users entry.
Version: 0.7
Author: TJ Tyrrell
Author URI: http://tjtyrrel.com/



    Copyright 2010  TJ Tyrrell  (email : creative@tjtyrrell.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



define('SITEKEYWORDS_VERSION', '0.7');
$sk_db_version = "0.7";




/*
******* Plugin activation
*/
function sitekeywords_activate () {
	global $wpdb;
	global $sk_db_version;
	
	$table_name = $wpdb->prefix . "sitekeywords";
   
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE " . $table_name . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			keyword VARCHAR(55) NOT NULL,
			url VARCHAR(55) NOT NULL,
			expires int(1) NOT NULL,
			starts date NOT NULL,
			finish date NOT NULL,
			UNIQUE KEY id (id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	add_option("sk_db_version", $sk_db_version);
	
	//wp_schedule_event(mktime(date('H'),0,0,date('m'),date('d'),date('Y')), 'hourly', 'expirationdate_delete_'.$current_blog->blog_id);
}
register_activation_hook (__FILE__, 'sitekeywords_activate');






add_action('admin_init', 'site_keywords_admin_init');
    
function site_keywords_admin_init() {
	/* Register our stylesheet. */
	wp_register_style('siteKeywordsStylesheet', WP_PLUGIN_URL . '/site-keywords/css/site-keywords.css');
	wp_register_script('siteKeywordsJavascript', WP_PLUGIN_URL . '/site-keywords/js/site-keywords.js', array('jquery'));
}
    
function site_keywords_admin_styles() {
	/*
	* It will be called only on your plugin admin page, enqueue our stylesheet here
	*/
	wp_enqueue_style('siteKeywordsStylesheet');
	wp_enqueue_script('siteKeywordsJavascript');
}





add_action('admin_menu', 'sitekeywords_menu');

function sitekeywords_menu() {
	$page = add_posts_page( 'SiteKeywords Options', 'Site Keywords', 'administrator', 'sitekeywords', 'sk_options_page');
	
	//$options_page = add_options_page(__("Advanced Excerpt Options", $this->text_domain), __("Excerpt", $this->text_domain), 'manage_options', 'options-' . $this->name, array(&$this, 'page_options'));
        
        // Scripts
      //  add_action('admin_print_scripts-' . $options_page, array(&$this, 'page_script'));
	
	
	add_action('admin_print_styles-'. $page, 'site_keywords_admin_styles');
	
	
	
	
}


function update_keywords() {
	global $wpdb;
	$process = $_POST['process'];
	$id = $_POST['id'];
	$keyword = $_POST['keyword'];
	$url = $_POST['url'];
	$expires = $_POST['expires'];
	$starts = $_POST['start'];
	$finish = $_POST['finish'];
	
	//echo "Process: $process<br /> ID: $id <br /> Keyword: $keyword<br /> URL: $url<br /> Expires: $expires<br />$starts - $finish";
	$table_name = $wpdb->prefix . "sitekeywords";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {

	if($process == "delete_keyword") {
		$sql = "DELETE FROM " . $table_name . " WHERE id=". $id;
		$results = $wpdb->query( $sql );
	} else if ($process == "edit_keyword") {
		if($expires == "Yes") {
			$sql = "UPDATE " . $table_name . " SET keyword = '" . $keyword . "', url = '" . $url . "', expires = '1', starts = '" . $starts . "', finish = '" . $finish . "' WHERE id = ". $id;
			$results = $wpdb->query( $sql );
		} else {
			$sql = "UPDATE " . $table_name . " SET keyword = '" . $keyword . "', url = '" . $url . "', expires = '0', starts = '0000-00-00', finish = '0000-00-00' WHERE id = ". $id;
			$results = $wpdb->query( $sql );
		}
		
	} else if ($process == "add_keyword") {
		if($expires == "Yes") {
			$sql = "INSERT INTO " . $table_name . " VALUES('','" . $keyword . "', '" . $url . "', '1', '" . $starts . "', '" . $finish . "')";
			$results = $wpdb->query( $sql );
		} else {
			$sql = "INSERT INTO " . $table_name . " VALUES('','" . $keyword . "', '" . $url . "', '0', '0000-00-00', '0000-00-00')";
			$results = $wpdb->query( $sql );
		}
	}
}
	
	//$ellipsis = (get_magic_quotes_gpc() == 1) ? stripslashes($_POST[$this->name . '_ellipsis']) : $_POST[$this->name . '_ellipsis'];
	//$read_more = (get_magic_quotes_gpc() == 1) ? stripslashes($_POST[$this->name . '_read_more']) : $_POST[$this->name . '_read_more'];
	
	//$allowed_tags = array_unique((array) $_POST[$this->name . '_allowed_tags']);
		
}
        




function sk_options_page() {
	
	if ('POST' == $_SERVER['REQUEST_METHOD']) {
		check_admin_referer('sk_update_keywords');
		update_keywords();
	}
	
	
	
	echo '<div id="site-keywords" class="wrap">';
	echo '<h2>Keywords:</h2>';
	$ev_odd = "odd";
	global $wpdb;
	$table_sitekeywords = $wpdb->prefix . "sitekeywords";
	
	$get_keywords = $wpdb->get_results( "SELECT id, keyword, url, expires, starts, finish FROM $table_sitekeywords", "OBJECT" );
	?>
	<table>
	<thead>
	<tr>
	<th class="keyword">Keyword</th>
	<th class="url">Url</th>
	<th class="expires">Expires?</th>
	<th class="start">Start</th>
	<th class="finish">Finish</th>
	<th class="edit">Edit</th>
	<th class="delete">Delete</th>
	</thead>
	<tbody>
	<?php 
		foreach ($get_keywords as $keyword) {
			$id = $keyword->id;
			echo "<tr class='$ev_odd'>";
			if( $ev_odd == "even" ) {
				$ev_odd = "odd";
			} else {
				$ev_odd = "even";
			}
			$url_length = strlen($keyword->url);
			if( $url_length > 25 ) {
				$url = substr($keyword->url, 0, 25) . "...";
			} else {
				$url = $keyword->url;
			}
			echo "<td>$keyword->keyword</td>";
			echo "<td>$url</td>";
			if( $keyword->expires == 0 ) {
				$expire = "No";
				$start = "0000-00-00";
				$finish = "0000-00-00";
			} else {
				$expire = "Yes";
				$start = $keyword->starts;
				$finish = $keyword->finish;
			}
			echo "<td>$expire</td>";
			echo "<td>$start</td>";
			echo "<td>$finish</td>";
			echo "<td><a href='#' id='edit-$id' class='edit'><img src='" . WP_PLUGIN_URL . "/site-keywords/images/edit.png' /></a></td>";
			echo "<td><a href='#' id='delete-$id' class='delete $id'><img src='" . WP_PLUGIN_URL . "/site-keywords/images/trash.png' /></a></td>";
			echo "</tr>";
			echo "<tr id='edit-$id' class='edit_keyword'>";
			echo "<td colspan='7' class='edit_keyword'>";
			?>
			<form method="post" id="sitekeyword_save_options">
			
			<?php
        		if ( function_exists('wp_nonce_field') )
            	wp_nonce_field('sk_update_keywords'); ?>
			
				<input type="hidden" name="process" value="edit_keyword" />
				<input type="hidden" name="id" value="<?php echo $id; ?>" />
				<input type="hidden" class="expires_value" name="expires_value" value="<?php echo $expire; ?>" />
				<span class="label">Keyword:</span><input type="text" name="keyword" value="<?php echo $keyword->keyword; ?>" /><br />
				<span class="label">Url:</span><input type="text" name="url" value="<?php echo $keyword->url; ?>" /><br />
				<span class="label">Expires:</span><select class="edit_expires" name="expires">
					<option value="No"<?php if ($expire == 'No'){ echo ' selected="selected"';}?>>No</option>
					<option value="Yes"<?php if ($expire == 'Yes'){ echo ' selected="selected"';}?>>Yes</option>
					</select><br />
				<div id="if_expires">
					<span class="label">Starts (YYYY-MM-DD):</span><input type="text" name="start" value="<?php echo $start; ?>" /><br />
					<span class="label">Finishes (YYYY-MM-DD):</span><input type="text" name="finish" value="<?php echo $finish; ?>" /><br />
				</div>
				<input type="button" class="close" name="close" value="close" />
				<input type="submit" class="save" name="submit" value="save" />
			</form>
			</td></tr>
			<tr id="delete-<?php echo $id; ?>" class="delete_keyword">
			<td colspan="7" class="edit_keyword">
				<form method="post" id="sitekeyword_delete">
				<?php
        		if ( function_exists('wp_nonce_field') )
            	wp_nonce_field('sk_update_keywords'); ?>
					<input type="hidden" name="process" value="delete_keyword" />
					<input type="hidden" name="id" value="<?php echo $id; ?>" />
					<div class="delete">Are you sure you want to delete keyword <strong><?php echo $keyword->keyword; ?></strong>?
					<input type="button" class="no" value="No" />
					<input type="submit" class="yes" name="submit" value="Yes" /></div>
				</form>
			</td>
			</tr>
			
						
			<?php
		}
	?>
	</tbody>
	</table>
	
	<div id="add_keyword">
	<h2>Add New Keyword</h2>
		<form method="post" id="sitekeyword_add_keyword">
		<?php
    		if ( function_exists('wp_nonce_field') )
        	wp_nonce_field('sk_update_keywords'); ?>
			<input type="hidden" name="process" value="add_keyword" />
			<span class="add_label">Keyword:</span><input type="text" name="keyword" /><br />
			<span class="add_label">Url:</span><input type="text" name="url" value="" /><br />
			<span class="add_label">Expires:</span><select class="add_expires" name="expires" id="add_expires">
				<option>No</option>
				<option>Yes</option>
				</select><br />
			<div id="if_expires">
				<span class="add_label">Starts (YYYY-MM-DD):</span><input type="text" name="start" /><br />
				<span class="add_label">Finishes (YYYY-MM-DD):</span><input type="text" name="finish" /><br />
			</div>
			<input type="submit" class="add" name="submit" value="add" />
		</form>
	
	</div><!-- add_keyword -->	
	
	
	
	</div>

<?php }



add_action('wp_head', 'sk_intercept_search');

function sk_intercept_search() {

	if(is_search()) {
		$search_key = get_search_query();
	
		global $wpdb;
		$table_sitekeywords = $wpdb->prefix . "sitekeywords";
		
		$get_keyword = $wpdb->get_results( "SELECT id, keyword, url, expires, starts, finish FROM $table_sitekeywords WHERE keyword = '$search_key'", "ARRAY_A" );
		
		$test_keyword = count($get_keyword);
		
		if ($test_keyword > 0) {
			
			$keyword = $get_keyword[0]['keyword'];
			$url = $get_keyword[0]['url'];
			$expires = $get_keyword[0]['expires'];
			$starts = $get_keyword[0]['starts'];
			$finish = $get_keyword[0]['finish'];
			$today = date('Y-m-d');
			
			
			
			if( $expires != 1 ) {
				echo "<meta http-equiv='refresh' content='0;$url'>";
				flush();
				exit(0);
				
			} elseif (($starts <= $today) && ($today <= $finish)) {
				
				echo "<meta http-equiv='refresh' content='0;$url'>";
				flush();
				exit(0);
				
			}
			
			
			
			
			//header('Location: 
		}
	}
}







?>