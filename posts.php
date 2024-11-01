<?php
function myWPSMF_post_to_smf ($post_id) {

	//FROM NOW ON THERES NO WAY YOU CAN STOP ME!!!
	ignore_user_abort(true);
	set_time_limit(0);
	
	//GET THE POST
	$queried_post = get_post($post_id);
	
	$sucess = false;
			
	//IS POST IS PUBLISH LETS GO...
	if ($queried_post -> post_status == 'publish'){
		
		//WHAT ABOUT CHARSET?
		if(get_option('smf_charset') == 'UTF8'){
			$subject = $queried_post -> post_title;
		}else{
			$subject = utf8_encode($queried_post -> post_title);
		}
		
		if(get_option('smf_charset') == 'UTF8'){
			$body = $queried_post -> post_content;
		}else{
			$body = utf8_encode($queried_post -> post_content);
		}
		
		$body = apply_filters('the_content', $body);
        $body = str_replace(']]>', ']]&gt;', $body);
		
		$body = '[html]'.$body.'[/html]';
		
		//GET THE POSTER INFO
		$user_info = get_userdata($queried_post -> post_author);
		
		$poster = get_option('smf_'.$user_info -> user_login);
		
		//GET THE CATEGORY
		$post_cat = get_the_category($queried_post -> ID);
		
		//GET THE BOARD ID
		$id_board = get_option('smf_'.$post_cat[0]->cat_name );
		
		//GET THE IP
		$ip = $_SERVER['REMOTE_ADDR'];
		
		//IM NO FOOL!
		if ($id_board != '' && $id_board != 0) {
		
			$msgOptions = array(
			   'id' =>  0,
			   'subject' => $subject,
			   'body' => $body,
			   'smileys_enabled' => true,
			);
			
			$topicOptions = array(
			   'id' => 0,
			   'board' => $id_board,
			   'mark_as_read' => true,
			);
			
			$posterOptions = array(
			   'id' => $poster,
			   'ip' => $ip,
			   'update_post_count' => 1
			);
		
			//IF USER IS A GUEST LETS USE HIS INFO ON WP ALSO ON SMF
			if ($poster == '' || $poster == 0) {
				$posterOptions['name'] = $queried_comment -> comment_author;
				$posterOptions['email'] = $queried_comment -> comment_author_email;
			}
			
			//AM I MODIFYING A POST
			if (get_post_meta($post_id, 'smf_topic_id', true) && get_post_meta($post_id, 'smf_msg_id', true)) {
				
				//exist, update post
				$smf_msg_id = get_post_meta($post_id, 'smf_msg_id', true);
				$smf_topic_id = get_post_meta($post_id, 'smf_topic_id', true);

				$topicOptions['id'] = (int)$smf_topic_id;
				$msgOptions['id'] = (int)$smf_msg_id;

				$sucess = modifyPost($msgOptions, $topicOptions, $posterOptions);

			} else {//OR CREATE A NEW ONE?

			  $sucess = createPost($msgOptions, $topicOptions, $posterOptions);
			  add_post_meta($post_id, 'smf_topic_id', $topicOptions['id']);
			  add_post_meta($post_id, 'smf_msg_id', $msgOptions['id']);
		   
			}
			
		}
	
	}
	
	//NOW YOU CAN CANCEL ME
	ignore_user_abort(false);
	
	return $sucess;
	
}

//CAN I POST AUTOMATICALLY...PLZZZZZ
if(get_option('smf_autoexport_posts') == 'true'){
	add_action('publish_post', 'myWPSMF_post_to_smf');
}

