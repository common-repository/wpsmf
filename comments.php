<?php
function myWPSMF_comment_to_smf ($comment_id) {

	//FROM NOW ON THERES NO WAY YOU CAN STOP ME!!!
	ignore_user_abort(true);
	set_time_limit(0);
	
	//GET MY COMMENT DATA
	$queried_comment = get_comment($comment_id);
	
	//AND ALSO MY POST DATA
	$queried_post = get_post($queried_comment -> comment_post_ID);
	
	//LETS SEE WHAT WE CAN DO...
	$sucess = false;
	
	//IF COMMENT IS APPROVED LETS MOVE ON
	if ($queried_comment -> comment_approved  == '1'){
	
		//AND WHAT ABOUT MY CHARSET?
		if(get_option('smf_charset') == 'UTF8'){
			$subject = $queried_post -> post_title; //ASSUMING WP IS UTF8
		}else{
			$subject = utf8_decode($queried_post -> post_title);
		}
		
		if(get_option('smf_charset') == 'UTF8'){
			$body = $queried_comment -> comment_content; //ASSUMING WP IS UTF8
		}else{
			$body = utf8_encode($queried_comment -> comment_content);
		}
		
		//APPLY SOME FILTERS TO PREVENT....
		//$body = $queried_comment -> comment_content;
		$body = apply_filters('the_content', $body);
        $body = str_replace(']]>', ']]&gt;', $body);
		
		//..AND WRAP IT IN HTML TAGS
		$body = '[html]'.$body.'[/html]';
		
		//GET THE POSTER SMF USER ID
		$poster = get_option('smf_'.$queried_comment -> comment_author);
		
		//GET THE TIME
		$date = strtotime($queried_comment -> comment_date);
		
		//GET THE SMF BOARD ID...
		$post_cat = get_the_category($queried_post -> ID);
		
		$id_board = get_option('smf_'.$post_cat[0]->cat_name );
		
		//...AND ALSO DE TOPIC ID
		$smf_topic_id = get_post_meta($queried_post -> ID, 'smf_topic_id', true);
		
		//LETS GET YOUR IP
		$ip = $queried_post -> comment_author_IP;
		
		if($ip == ''){
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		
		//I'M NO FOOL!
		if ($id_board != '' && $id_board != 0) {
		
			$msgOptions = array(
			   'id' =>  0,
			   'subject' => $subject,
			   'body' => $body,
			   //'modify_time' => $date,
			   'poster_time' => $date,//SEEMS NOT TO BE WORKING...LETS SEE LATER
			   'smileys_enabled' => true
			);
			
			$topicOptions = array(
			   'id' => $smf_topic_id,
			   'board' => $id_board,
			   'mark_as_read' => true
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
			
			//AM I UPDATING A COMMENT...
			if (get_comment_meta($comment_id, 'smf_msg_id', true)) {
				
				//exist, update comment
				$smf_msg_id = get_comment_meta($comment_id, 'smf_msg_id', true);

				$msgOptions['id'] = (int)$smf_msg_id;

				$sucess = modifyPost($msgOptions, $topicOptions, $posterOptions);

			} else { //..OR CREATING A NEW ONE?

			  $sucess = createPost($msgOptions, $topicOptions, $posterOptions);
			  add_comment_meta($comment_id, 'smf_msg_id', $msgOptions['id']);
		   
			}
			
		}
	
	}
	
	//ALL WORK DONNE...CANCEL ME IF YOU WANT
	ignore_user_abort(false);
	return $sucess;
	
}

//CAN I POST AUTOMATICALLY...PLZZZZZ
if(get_option('smf_autoexport_comments') == 'true'){
	add_action('wp_set_comment_status', 'myWPSMF_comment_to_smf',10,2);
	add_action('comment_post', 'myWPSMF_comment_to_smf',10,2);
	add_action('edit_comment', 'myWPSMF_comment_to_smf',10,2);
}


function myWPSMF_comments_sync() {
	
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
	 if($_POST['smf_hidden'] == 'Y' && $_POST['comments']) {
		  
		 foreach($_POST['comments'] as &$comment_id){
		 
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
			if ($action == 'Export') {
				$sucess = myWPSMF_comment_to_smf ($comment_id);
				usleep(100000); //SLEEP 100MS TO DON'T GET BORED...
			}elseif ($action == 'Unlink') {
				delete_comment_meta($comment_id, 'smf_topic_id');
				delete_comment_meta($comment_id, 'smf_msg_id');
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
	function checkedAll (frm, post) {
		var aa= document.getElementById(frm);
		 if (checked == false)
			  {
			   checked = true
			  }else{
				checked = false
				}
		for (var i =0; i < aa.elements.length; i++) 
		{
			if(post != '' && post != 'all'){
				if(aa.elements[i].className == post){
					aa.elements[i].checked = checked;
				}
			}else if(post == 'all'){
				aa.elements[i].checked = checked;
			}
		}
		
	}
	
	</script>
	
	<div class="wrap">
		
	<h2>Comments To Synchronize</h2>
	<?php 
		if (!get_option('smf_charset')) {
		  echo '<div style="border:5px solid #aa0000; background:#feeeee; margin:0px;margin-top:10px; padding:4px;clear:both;"><p>';
		  echo '&nbsp;&nbsp;<b>SMF CHARSET NOT SET!</b> Please set smf charset before creating or updating posts</p></div>';
		} ?>
	<!--<a href="#" id="selectall" onclick='checkedAll("WPSMF_form","all");' >Select All</a>-->
	<?php myWPSMF_table_header (1,$num_posts); ?>
	<form name="WPSMF_form" id="WPSMF_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="smf_hidden" value="Y">
		<table class="widefat">
		<thead>
			<tr>
				<th><input type="checkbox" name="checkall" value="checkall" onclick='checkedAll("WPSMF_form","all");'> Comments per Post</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
<?php
	// SEE THE POSSIBILITY TO USE comment_count FIELD ON WP_POSTS
	// RETRIVE ALL POSTS WITH COMMENTS
	 $args_comments_to_show = array(
		 'status' => 'approve',
		 'type' => 'comment'
	 );
	 
	$allcomments = get_comments ($args_comments_to_show);
	
	foreach($allcomments as $comment) :
		$post_id[] = $comment->comment_post_ID;
	endforeach;
	
	//CRATE AN ARRAY WITH UNIQUE POSTS WITH COMMENTS ONLY
	$post_id = array_unique($post_id);
	
	//ONLY THEN FILTER
	
	 $args_to_show = array(
		 'post__in' => $post_id,
		 'numberposts' => '-1',
		 'post_type' => 'post',
		 'meta_key' => 'smf_topic_id',
		 'orderby' => 'ID',
		 'order' => 'ASC'
	 );
	
	//APPLYING SOME FILTERS...
	 if(isset($categories)){
		if($categories != '-1'){
			$args_to_show['cat'] = $categories;
		}
	 }
	 
	 //WHAT WILL I SHOW?
	 if($status == 'notexp'){
		
		 GLOBAL $wpdb;
		
		//GET ALL COMMENTS ALREADY EXPORTED
		$comments_exported = $wpdb->get_col("SELECT DISTINCT comment_id FROM $wpdb->commentmeta WHERE meta_key = 'smf_msg_id'");
		 
		//GET ALL COMMENTS
		foreach($allcomments as $comment) :
			$all_comments_id[] = $comment->comment_ID;
		endforeach;
		
		//FIND THE DIFF
		$diff = array_diff($all_comments_id, $comments_exported);
		
		$v = false;
		$q = '';
		foreach($diff as $comment) :
			 if($v){
				$q = $q.',';
			}
			$q = $q.$comment;
			$v = true;
		endforeach;
		
		//RETURN A SQL FORMATED STRING WITH IDS
		
		$posts_with_comments_not_exported = $wpdb->get_col("SELECT DISTINCT (comment_post_ID) FROM $wpdb->comments WHERE comment_ID IN ($q)");
		
		if(!empty($posts_with_comments_not_exported)){
			//IF THERE IS A DIFFERENCE SHOW IT...
			$args_to_show['post__in'] = $posts_with_comments_not_exported;
			}else{
				//...ELSE DON'T BOTHER ME!
				$args_to_show['include'] = 'dfgdfsgdfgdfsgdsfgsdf';
			}
			
	}elseif($status == 'exp'){
		GLOBAL $wpdb;
		
		//GET ALL COMMENTS ALREADY EXPORTED AND GIVE ME THOSE POSTS IDS
		$comments_exported = $wpdb->get_col("SELECT DISTINCT comment_post_ID FROM $wpdb->commentmeta INNER JOIN $wpdb->comments ON $wpdb->comments.comment_ID = $wpdb->commentmeta.comment_ID WHERE meta_key = 'smf_msg_id'");
		
		/* if(is_array($comments_exported)){
			$comments_exported = array_unique($comments_exported);
		} */
		
		$args_to_show['post__in'] = $comments_exported;
		
	}
	
	//AND SOME MORE FILTERING STUFF
	 
	 if (isset($year) && isset($month) && $year > 0){
		$args_to_show['year'] = $year;
		$args_to_show['monthnum'] = $month;
	 }
	 
	 $total_posts = count(get_posts($args_to_show));
	 
	 if (isset($total_posts) && isset($num_posts) && $num_posts >0){
		$total_page = ceil($total_posts/$num_posts);
	 }
	 
	 //ONLY THEN FILTER PAG - EXTREMELLY IMPORTED TO DO ONLY NOW
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
	 
	 //AND FINALLY GET THE POSTS
	 $myposts = get_posts($args_to_show);
	 
	  if(!$myposts) {
		echo "<h4>No POST Availiable</h4>";
	 } 
	  
	 
	foreach($myposts as $post) :
	
	//CHECK IF CAT HAS BOARD ID AND IS PUBLISHED
	
	$post_cat = get_the_category($post -> ID);
	$id_board = get_option('smf_'.$post_cat[0]->cat_name );
	$topic = get_post_meta($post->ID, 'smf_topic_id', true);
	$msg = get_post_meta($post->ID, 'smf_msg_id', true);
	
	if($topic != '' && $msg != ''){
		$psinc = true;
		}else{
			$psinc = false;
		}
	
	if((!$id_board == '' || !$id_board == 0) && $post -> post_status == 'publish'){
	
		$args_comments_to_show = array(
			 'post_id' => $post->ID,
			 'status' => 'approve',
			 'orderby' => 'ID',
			 'type' => 'comment',
			 'order' => 'ASC'
		 );
		 
	 //AFTER SOME EASY FILTERING STUFF RETURN THE COMMENTS
		$mycomments = get_comments ($args_comments_to_show);
		
		if(!$mycomments) {
			/*echo "<h4>No Comments Availiable</h4>";*/
		 } else {
			
		?>
			<br />
			<table class="widefat">
			<thead>
				<tr>
					<th width="5%"><input type="checkbox" name="checkall" value="checkall" class="post-<?php echo $post->ID; ?>"onclick='checkedAll("WPSMF_form","post-<?php echo $post->ID; ?>");'></th>
					<th width="80%"><?php echo $post->post_title; ?></th>
					<th width="5%"><a href="post.php?action=edit&post=<?php echo $post->ID; ?>">Edit</a></th>
					<th width="5%"><a href="<?php echo $post->guid; ?>">Preview</a></th>
					<th width="5%">
						<?php
						if($psinc){
							$topic = get_post_meta($post->ID, 'smf_topic_id', true);
							$smf_path = get_option('smf_path');
							
							$domain = parse_url($_SERVER['HTTP_HOST']);
							
							$smf_link = "http://".$domain['path']."$smf_path/index.php?topic=$topic.0";
							
							echo '<a href="'.$smf_link.'">OK</a>';
							}else{
								echo '<b>NO</b>';
							}
						?>
					</th>
				</tr>
			<thead>
			<tfoot>
				<tr>
					<th width="5%"><input type="checkbox" name="checkall" value="checkall" class="post-<?php echo $post->ID; ?>" onclick='checkedAll("WPSMF_form","post-<?php echo $post->ID; ?>");'></th>
					<th width="80%"><?php echo $post->post_title; ?></th>
					<th width="5%"><a href="post.php?action=edit&post=<?php echo $post->ID; ?>">Edit</a></th>
					<th width="5%"><a href="<?php echo $post->guid; ?>">Preview</a></th>
					<th width="5%">
						<?php
						if($psinc){
							$topic = get_post_meta($post->ID, 'smf_topic_id', true);
							$smf_path = get_option('smf_path');
							
							$domain = parse_url($_SERVER['HTTP_HOST']);
							
							$smf_link = "http://".$domain['path']."$smf_path/index.php?topic=$topic.0";
							
							echo '<a href="'.$smf_link.'">OK</a>';
							}else{
								echo '<b>NO</b>';
							}
						?>
					</th>
				</tr>
			<tfoot>
			<tbody>
			<?php
				foreach($mycomments as $comment) :
					
				$msg = get_comment_meta($comment->comment_ID, 'smf_msg_id', true);
				
				$t++;
		
				if($msg != ''){
					$csinc = true;
					//$ty++;
					}else{
						$csinc = false;
						//$tn++;
					}
				
				$smf_comment_msg_id =  get_comment_meta($comment->comment_ID, 'smf_msg_id', true);
				
				if(($status == 'notexp' && !$csinc) || ($status == 'all') || ($status == 'exp' && $csinc)){
					
					//ONLY THEN INCREASE THE COUNTER...
					if($csinc == true){
					$ty++;
					}else{
						$tn++;
					}
				
			?>
				<tr>
					<th width="5%"><input type="checkbox" class="post-<?php echo $post->ID; ?>" name="comments[]" <?php if(!$csinc){echo 'class="notsinc post-'.$post->ID.'"';}else{echo 'class="post-'.$post->ID.'"';}?> value="<?php echo $comment->comment_ID; ?>"</th>
					<th width="80%">
						<?php 
						if($csinc){
							echo $comment->comment_content; 
							}else{
								echo '<span style="color:red;">'.$comment->comment_content.'</span>'; 
							}
						?>
					</th>
					<th width="5%"><a href="comment.php?action=editcomment&c=<?php echo $comment->comment_ID; ?>">Edit</a></th>
					<th width="5%"><a href="../index.php?p=<?php echo $post->ID; ?>#comment-<?php echo $comment->comment_ID; ?>">Preview</a></th>
					<th width="5%">
						<?php
						if($csinc){
							$topic = get_post_meta($post->ID, 'smf_topic_id', true);
							$smf_path = get_option('smf_path');
							
							$domain = parse_url($_SERVER['HTTP_HOST']);
							
							$smf_link = "http://".$domain['path']."$smf_path/index.php?topic=$topic.msg".$smf_comment_msg_id."#msg".$smf_comment_msg_id."";
							
							echo '<a href="'.$smf_link.'">OK</a>';
							}else{
								echo '<span style="color:red;"><b>NO</b></span>';
							}
						?>
					</th>
				</tr>
			<?php
			}else{
				//OR DECREASE IT IF NOT SHOW
				$t--;
			}
			//}
			endforeach;
		?>
		</tbody>
			</table>
		<?php
		 }
		?>
		
	<?php } ?>
	<?php endforeach; ?>
	</tr>
		</tbody>
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
	<?php myWPSMF_table_header (2,$num_posts); ?>
	
	<table class="widefat">
		<thead>
			<tr>
				<th><b>Total Comments</b></th>
				<th><b>Total Comments Synchronized</b></th>
				<th><b>Total Comments Not Synchronized</b></th>
				<?php
				if($_POST['smf_hidden'] == 'Y') {
				?>
				<th><b>Comments <?php echo $action?>ed Now</b></th>
				<th><b>Comments Not <?php echo $action?>ed Now</b></th>
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
	</td>
	
	</div>
<?php

}
//VOILÁ

?>