<?php
/*
Plugin Name: Remember Old Post Widget
Description: This widget plugin shows the list of posts you wrote on the same day in the past.
Author: hondamarlboro
Author URI: http://daisukeblog.com
License: GPLv2 or later
Version: 0.2
*/
class FJLoadTextDomain {
	var $domain = '';
	var $loaded = false;

	function FJLoadTextDomain($_domain) {
		$this->__construct($_domain);
	}

	function __construct($_domain) {
		$this->domain = $_domain;
	}

	function load() {
		if ($this->loaded) {
			return;
		}
		$locale  = get_locale();
		$mofile  = dirname(__FILE__);
		$mofile .= "/{$this->domain}-{$locale}.mo";
		load_textdomain($this->domain, $mofile);
		$this->loaded = true;
	}
}

$getoldpost_td = new FJLoadTextDomain('getoldpost');

class OldPostWidget extends WP_Widget {
	public function __construct() {
		global $getoldpost_td;
		$getoldpost_td->load();

		parent::__construct(
			'oldpost_widget',
			'Remember Old Post Widget',
			array( 'description' => __('Display your posts written on the same day years ago', $getoldpost_td->domain), )
			);
	}

	public function widget( $args, $instance) {
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;
			if (!empty($title))
				echo $before_title . $title . $after_title;

                ?><?php echo get_oldpost(); ?><?php
                echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}
	public function form( $instance ) {
		global $getoldpost_td;
		$getoldpost_td->load();

		if ( $instance ) {
			$title = esc_attr($instance['title']);
		} else {
			$title = __('Remember Old Post Widget', $getoldpost_td->domain);
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<?php
	}
}

function get_oldpost(){

    $yday = date('Y',current_time('timestamp',0));
	$mday = date('m',current_time('timestamp',0));
	$dday = date('d',current_time('timestamp',0));

	$result = query_posts( 'monthnum='.$mday.'&day='.$dday );
	$body = "<ul>";

	global $getoldpost_td;
	$getoldpost_td->load();

	if(count($result) != 0){
		$yu = __('yrs', $getoldpost_td->domain);
		for($i=0; $i < count($result); $i++){
			$py[$i] = date('Y', strtotime($result[$i]->post_date));
			if($yday != $py[$i]){
				$yearago[$i] = $yday - $py[$i];
				if($yearago[$i]==1) $yu = __('yr', $getoldpost_td->domain);
				$body .= "<li><span class='wp_ryp'>[".$yearago[$i].$yu.__(' ago', $getoldpost_td->domain)."]</span> <a href='".get_permalink($result[$i]->ID)."'>".esc_html($result[$i]->post_title)."</a></li>";
			} else if (count($result)==1){
				$body .= "<span class='wp_rypn'>".__('On the same day you had a break from writing:-)', $getoldpost_td->domain)."</span><br>";
			}
		}
	} else {
		$body .= "<span class='wp_rypn'>".__('On the same day you had a break from writing:-)', $getoldpost_td->domain)."</span><br>";
	}
	$body .= "</ul>";
	return $body;
}

add_action('widgets_init',
        create_function('', 'return register_widget("OldPostWidget");')
);

?>