function myWPSMF_posts_sync() {
	
	//FROM NOW ON THERES NO WAY YOU CAN STOP ME!!!
	ignore_user_abort(true);
	set_time_limit(0);
	
	$y = 0; //synchronized now
	$n = 0; //not synchronized now
	$t = 0; //total
	$ty = 0; //total synchronized
	$tn = 0; //total not synchronized
	
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
		//WHAT FILTER AM I USING?
		if(isset($_POST['filter-1'])){
			$num_posts = $_POST['num_posts-1'];
			$categories = $_POST['categories-1'];
			$date = $_POST['date-1'];
			$status = $_POST['see-status-1'];
		}elseif(isset($_POST['filter-2'])){
			$num_posts = $_POST['num_posts-2'];
			$categories = $_POST['categories-2'];
			$date = $_POST['date-2'];
			$status = $_POST['see-status-2'];
		}
		
		//SET AND GET DEFAULTS
		if(isset($num_posts)){
			update_option('smf_num_posts', $num_posts);
		}
		
		if(isset($date)){
			$date = str_split($date, 4);
			$year = $date[0];
			$month = $date[1];
		}
	}
	
	$num_posts = get_option('smf_num_posts');
	
	if(!isset($status)){
		$status = 'notexp';
	}
	 
	//TIME TO GET SOME ACTION...
	 if($_POST['smf_hidden'] == 'Y' && $_POST['posts']) {
		 
		/* if(isset($_POST['submit'])){
			$action = $_POST['action'];
		}elseif(isset($_POST['submit2'])){
			$action = $_POST['action2'];
		}else{
			break;
		} */ 
			
		if(isset($_POST['action'])){
			$action = $_POST['action'];
		}else{
			break;
		}
			
		//WHAT WILL I DO?
		if ($action == 'Remove') {
			removeTopics($_POST['posts'],true, true);
		}
		  
		 foreach($_POST['posts'] as &$post_id){
		 
			if ($action == 'Export') {
				$sucess = myWPSMF_post_to_smf ($post_id);
				usleep(100000); //100ms
			}elseif ($action == 'Unlink' || $action == 'Remove') {
				delete_post_meta($post_id, 'smf_topic_id');
				delete_post_meta($post_id, 'smf_msg_id');
			}elseif ($action == 'Remove') {
				//removeTopics($_POST['posts'],true, true);
				//remove the comments of this particular post
				$comments = get_comments ('post_id='.$post_id);
			  foreach($comments as $comm) :
				delete_comment_meta($comm, 'smf_topic_id');
				delete_comment_meta($comm, 'smf_msg_id');
			  endforeach;
			  
			}elseif ($action == '-1') {
				break;
			}
			
			//INCREASE COUNTERS
			if($sucess){
				$y++;
				}else{
					$n++;
				}
			
		 }
	 
	 }
	 
	 //ALL WORK DONE
	 ignore_user_abort(false);
	 
?>
	<script type="text/javascript">
	checked=false;
	function checkedAll (frm) {
		clearSelect(frm);
		var aa= document.getElementById(frm);
		 if (checked == false)
			  {
			   checked = true
			  }else{
				checked = false
				}
		for (var i =0; i < aa.elements.length; i++) 
		{
		 aa.elements[i].checked = checked;
		}
		
	}
	
	function checkedAllNotSinc (frm) {
		clearSelect(frm);
		var aa= document.getElementById(frm);
		 if (checked == false)
			  {
			   checked = true
			  }else{
				checked = false
				}
		for (var i =0; i < aa.elements.length; i++) 
		{
			if(aa.elements[i].className == 'notSinc'){
				aa.elements[i].checked = checked;
				}
		}
		
	}
	
	function clearSelect (frm) {
		var aa= document.getElementById(frm);
		
		for (var i =0; i < aa.elements.length; i++) 
		{
			aa.elements[i].checked = false;
		}
		
	}
	
	</script>
	<div class="wrap">
		<!--<a href="#" id="selectall" onclick='checkedAll("WPSMF_form");' >Select All</a>
		<a href="#" id="selectallnotsinc" onclick='checkedAllNotSinc("WPSMF_form");' >Select All Not Sinc</a>
		<a href="#" id="clearall" onclick='clearSelect("WPSMF_form");' >Clear All</a>-->
	<h2>POST To Synchronize</h2>
	<?php 
		if (!get_option('smf_charset')) {
		  echo '<div style="border:5px solid #aa0000; background:#feeeee; margin:0px;margin-top:10px; padding:4px;clear:both;"><p>';
		  echo '&nbsp;&nbsp;<b>SMF CHARSET NOT SET!</b> Please set smf charset before creating or updating posts</p></div>';
		} 
		
		myWPSMF_table_header (1,$num_posts);
	?>
			
	<form name="WPSMF_form" id="WPSMF_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
	
		<input type="hidden" name="smf_hidden" value="Y">
		
