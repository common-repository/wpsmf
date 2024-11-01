<?php
/**
 * myWPSMF_recent_posts Class
 */
class myWPSMF_recent_posts extends WP_Widget {
    /** constructor */
    function myWPSMF_recent_posts() {
		parent::WP_Widget(false, $name = 'SMF Recent Posts');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
		$posts = apply_filters('widget_title', $instance['posts']);
        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title; 
						
						$ssi_recentTopics = ssi_recentTopics($posts, array(), array(), 'array');
						
					?>
						<ul class="smf_recent_posts">
					<?php
						foreach($ssi_recentTopics as $topic):
							echo ('<li><a href="'.$topic[href].'">'.utf8_encode($topic[subject]).'</a></li>');
						endforeach;
					?>
						</ul>
					<?php
						
					?>
              <?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = esc_attr($instance['title']);
        $posts = esc_attr($instance['posts']);
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('posts'); ?>"><?php _e('Posts:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('posts'); ?>" name="<?php echo $this->get_field_name('posts'); ?>" type="text" value="<?php echo $posts; ?>" /></label></p>
        <?php 
    }

} // class myWPSMF_recent_posts

// register myWPSMF_recent_posts widget
add_action('widgets_init', create_function('', 'return register_widget("myWPSMF_recent_posts");'));

?>