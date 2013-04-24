<?php
/**
 * The Categories widget replaces the default WordPress Categories widget. This version gives total
 * control over the output to the user by allowing the input of all the arguments typically seen
 * in the wp_list_categories() function.
 *
 */
class TVST_Countdown_Pro_Widget extends WP_Widget {

	// Prefix for the widget.
	var $prefix;

	// Textdomain for the widget.
	var $textdomain;

	// the episodes
	var $episodes;

	/**
	 * Set up the widget's unique name, ID, class, description, and other options.
	 * @since 0.6.0
	 */
	function __construct() {

		$this->prefix = 'tvst-countdown';
		$this->textdomain = 'tvst-countdown';
	
		// Give your own prefix name eq. your-theme-name-
		$prefix = '';
		
		// Set up the widget options
		$widget_options = array(
			'classname' => 'tvst-countdown',
			'description' => esc_html__( 'Display a countdown for your TV shows.', $this->textdomain )
		);

		// Set up the widget control options
		$control_options = array(
			'id_base' => $this->prefix
		);

		// Create the widget
		$this->WP_Widget( $this->prefix, esc_attr__( 'TVST Countdown', $this->textdomain ), $widget_options, $control_options );
		
		// Load the widget stylesheet for the widgets admin screen
		add_action( 'load-widgets.php', array(&$this, 'tvst_countdown_widget_admin_script_style') );
		add_action( 'admin_print_styles', array(&$this, 'tvst_countdown_widget_admin_style') );
		
		// Print the user costum style sheet
		if ( is_active_widget(false, false, $this->id_base) ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_style( $this->prefix, TVST_COUNTDOWN_URL . 'css/jquery.countdown.css' );
			wp_enqueue_script( $this->prefix, TVST_COUNTDOWN_URL . 'js/jquery.countdown.js' );
			add_action( 'wp_head', array( &$this, 'print_script') );
		}

		add_action('wp_ajax_my_show_search', array($this, 'my_show_search_callback'));
	}

	function my_show_search_callback() {
		if (!isset($_POST['query'])){
     		//you can do what ever here
     		die();
 		}

		$request = new WP_Http;
		$result = $request->request( "http://api.tozelabs.com/v2/show?q=" . $_POST['query']);

		echo $result['body'];

		die();
	}

