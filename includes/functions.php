<?php
// Create Custom Events Post Type
function custom_events_post_type() 
{
    $labels = array(
        'name'                => _x( 'Events', 'Post Type General Name' ),
        'singular_name'       => _x( 'Event', 'Post Type Singular Name' ),
        'menu_name'           => __( 'Events'),
        'parent_item_colon'   => __( 'Parent Event'),
        'all_items'           => __( 'All Events'),
        'view_item'           => __( 'View Event'),
        'add_new_item'        => __( 'Add New Event' ),
        'add_new'             => __( 'Add New'),
        'edit_item'           => __( 'Edit Event' ),
        'update_item'         => __( 'Update Event' ),
        'search_items'        => __( 'Search Event' ),
        'not_found'           => __( 'Not Found' ),
        'not_found_in_trash'  => __( 'Not found in Trash'),
    );
       
    $args = array(
        'label'               => __( 'events' ),
        'description'         => __( 'Event news and reviews'),
        'labels'              => $labels,
        'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
       
        'taxonomies'          => array( 'genres' ),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' => true,
 
    );  
    // Registering your Custom Post Type
    register_post_type( 'events', $args );
 
}
add_action( 'init', 'custom_events_post_type', 0 );

//Create Custom Event Types Texonomy
add_action( 'init', 'create_event_types_taxonomy');

function create_event_types_taxonomy() 
{
  $labels = array(
    'name' => _x( 'Event Types', 'taxonomy general name' ),
    'singular_name' => _x( 'Event Type', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Event Type' ),
    'all_items' => __( 'All Event Types' ),
    'parent_item' => __( 'Parent Event Type' ),
    'parent_item_colon' => __( 'Parent Event Type:' ),
    'edit_item' => __( 'Edit Event Type' ), 
    'update_item' => __( 'Update Event Type' ),
    'add_new_item' => __( 'Add New Event Type' ),
    'new_item_name' => __( 'New Event Type Name' ),
    'menu_name' => __( 'Event Types' ),
  ); 	
  
   // Now register the taxonomy
  register_taxonomy('event_types',array('events'), array(
    'hierarchical' => true,
    'labels' => $labels,
	'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields' ),
	'show_in_rest' => true,
    'show_ui' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'author' ),
  ));
}

function event_data_meta_box() 
{
    $screens = array('events' );

    foreach ( $screens as $screen ) 
	{
        add_meta_box(
            'event-data',
            __( 'Events Data' ),
            'event_data_meta_box_callback',
            $screen
        );
    }
}
add_action( 'add_meta_boxes', 'event_data_meta_box' );

function events_cpt() 
{
    $args = array(
        'label'                => 'Events',
        'public'               => true,
        'register_meta_box_cb' => 'event_data_meta_box'
    );

    register_post_type( 'events', $args );
}

add_action( 'init', 'events_cpt' );

function event_data_meta_box_callback( $post ) 
{
    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'event_data_nonce', 'event_data_nonce' );

    $event_date = get_post_meta( $post->ID, '_event_date', true );
    $event_venue = get_post_meta( $post->ID, '_event_venue', true );
    $event_location = get_post_meta( $post->ID, '_event_location', true );

	?>
	<p class="meta-options">
        <label for="event_start_date">Event Date</label>
        <input id="event_date" type="date" name="event_date" value="<?php echo $event_date; ?>">
    </p>	
	<p class="meta-options">
        <label for="event_venue">Event Venue</label>
        <input id="event_venue" type="text" name="event_venue" value="<?php echo $event_venue; ?>">
    </p>	
	<p class="meta-options">
        <label for="event_location">Location</label>
        <input id="event_location" type="text" name="event_location" value="<?php echo $event_location; ?>">
    </p>
   
	<?php
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id
 */
