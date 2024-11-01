<?php
/*
Plugin Name: WPSMF - Wordpress to SMF
Plugin URI: http://wordpress.org/extend/plugins/wpsmf/
Description: Automatically Export/Sync Wordpress to SMF 
Version:  0.4
Author: SchattenMann
*/
if (file_exists($_SERVER{'DOCUMENT_ROOT'}.get_option('smf_path').'/SSI.php')){
	require_once($_SERVER{'DOCUMENT_ROOT'}.get_option('smf_path')."/SSI.php");
	require_once($_SERVER{'DOCUMENT_ROOT'}.get_option('smf_path')."/Sources/Subs-Post.php");
	require_once($_SERVER{'DOCUMENT_ROOT'}.get_option('smf_path')."/Sources/RemoveTopic.php");
}

require_once ('posts.php');
require_once ('comments.php');
require_once ('widget.php');

add_action('admin_menu', 'myWPSMF_menu');

//SET DEFAULT NUM_POSTS

if(get_option('smf_num_posts') == ''){
	update_option('smf_num_posts', 50);
}

if(get_option('smf_autoexport_posts') == ''){
	update_option('smf_autoexport_posts', true);
}

if(get_option('smf_autoexport_comments') == ''){
	update_option('smf_autoexport_comments', true);
}

function myWPSMF_menu() {
	//add_submenu_page(parent, page_title, menu_title, capability required, file/handle, [function]); 
  add_menu_page('WP - SMF Sync', 'WPSMF', '9', 'myWPSMF_options', 'myWPSMF_options');
  add_submenu_page('myWPSMF_options','WP - SMF Sync', 'Options', '9', 'myWPSMF_options', 'myWPSMF_options');
  add_submenu_page('myWPSMF_options','WP - SMF Sync', 'Export Posts', '9', 'myWPSMF_posts_sync', 'myWPSMF_posts_sync');
  add_submenu_page('myWPSMF_options','WP - SMF Sync', 'Export Comments', '9', 'myWPSMF_comments_sync', 'myWPSMF_comments_sync');
}