	// Push the widget stylesheet widget.css into widget admin page
	function tvst_countdown_widget_admin_script_style() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-autocomplete' );
	}
	
	// Push the widget stylesheet widget.css into widget admin page
	function tvst_countdown_widget_admin_style() {
		echo '<style type="text/css"> .tvstControls .timestamp { background-image: url(images/date-button.gif); background-position: left top; background-repeat: no-repeat; padding-left: 18px; }</style>';
	}
	
	function print_script() {
		$settings = $this->get_settings();

		foreach ($settings as $key => $setting){
			$widget_id = $this->id_base . '-' . $key;

			if( is_active_widget( false, $widget_id, $this->id_base ) ) {
				if (!isset($setting['show_id']) or $setting['show_id'] == 0)
					continue;
	
				// Getting last air date and next air date for the selected show
				$request = new WP_Http;
				$result = $request->request( "http://api.tozelabs.com/v2/show/".$setting['show_id']."/next_aired");
				$result = json_decode($result['body'], true);
		
				$last_aired = $result['last_aired'];
				$next_aired = $result['next_aired'];
		
				$this->episodes['last_aired'] = $last_aired;
				$this->episodes['next_aired'] = $next_aired;

				if (!isset($last_aired) && !isset($next_aired))
					continue;
				
				$next_date = $next_aired['first_air_date']." ".$next_aired['air_time'];
		
				try {
					$datetime = new DateTime($next_date, new DateTimeZone("America/New_York"));
				}
				catch(Exception $e) {
					$next_date = $next_aired['first_air_date']." 20:00";
					$datetime = new DateTime($next_date, new DateTimeZone("America/New_York"));
				}
        		$tz = new DateTimeZone("GMT");
        		$datetime->setTimezone($tz);
        		$next_timestamp = mktime($datetime->format("H"), 
        		                      $datetime->format("i"),
        		                      $datetime->format("s"), 
        		                      $datetime->format("m"), 
        		                      $datetime->format("d"), 
        		                      $datetime->format("Y")); 
				
				// Print TVST Countdown script
				echo '<script type="text/javascript">';
					echo 'jQuery(document).ready(function($){';
						//echo "$('#$widget_id-wrapper').jCountdown({timeText: '$time_text', reflection:false, style:'metal', width: 220, dayTextNumber: 3, displaySecond:true})";
						echo "$('#$widget_id-wrapper').countdown({timestamp: $next_timestamp * 1000})";
					echo '});';  
				echo '</script>';
	
				// Print the custom style and script
				if ( !empty( $setting['customstylescript'] ) ) echo $setting['customstylescript'];
			}
		}
	}
	
	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 * @since 0.6.0
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Set up the arguments for wp_list_categories(). */
		$args = array(
			'title'					=> $instance['title'],
			'show'					=> $instance['show'],
			'show_id'				=> $instance['show_id'],
			'theme'					=> $instance['theme'],
			'display_ep_name'		=> $instance['display_ep_name'],
			'display_ep_number'		=> $instance['display_ep_number'],
		);

		// Output the theme's widget wrapper
		echo $before_widget;		

		// If a title was input by the user, display it
		if ( !empty( $instance['title'] ) )
			echo $before_title . apply_filters( 'widget_title',  $instance['title'], $instance, $this->id_base ) . $after_title;
		
		$epId = $this->episodes['next_aired']['id'];
		$showId = $instance['show_id'];
		$episode_number  = 'S'.str_pad($this->episodes['next_aired']['season_number'], 2, "0", STR_PAD_LEFT);
		$episode_number .= 'E'.str_pad($this->episodes['next_aired']['number'], 2, "0", STR_PAD_LEFT);

		if ($instance['display_ep_number'])
			echo '<a href="http://series.tozelabs.com/show/'.$showId.'/episode/'.$epId.'" target="_blank">Episode '.$episode_number;
		
		if ($instance['display_ep_number'] && $instance['display_ep_name'])
			echo '<br>';

		if ($instance['display_ep_name'])
			echo $this->episodes['next_aired']['name']."</a>";

		$theme = $instance['theme'] == 0 ? 'dark' : 'light';
		echo '<div id="'. $this->id . '-wrapper" class="'.$theme.'"></div>';
		echo '<p align="right">provided by <a href="http://www.tozelabs.com"><img src="'.plugins_url( './images/logo16.png', __FILE__ ).'" style="display:inline; border:none"/></a></p>';

		// Close the theme's widget wrapper
		echo $after_widget;
	}

	/**
	 * Updates the widget control options for the particular instance of the widget.
	 * @since 0.6.0
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Set the instance to the new instance. */
		$instance = $new_instance;

		$instance['title'] 				= strip_tags( $new_instance['title'] );
		$instance['show']				= strip_tags( $new_instance['show'] );
		$instance['show_id']			= strip_tags( $new_instance['show_id'] );		
		$instance['theme']				= strip_tags( $new_instance['theme'] );
		$instance['display_ep_name']	= $new_instance['display_ep_name'];
		$instance['display_ep_number']	= $new_instance['display_ep_number'];

		return $instance;
	}

	/**
	 * Displays the widget control options in the Widgets admin screen.
	 * @since 0.6.0
	 */
	function form( $instance ) {

		// Set up the default form values
		// date-time: mm jj aa hh mn
		$defaults = array(
			'title' 			=> esc_attr__( 'Next GOT episode', $this->textdomain ),
			'show'				=> 'Game of Thrones',
			'show_id'			=> '121361',
			'display_ep_name'	=> true,
			'display_ep_number'	=> true,
			'theme'				=> 0
		);

		/* Merge the user-selected arguments with the defaults. */
		$instance = wp_parse_args( (array) $instance, $defaults );

		// Set the default value of each widget input
		global $wp_locale;
		$time_adj = current_time('timestamp');

		?>

		<script type="text/javascript">
			jQuery(document).ready(function($){
				$(document).on("focus", ".show_selection:not(.ui-autocomplete-input)", function(event) { $(this).autocomplete({
						source: function(request, response) {
            				$.ajax({
                				url: ajaxurl,
                				data: { action: 'my_show_search', query: encodeURIComponent($(this.element).val())},
                				dataType: "json",
                				type: "POST",
                				success: function(data){
	            	    			response($.map(data, function(obj) {
                	            		return {
	            	                		label: obj.name,
	            	                		description: obj.nb_followers,
                	            			value: obj.name,
                	            			id: obj.id
                						};
                					}));
                				},
								//error: function(xhr, textStatus, errorThrown){
       							//	alert('Request failed ' + ' ' + textStatus + ' '+ errorThrown);
    							//}                			
            				});
        				},
        				select: function(event, ui) {
							$(this).closest('p').find('input:hidden').val(ui.item.id);
							return false;
                		}
    				}).data("autocomplete")._renderItem = function(ul, item) {
  						return $("<li></li>").data("item.autocomplete", item).append(
  						    '<a><b>' + item.label + '</b><br>' + item.description + ' followers</a>').appendTo(ul);
					};

				});				
			});
		</script>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', $this->textdomain ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label><?php _e( 'Type in your show', $this->textdomain ); ?></label>
			<input type="text" class="widefat show_selection" id="<?php echo $this->get_field_id( 'show' ); ?>" name="<?php echo $this->get_field_name( 'show' ); ?>" value="<?php echo esc_attr( $instance['show'] ); ?>" />
			<input type="hidden" id="<?php echo $this->get_field_id( 'show_id' ); ?>" name="<?php echo $this->get_field_name( 'show_id' ); ?>" value="<?php echo esc_attr( $instance['show_id'] ); ?>" />							
		</p>
		<p>
			<label><?php _e( 'Display episode number', $this->textdomain ); ?></label>
			<input class="checkbox" type="checkbox" <?php checked( (bool) $instance['display_ep_number'], true ); ?> id="<?php echo $this->get_field_id( 'display_ep_number' ); ?>" name="<?php echo $this->get_field_name( 'display_ep_number' ); ?>" />
		</p>	
		<p>
			<label><?php _e( 'Display episode name', $this->textdomain ); ?></label>
			<input class="checkbox" type="checkbox" <?php checked( (bool) $instance['display_ep_name'], true ); ?> id="<?php echo $this->get_field_id( 'display_ep_name' ); ?>" name="<?php echo $this->get_field_name( 'display_ep_name' ); ?>" />
		</p>			
		<p>
			<label><?php _e( 'Select your theme', $this->textdomain ); ?></label>
			<select name="<?php echo $this->get_field_name( 'theme' );?>" id="<?php echo $this->get_field_id( 'theme' );?>">
				<option value="0" <?php selected(0, $instance['theme']); ?>>Dark</option>
				<option value="1" <?php selected(1, $instance['theme']); ?>>Light</option>
			</select>
		</p>	
	<?php
	}
}

?>