function save_event_data_meta_box_data( $post_id ) 
{
    // Check if our nonce is set.
    if ( ! isset( $_POST['event_data_nonce'] ) ) 
	{
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['event_data_nonce'], 'event_data_nonce' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

	$event_date = '';
	$event_venue = '';
	$event_location = '';
	
    // Make sure that it is set.
    if (isset( $_POST['event_date'] ) ) 
	{
        $event_date =sanitize_text_field( $_POST['event_date'] );
    }	
	if (isset( $_POST['event_venue'] ) ) 
	{
        $event_venue =sanitize_text_field( $_POST['event_venue'] );
    }
	if (isset( $_POST['event_location'] ) ) 
	{
        $event_location =sanitize_text_field( $_POST['event_location'] );
    }
	
    // Update the meta field in the database.
    update_post_meta( $post_id, '_event_date', $event_date );
    update_post_meta( $post_id, '_event_venue', $event_venue );
    update_post_meta( $post_id, '_event_location', $event_location );
}

add_action( 'save_post', 'save_event_data_meta_box_data' );

//Create Custom Event filter Shortcode
add_action('init','events_page');
function events_page()
{
	if ( !get_option('events_filter') )
	{ 
		$curr_page = array(
			'post_title' => __('Events List'),
			'post_content' => '[events_filter]',
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'post_category' => array(1),
			'post_parent' => 0 );
			$curr_created = wp_insert_post( $curr_page );
			update_option( 'events_filter', $curr_created );
	} 
}
//Event Search Function
add_shortcode( 'events_filter','events_filter_function' );
function events_filter_function()
{
	?>
	<link href = "https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css"
         rel = "stylesheet">
	<script src = "https://code.jquery.com/jquery-1.10.2.js"></script>
    <script src = "https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
	<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
	<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
	<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
	
	<?php
	wp_enqueue_script('jquery-dataTables', plugins_url('/event-management/admin/js/jquery-dataTables.js'), array( 'jquery' ),true );
	wp_enqueue_script('jquery-dataTables', plugins_url('/event-management/admin/js/jquery-dataTables.js'), array( 'jquery' ),true );
	wp_enqueue_script('dataTables-editor', plugins_url('/event-management/admin/js/dataTables-editor.js'), array( 'jquery' ),true );	
	wp_enqueue_script('dataTables-responsive', plugins_url('/event-management/admin/js/dataTables-responsive.js'), array( 'jquery' ),true );
	wp_enqueue_style('dataTables-css', plugins_url('/event-management/admin/css/dataTables.css'),true );
	wp_enqueue_style('dataTables-responsive-css', plugins_url('/event-management/admin/css/dataTables-responsive.css'),true );
	?>
	<script type="text/javascript">
	$(document).ready(function() 
	{		
		$('#start-end-date').daterangepicker({		
			  locale: {
            format: 'DD-MM-YYYY'
        }	
		  }, function(start, end, label) 
		  {			
			$('#start_date').val(start.format('DD-MM-YYYY'));
			$('#end_date').val(end.format('DD-MM-YYYY'));		
		  });
		
		fill_datatable();
  
		function fill_datatable(start_date = '', end_date = '', event_type = '')
		{
		   var dataTable = $('#event_list').DataTable({
			"processing" : true,
			"serverSide" : true,
			"order" : [],
			"searching" : true,
			"ajax" : {
			 url:'<?php echo admin_url('admin-ajax.php'); ?>',
			 type:"POST",
			 data:{ 
				action:"get_event_data_ajax_action",
				start_date:start_date, 
				end_date:end_date, 
				event_type:event_type
			 }
			}
		   });
		}
		  
		$('#filter').click(function()
		{	
			   var start_date = $('#start_date').val();
			   var end_date = $('#end_date').val();
			   var event_type = $('#evet_type').val();
			   
			   console.log('start_date',start_date);
			   console.log('end_date',end_date);
			   console.log('event_type',event_type);
			   
			   if(start_date == '' && end_date == '' && event_type == '')
			   {
					alert('Please apply any filter');
					$('#event_list').DataTable().destroy();
					fill_datatable();
			   }
			   else
			   {
				   $('#event_list').DataTable().destroy();
					fill_datatable(start_date,end_date, event_type);	
			   }
		});
	});
	</script>
	<style>	
	#event_list
	{
		font-size: 17px;
	}
	.fa 
	{
	  font-size: 20px;
	}
	.checked 
	{
	  color: orange;
	}
	.div_width
	{
		width:80%;
		max-width:80%!important;
	}
	#book_list_length,#book_list_filter
	{
		display:none;
	}
	th,td
	{
		text-align:center;
		border-color:black;
	}
	#search_form
	{
		border:1px solid black;
		padding:15px;
		width:100%;
		float:left;
		margin-bottom:50px;
	}
	#search_form label
	{
		width:15%;
		font-weight: bold;
		font-size: 22px;
		margin-top: 10px;
		padding-left: 15px;
	}
	#search_form input,#search_form select
	{
		width:33%;
	}
	#search_form .form-group
	{
		display:flex;
		margin-top:10px;
		margin-bottom:10px;
	}
	.second_lable
	{
		padding-left:20px;
	}
	.search_event_btn
	{
		margin-left: 43%!important;
		width: 200px!important;
		background-color: white!important;
		color: black!important;
		font-size: 22px!important;
		border: 2px solid!important;
		padding: 15px!important;
	}
	.search_event_btn:hover
	{
		text-decoration:none!important;
	}
	.heading_book_search
	{
		text-align:center;
		width:100%;
		font-size: 20px;
		font-weight: bold;
	}
	.search_event_btn
	{
		text-transform: capitalize!important;	
		margin-top: 30px!important;
	}
	</style>
	
	<div class="table-responsive div_width">
		<form name="book_search_form" action="" method="post" id="search_form" enctype='multipart/form-data'>
		<div class="form-group">
			<label class="control-label"><?php esc_html_e('Start & End Date :');?></label>
			<input type="hidden" id="start_date" value="<?php echo date('d-m-Y'); ?>">
			<input type="hidden" id="end_date" value="<?php echo date('d-m-Y'); ?>">
			<input class="form-control has-feedback-left text-input" type="text"  id="start-end-date" name="start-end-date">			
			<label class="control-label"><?php esc_html_e('Event Type :');?></label>
			<select name="evet_type" id="evet_type">
				<option value="">Select Event Type</option>
				<?php
				$terms = get_terms([
					'taxonomy' => 'event_types',
					'hide_empty' => false,
				]);
			
				foreach($terms as $data)
				{
					?>
				  <option value="<?php echo  $data->term_id; ?>"><?php echo  $data->name; ?></option>
					<?php
				}
				?>
			</select>			
		</div>	
		<div class="form-group">		
			<button type="button" name="filter" id="filter" class="btn btn-info search_event_btn">Filter</button>
		</div>	
		</form>
		<table id="event_list" class="display" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th><?php  _e( 'No') ;?></th>
					<th><?php _e( 'Event Name' ) ;?></th>
					<th><?php _e( 'Event Type') ;?></th>
					<th><?php _e( 'Event Date' ) ;?></th>					
					<th><?php _e( 'Venue') ;?></th>
					<th><?php _e( 'Location ' ) ;?></th>									
				</tr>
			</thead>
			<tbody>
			
			</tbody>
			</table>
		</div>	
	<?php
  return ob_get_clean();
}
//Save Post title into the postmeta 
function wpse_275785_save_title_as_meta( $post_id, $post, $update ) 
{
    update_post_meta( $post_id, 'post_title', $post->post_title );
}
add_action( 'save_post', 'wpse_275785_save_title_as_meta', 99, 3 );

//Get Events Ajax//
add_action('wp_ajax_get_event_data_ajax_action', 'get_event_data_ajax_action');
add_action('wp_ajax_nopriv_get_event_data_ajax_action', 'get_event_data_ajax_action');

function get_event_data_ajax_action()
{
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $event_type = $_POST['event_type'];
	
	if($start_date == '' && $end_date == '' && $event_type == '')
	{ 
		$get_events = get_posts(array(		
				'post_type'   => 'events',
				'post_status' => 'publish',
				'orderby' => 'ID', 
				'posts_per_page' => -1
				
				));			
	}
	else
	{	
		if(!empty($start_date) )
		{			
			$meta_array[] =
				  array(
					  'key' => '_event_date', 
					  'value' => date('Y-m-d',strtotime($start_date)),
					  'compare' => '>='
				);
		} 
		 
		if(!empty($end_date) ) 
		{			
			$meta_array[] =
				  array(
					  'key' => '_event_date', 
					  'value' =>date('Y-m-d',strtotime($end_date)),
					  'compare' => '<='
				);
		} 
		
		$tax_query=array();
		if(!empty($event_type) ) 
		{
			$tax_query[] = 
			  array(
				  'taxonomy' => 'event_types',
					'field' => 'term_id',
					'terms' => $event_type
				);
		}
		
		$args = array( 
			  'post_type'      => 'events',              
			  'post_status'    => 'publish', 
			  'posts_per_page' => -1,
			  'orderby' => 'ID', 					  
			 'meta_query'     => $meta_array,
			  'tax_query'      => $tax_query
		);

		$args['meta_query']['relation'] = 'AND';
		$args['tax_query']['relation'] = 'AND';
		
		$get_events = get_posts($args);			
	} 
	
	$data = array();
	$i=1;
	foreach($get_events as $event_data)
	{
		 $sub_array = array();
		 $sub_array[] = $i;
		 $sub_array[] = $event_data->post_title;
		 $event_types_array=array();
		 $event_types_list = wp_get_post_terms( $event_data->ID, 'event_types', array( 'fields' => 'all' ) ); 
		 if(!empty($event_types_list))
		 {
			foreach ($event_types_list as $event_type_data)
			{ 
				$event_types_array[]=$event_type_data->name;							
			}		
			$sub_array[] = implode(',',$event_types_array);
		}
		else
		{			
			$sub_array[] = '-';
		}
		$event_date=get_post_meta($event_data->ID, '_event_date', true);
		
		$sub_array[] = date('d-m-Y',strtotime($event_date));		
		$sub_array[] = get_post_meta($event_data->ID, '_event_venue', true);
		$sub_array[] = get_post_meta($event_data->ID, '_event_location', true);
		$data[] = $sub_array;
		$i++;
	}

	$total_count_row=count($get_events);
	$number_filter_row=count($get_events);
	$output = array(
	 "draw"       =>  intval($_POST["draw"]),
	 "recordsTotal"   => $total_count_row,
	 "recordsFiltered"  =>  $number_filter_row,
	 "data"       =>  $data
	);

	echo json_encode($output);

	exit;
}