<?php	 
	//GET ALL POSTS
	 $args_to_show = array(
		 'numberposts' => '-1',
		 'post_type' => 'post',
		 'orderby' => 'ID',
		 'order' => 'ASC'
	 );

	 if(isset($categories)){
		if($categories != '-1'){
			$args_to_show['cat'] = $categories;
		}
	 }
	
	 //WHAT WILL I SHOW?
	 if($status == 'notexp'){
		 
		 //GET THE ONES EXPORTED
		$args_to_exclude = array(
			 'meta_key' => 'smf_topic_id',
			 'posts_per_pag' => '-1',
			 'numberposts' => '-1',
			 'post_type' => 'post'
		 );
	 
		$posts_exclude = get_posts($args_to_exclude);
		
		foreach($posts_exclude as $post) :
			$exclude[] = $post->ID;
		endforeach;
		
		//AND EXCLUDE THEM
		$args_to_show['post__not_in'] = $exclude;
		
	}elseif($status == 'exp'){
		
		//SHOW ONLY EXPORTED
		$args_to_show['meta_key'] = 'smf_topic_id';
		
	}
	
	//COUNT
	$total_posts = count(get_posts($args_to_show));
	  
	 if (isset($total_posts) && isset($num_posts) && $num_posts >0){
		$total_page = ceil($total_posts/$num_posts);
	 }
	 
	 if (isset($year) && isset($month) && $year > 0){
		$args_to_show['year'] = $year;
		$args_to_show['monthnum'] = $month;
	 }
	 
	 //ONLY THEN FILTER PAG
	 if(isset($_GET['p'])){
		$p = $_GET['p'];
		}else{
			$p = 1;
		}
 
	 if(isset($num_posts)){
		//$args_to_show['numberposts'] = $num_posts;
		$args_to_show['nopaging'] = false;
		$args_to_show['numberposts'] = $num_posts;
		$args_to_show['posts_per_page'] = $num_posts;
		$args_to_show['offset'] = ($p * $num_posts)-$num_posts;
	 }
	
	 //GET MY POSTS BACK!
	 $myposts = get_posts($args_to_show);
	 
	  if(!$myposts) {
		echo "<h4>No POST Availiable</h4>";
	 } 
	  
	  ?>
		<table class="widefat">
		<thead>
			<tr>
				<th width="5%"><input type="checkbox" name="checkall" value="checkall" onclick='checkedAll("WPSMF_form");'></th>
				<th width="80%"><b>Posts</b></th>
				<th width="5%"><b>Edit</b></th>
				<th width="5%"><b>Preview</b></th>
				<th width="5%"><b>Status</b></th>
			</tr>
		<thead>
		<tbody>
	 <?php
	 
	foreach($myposts as $post) :
	
	//CHECK IF CAT HAS BOARD ID AND IS PUBLISHED
	
	$post_cat = get_the_category($post -> ID);
	$id_board = get_option('smf_'.$post_cat[0]->cat_name );
	$topic = get_post_meta($post->ID, 'smf_topic_id', true);
	$msg = get_post_meta($post->ID, 'smf_msg_id', true);
	
	if((!$id_board == '' || !$id_board == 0) && $post -> post_status == 'publish'){
		
	$t++;
		
	//COUNTERS AND SOME OTHER EASY STUFF
	if($topic != '' && $msg != ''){
		$sinc = true;
		$ty++;
		}else{
			$sinc = false;
			$tn++;
		}
	
?>
		<!-- <p><input type="checkbox" name="posts[]" value="<?php echo $post->ID; ?>"><?php echo $post->post_title; ?> </p>
		
	<hr /> -->
	
	<tr>
	<td style="padding:10px 13px;"><input type="checkbox" name="posts[]" <?php if(!$sinc){echo 'class="notSinc"';}?> value="<?php echo $post->ID; ?>"></td>
	<td style="padding:8px 7px;">
		<?php 
		if($sinc){
			echo $post->post_title; 
			}else{
				echo '<span style="color:red;">'.$post->post_title.'</span>'; 
			}
		?>
	</td>
	<td style="padding:8px 7px;"><a href="post.php?action=edit&post=<?php echo $post->ID; ?>">Edit</a></td>
	<td style="padding:8px 7px;"><a href="<?php echo $post->guid; ?>">Preview</a></td>
	<td style="padding:8px 7px;">
		<?php
		if($sinc){
			$topic = get_post_meta($post->ID, 'smf_topic_id', true);
			$smf_path = get_option('smf_path');
			
			$domain = parse_url($_SERVER['HTTP_HOST']);
			
			$smf_link = "http://".$domain['path']."$smf_path/index.php?topic=$topic.0";
			
			echo '<a href="'.$smf_link.'">OK</a>';
			}else{
					echo '<span style="color:red;"><b>NO</b></span>';
			}
		?>
	</td>
	</tr>
		
	<?php } ?>
	<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr>
			<th><input type="checkbox" name="checkall" value="checkall" onclick='checkedAll("WPSMF_form");'></th>
			<th><b>Posts</b></th>
			<th><b>Edit</b></th>
			<th><b>Preview</b></th>
			<th><b>Status</b></th>
		</tr>
	<tfoot>
	</table> 
	</form>
	<div style="text-align:right;">
	<?php 
		//PAGINATE ME
		$page_links = paginate_links( array(
			'base' => add_query_arg('p','%#%'),
			'format' => '?p=%#%',
			'show_all' => false,
			'total' => $total_page,
			'current' => $p
		));
		echo $page_links;
	?>
	</div>
	<?php myWPSMF_table_header (2, $num_posts); ?>
	<table class="widefat">
		<thead>
			<tr>
				<th><b>Total Posts</b></th>
				<th><b>Total Posts Synchronized</b></th>
				<th><b>Total Posts Not Synchronized</b></th>
				<?php
				if($_POST['smf_hidden'] == 'Y') {
				?>
				<th><b>Posts <?php echo $action?>ed Now</b></th>
				<th><b>Posts Not <?php echo $action?>ed Now</b></th>
				<?php 
				}
				?>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php echo $t?></td>
				<td><?php echo $ty?></td>
				<td><?php echo $tn?></td>
				<?php
				if($_POST['smf_hidden'] == 'Y') {
				?>
				<td><?php echo $y?></td>
				<td><?php echo $n?></td>
				<?php 
				}
				?>
			</tr>
		</tbody>
	</table>
	
	</div>
<?php

}

?>