function myWPSMF_options() {

  if($_POST['smf_hidden'] == 'Y') {
	  
	 //Form data sent  
	 $smf_path = $_POST['smf_path'];  
	 update_option('smf_path', $smf_path);  
	 
	 $smf_link_text = $_POST['smf_link_text'];  
	 update_option('smf_link_text', $smf_link_text); 
	 
	 $smf_charset = $_POST['smf_charset'];  
	 update_option('smf_charset', $smf_charset);  
	 
	 $smf_autoexport_posts = $_POST['smf_autoexport_posts']; 
	 
	 if($smf_autoexport_posts == 'true'){
		 update_option('smf_autoexport_posts', $smf_autoexport_posts);  
		 }else{
			update_option('smf_autoexport_posts', 'false');  
		 }
	
	 $smf_autoexport_comments = $_POST['smf_autoexport_comments'];  
	 if($smf_autoexport_comments == 'true'){
		  update_option('smf_autoexport_comments', $smf_autoexport_comments);  
		 }else{
			update_option('smf_autoexport_comments', 'false');  
		 }
	 
	 if(!$smf_autoexport_posts){
		update_option('smf_autoexport_comments', false);  
	 }
	 
	 $smf_users = $_POST['users'];
	 foreach ( $smf_users as $key => $value ){
		update_option('smf_'.$key, $value);
		$user_var = 'smf_'.$key;
		$$user_var = $value;
	 }
	 
	 $smf_cats = $_POST['cats'];
	 foreach ( $smf_cats as $key => $value ){
		update_option('smf_'.$key, $value);
		$cat_name = 'smf_'.$key;
		$$cat_name = $value;
	 }
	 
	 
?>  
	 <div class="updated"><p><strong>Options saved</strong></p></div>  
<?php  
 } else {
	 //Normal page display  
	 $smf_path = get_option('smf_path');  
	 $smf_link_text = get_option('smf_link_text');  
	 
	 if ($smf_link_text == ''){
		$smf_link_text = 'Follow on our <a>forum</a>';
	 }
	 
	 GLOBAL $wpdb;
	 
	 $users = $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users "));
				
		foreach ( $users as $userID ) :
		
			$user = get_userdata( $userID );
			
			$username = $user->user_login;
			
			$user_var = 'smf_'.$username;
			
			$$user_var = get_option('smf_'.$username); 
			
		endforeach;
	
	$categories = get_categories( 'type=post' );
				
		foreach ( $categories as $cat ) :
			
			$cat_name = $cat->cat_name;
			$cat_var = 'smf_'.$cat_name;
			$$cat_var = get_option('smf_'.$cat_name); 
			
		endforeach;
					
 }

?>
	<div class="wrap">
		<h2>SMF Settings</h2>

		<?php if (!file_exists($_SERVER{'DOCUMENT_ROOT'}.$smf_path . '/SSI.php')) {
		  echo '<div style="border:5px solid #aa0000; background:#feeeee; margin:0px;margin-top:10px; padding:4px;clear:both;"><p>';
		  echo '&nbsp;&nbsp;<b>smf not found!</b> Please set smf local path before creating or updating posts</p></div>';
		} ?>
		<?php 
		if (!get_option('smf_charset')) {
		  echo '<div style="border:5px solid #aa0000; background:#feeeee; margin:0px;margin-top:10px; padding:4px;clear:both;"><p>';
		  echo '&nbsp;&nbsp;<b>SMF CHARSET NOT SET!</b> Please set smf charset before creating or updating posts</p></div>';
		} ?>
		<br />
		
		<form name="WPSMF_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
			<table class="widefat">
			<thead>
				<tr>
					<th width="50%"><b>SMF Settings</b></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th>
						<table class="form-table">
							<tbody>
							<input type="hidden" name="smf_hidden" value="Y">
							<tr>
								<th>
									<label for="smf_path">SMF Path</label>
								</th>
								<td>
							 <input type="text" class="regular-text" name="smf_path" value="<?php echo $smf_path; ?>"> 
							 <span class="description">relative to <?php echo $_SERVER{'DOCUMENT_ROOT'} ?></span>
							 <td>
							</tr>
							 <tr>
								<th>
									<label for="smf_link_text">SMF Link Text</label>
								</th>
								<td>
									<input type="text" class="regular-text" name="smf_link_text" value="<?php echo $smf_link_text; ?>" size="20">
									<span class="description"> format: text &lt;a&gt;link&lt;/a&gt; text 
								</td>
							 </tr>
							 <tr>
								<th>
									<label for="smf_charset">SMF Charset</label>
								</th>
								<td>
									<select size="1" name="smf_charset" id="smf_charset" class="regular-text">
									<?php if(get_option('smf_charset') != ''){ ?>
										<option value="UTF8" <?php if (get_option('smf_charset') == 'UTF8') echo "selected='selected' "; ?> >UTF-8</option>; 
										<option value="Others" <?php if (get_option('smf_charset') == 'Others') echo "selected='selected' "; ?>>Others (ISO-8859-1)</option>; 
										<?php
										}else{
										?>
										<option value="UTF8" selected='selected'>UTF-8</option>; 
										<option value="Others" >Others (ISO-8859-1)</option>; 
										<?php
										}
										?>
									</select>
									<span class="description"> this option will assume that WP is using UTF-8
								</td>
							 </tr>
							 <tr>
								<th>
									<label for="smf_charset">Auto-Export Posts</label>
								</th>
								<td>
									<input type="checkbox" name="smf_autoexport_posts" value="true" <?php if(get_option('smf_autoexport_posts') == 'true'){echo ' checked="yes"';}?>>
									<span class="description"> select to automatically export post when published
								</td>
							 </tr>
							 <tr>
								<th>
									<label for="smf_charset">Auto-Export Comments</label>
								</th>
								<td>
									<input type="checkbox" name="smf_autoexport_comments" value="true" <?php if(get_option('smf_autoexport_comments') == 'true'){echo ' checked="yes"';}?>>
									<span class="description"> select to automatically export comments when approved
								</td>
							 </tr>
							</tbody>
						</table>
							 <br />
					</th>
				</tr>
			</tbody>
		</table>
		
		<br />
		
			<table class="widefat">
				<thead>
					<tr>
						<th width="50%"><b>Users Map</b></th>
						<th width="50%"><b>Categories Map</b></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th><b>Users Map</b></th>
						<th><b>Categories Map</b></th>
					</tr>
				</tfoot>
				<tbody>
					<tr>
						<td>
							<table class="widefat">
								<thead>
									<tr>
										<th width="50%"><b>WP Users</b></th>
										<th width="50%"><b>SMF User (ID ONLY)</b></th>
									</tr>
								</thead>
								<tbody>
									
									
						<?php

						GLOBAL $wpdb;
						
						$users = $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users "));
						
						foreach ( $users as $userID ) :
						
							$user = get_userdata( $userID );
							
							$username = $user->user_login;
							$user_var = 'smf_'.$username;
							
					?><tr>
						<td><b><?php echo $username ?></b></td>
						<td><input type="text" name="users[<?php echo $username ?>]" value="<?php echo $$user_var; ?>" size="20"></td></tr>
					<?php
						endforeach;
					?>
			
									
								</tbody>
							</table>
						</td>
						<td>
							<table class="widefat">
								<thead>
									<tr>
										<th width="50%"><b>WP Category</b></th>
										<th width="50%"><b>SMF Category</b></th>
									</tr>
								</thead>
								<tbody>
			<?php
				$args = array(
					'type' => 'post',
					'hide_empty' => false
				);
				
				$categories = get_categories( $args );
				
				foreach ( $categories as $cat ) :
					
					$cat_name = $cat->cat_name;
					$cat_var = 'smf_'.$cat_name;
					
			?>
				<tr>
					<td><b><?php echo $cat_name ?></b></td>
					<td><input type="text" name="cats[<?php echo $cat_name ?>]" value="<?php echo $$cat_var; ?>" size="20"></td>
				</tr>
			<?php
				endforeach;
			?>
									
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>

			<p class="submit">
				<input type="submit" name="Submit" value="Update Options" />
			</p>
		</form>
	</div>
<?php
}

function myWPSMF_link_to_smf ($post_id) {

    if (get_post_meta($post_id, 'smf_topic_id', true) && get_post_meta($post_id, 'smf_msg_id', true)) {
		//exist, echo link
		$topic = get_post_meta($post_id, 'smf_topic_id', true);
		$smf_path = get_option('smf_path');
		$smf_link_text = get_option('smf_link_text');
		
		$domain = parse_url($_SERVER['HTTP_HOST']);
		
		$smf_link = "<a href=\"http://".$domain['path']."$smf_path/index.php?topic=$topic.0\">";
		
		$smf_link_text = str_replace ('<a>',$smf_link,$smf_link_text);

        //echo "<a href=\"".get_bloginfo('wpurl')."/$smf_path/index.php?topic=$topic.0\">$smf_link_text</a>";
		
		echo ($smf_link_text);
		
    }
    return true;
}

function myWPSMF_recent_posts_styles() {

    /* The xhtml header code needed for gallery to work: */
	$mySponsorsscript = "
		<style>
			#sidebar ul.smf_recent_posts li{
				float:none;
				width: auto;
			}
		</style>
	";
	echo($mySponsorsscript);
}

add_action('wp_head', 'myWPSMF_recent_posts_styles');

function myWPSMF_table_header ($i,$num_posts){
	
	if(isset($_POST['filter-1'])){
		$num_posts = $_POST['num_posts-1'];
		$category = $_POST['categories-1'];
		$date = $_POST['date-1'];
		$status = $_POST['see-status-1'];
	}elseif(isset($_POST['filter-2'])){
		$num_posts = $_POST['num_posts-2'];
		$category = $_POST['categories-2'];
		$date = $_POST['date-2'];
		$status = $_POST['see-status-2'];
	}
			

	
?>	
	<script>
	function submit_form(field){
		
		var theForm = document.getElementById("WPSMF_form"); 
		var newOption = document.createElement("input");  
		newOption.name = "action";
		newOption.type = "hidden";
		newOption.value = document.getElementById(field).value;
		
		theForm.appendChild(newOption);  
		
		theForm.submit();
	}
	</script>
	
		<form class="submit" name="myWPSMF_table_header_form" id="myWPSMF_table_header_form-<?php echo $i?>" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>&p=1">
		<select size="1" name="action-<?php echo $i?>" id="action-<?php echo $i?>" style="width:130px;">
			<option value="-1" selected='selected' >Bulk Actions</option>; 
			<option value="Export" >Export</option>; 
			<option value="Unlink" >Unlink</option>; 
			<option value="Remove" >Remove</option>; 
		</select>
		<input type="button" id="submit-<?php echo $i?>" name="submit-<?php echo $i?>" onClick="submit_form('action-<?php echo $i?>');" value="Apply" />
		<select size="1" name="see-status-<?php echo $i?>" id="see-status-<?php echo $i?>" style="width:130px;">
			<option value="notexp" selected='selected' >See Not Exported</option>
			<option value="exp" <?php if ($status == 'exp'){echo ' selected="selected" ';}?>>See Exported</option>
			<option value="all" <?php if ($status == 'all'){echo ' selected="selected" ';}?>>See All</option>
		</select>
		<select size="1" name="date-<?php echo $i?>" id="date-<?php echo $i?>" style="width:130px;">
			<option value="-1" selected='selected' >See all dates</option>
			<?php
				
				$args = array(
					'type' => 'monthly',
					'echo' => false,
					'show_post_count' => false
				);
				
				GLOBAL $wpdb;
				
				$years = $wpdb->get_col("SELECT DISTINCT YEAR(post_date) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date ASC");
				foreach($years as $year) :
					$months = $wpdb->get_results("SELECT DISTINCT MONTH(post_date) as month, MONTHNAME(post_date) as monthname FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' AND YEAR(post_date) = '$year' ORDER BY post_date ASC");
					foreach($months as $month) :
						//echo '<option value="'.$year.$month->month.'">'.$year.' '.$month->monthname.'</option>';
						if (($year.$month->month) == $date){
							echo '<option value="'.$year.$month->month.'" selected="selected">'.$year.' '.$month->monthname.'</option>; ';
						}else{
							echo '<option value="'.$year.$month->month.'">'.$year.' '.$month->monthname.'</option>';
						}
					endforeach;
				endforeach;
							?>
		</select>
		<select size="1" name="categories-<?php echo $i?>" id="categories-<?php echo $i?>" style="width:130px;">
			<option value="-1" selected='selected' >See all categories</option>; 
			<?php
				
				$args = array(
					'type' => 'post',
					'hide_empty' => true
				);
			
				$categories = get_categories($args);
				
				foreach ( $categories as $cat ) :
					$id_board = get_option('smf_'.$cat->cat_name );
					if($id_board){
						if ($cat->cat_ID == $category){
							echo '<option value="'.$cat->cat_ID.'" selected="selected">'.$cat->cat_name.'</option>; ';
						}else{
							echo '<option value="'.$cat->cat_ID.'">'.$cat->cat_name.'</option>; ';
						}
					}
				endforeach;
			?>
		</select>
		<span class="description">Posts to show:</span>
		<input type="text" class="regular-text" id="num_posts-<?php echo $i?>" name="num_posts-<?php echo $i?>" value="<?php if(isset($num_posts)){echo $num_posts;} ?>" style="width:50px;"> 
			<input type="submit" id="filter-<?php echo $i?>" name="filter-<?php echo $i?>" value="Filter" />
	</form>
	<?php
}

?>
