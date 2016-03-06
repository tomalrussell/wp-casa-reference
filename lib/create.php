<?php
namespace Casa\Reference\Create;
use Casa\Reference\Utils;
use Casa\Reference\Output;

function register(){
	$labels = array(
		'name' =>               __('References'),
		'singular_name' =>      __('Reference'),
		'add_new_item' =>       __('Add New Reference'),
		'edit_item' =>          __('Edit Reference'),
		'new_item' =>           __('New Reference'),
		'view_item' =>          __('View Reference'),
		'not_found' =>          __('No references found'),
		'not_found_in_trash' => __('No references found in Trash'),
		'all_items' =>          __('All References')
	);
	$args = array(
		'labels' =>        $labels,
		'public' =>        false,  // @TODO make public status optional?
		'show_ui' =>  true,
		'menu_position' => 8,
		'menu_icon' =>     'dashicons-welcome-learn-more',
		'show_in_nav_menus' => false,
		'has_archive' =>   true,
		'rewrite' => array(
			'slug' => 'papers' // @TODO make slug name an option
		),
		'supports' => array('thumbnail')
	);
	register_post_type('reference', $args);
}
add_action('init', __NAMESPACE__.'\\register');



function filter_title_placeholder($placeholder, $post){
	if($post->post_type == 'reference'){
		$placeholder = 'Enter short title here';
	}
	return $placeholder;
}
add_filter('enter_title_here',__NAMESPACE__.'\\filter_title_placeholder', 10, 2);

function setup_admin(){
	add_meta_box(
		'casa-reference-details',          // html id for the metabox
		__('Reference Details'),           // title
		__NAMESPACE__.'\\details_metabox', // callback to print the html
		'reference',                       // slug of relevant post-type
		'normal',                          // context (area of admin page)
		'high'                             // priority
	);
}
add_action('add_meta_boxes_reference',__NAMESPACE__.'\\setup_admin');

function details_metabox($post){
	wp_nonce_field( 'save_reference_details', 'reference_details' );

	?>
	<h3 id="casa-reference-preview"><?= htmlspecialchars_decode($post->post_title) ?></h3>
	<h4><?php _e('Reference type'); ?></h4>
	<?php

	$selected_type = get_post_meta($post->ID, 'casa_reference_type', true);
	if(empty($selected_type)) $selected_type = 'other';

	$formats = Utils\get_format_strings();

	foreach (Utils\get_reference_types() as $type => $label) {
		?>
		<div class="casa-meta-wrap close">
			<input
				type="radio"
				class="casa-meta-radio"
				name="casa_reference_type"
				id="casa_reference_type_<?= $type ?>"
				data-reference-format="<?= esc_attr($formats[$type]) ?>"
				<?= ($type == $selected_type)? 'checked="checked"' : ''; ?>
				value="<?= $type ?>">
			<label
				class="casa-meta-label"
				for="casa_reference_type_<?= $type ?>"><?= $label; ?></label>
		</div>
		<?php
	}

	?>
	<h4><?php _e('Bibliographic details'); ?></h4>
	<div class="casa-reference-fields">
	<?php

	foreach (Utils\get_text_fields() as $text_field => $label) {
		?>
		<div class="casa-meta-wrap">
			<label
				class="casa-meta-label"
				for="<?= $text_field ?>"><?= $label; ?></label>
			<input
				type="text"
				class="casa-meta-input"
				name="<?= $text_field ?>"
				id="<?= $text_field ?>"
				value="<?= get_post_meta($post->ID, $text_field, true); ?>">
		</div>
		<?php
	}

	?>
	</div>
	<?php
}

function save_details($post_id){
	if (
		! isset( $_POST['reference_details'] )
		|| ! wp_verify_nonce( $_POST['reference_details'], 'save_reference_details' )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| ! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}

	$meta = get_meta_from_post_params();

	foreach ($meta as $key => $value) {
		update_post_meta( $post_id, $key, $value );
	}

	set_title($post_id, $meta);
}
add_action( 'save_post_reference', __NAMESPACE__.'\\save_details' );

function set_title( $post_id, $meta ) {
	global $wpdb;
	$title = Output\get_default_listing($meta);
	$slug = sanitize_title( sanitize_title_with_dashes($title,'','save') );
	$success = $wpdb->query(
		$wpdb->prepare(       
			"UPDATE $wpdb->posts
			SET post_title = %s,
			post_name = %s
			WHERE ID = %d",
			$title,
			$slug,
			$post_id
		)
	);
	return $success;
}

function get_meta_from_post_params(){
	$meta = array();

	foreach (Utils\get_text_fields() as $text_field => $label) {
		// sanitize user input.
		$meta[$text_field] = sanitize_text_field($_POST[$text_field]);
	}

	$type = sanitize_text_field($_POST['casa_reference_type']);
	// validate that 'type' is in list of possible types
	if (in_array($type, array_keys(Utils\get_reference_types()))) {
		$meta['casa_reference_type'] = $type;
	}

	return $meta;
}

function filter_admin_title_view($title){
	if(is_admin() && get_post_type() == 'reference'){
		return strip_tags($title);
	} else {
		return $title;
	}
}
add_filter('the_title',__NAMESPACE__.'\\filter_admin_title_view');

function add_submenu_pages(){
	add_submenu_page( 'edit.php?post_type=reference', 'Upload', 'Upload', 'edit_posts', 'casa-reference-upload', __NAMESPACE__.'\\upload_page' );
	add_options_page( 'References', 'References', 'edit_posts', 'casa-reference-settings', __NAMESPACE__.'\\settings_page');
}
add_action('admin_menu', __NAMESPACE__.'\\add_submenu_pages');

function upload_page(){
	if(isset($_POST['submit']) && $_POST['submit'] === "Upload CSV"){
		handle_csv_upload();
	}
	?>
	<h2>Upload References</h2>
	<p>Select a CSV of references to add to the site.</p>
	<p>The CSV must follow this format exactly:
	<a href="<?= admin_url('edit.php?post_type=reference&page=casa-reference-upload&submit=Download Template'); ?>">
	(download template CSV)
	</a></p>
	<form action="<?= admin_url('edit.php?post_type=reference&page=casa-reference-upload'); ?>"
		method="post"
		enctype="multipart/form-data">
		<input type="file" name="csv">
		<?php \submit_button('Upload CSV'); ?>
	</form>
	<?php
}

function handle_example_csv_request(){
	if(isset($_GET['submit']) && $_GET['submit'] === "Download Template"){

		header('Content-Disposition: attachment; filename="reference-template.csv');
		print_example_csv();
		die();
	}
}
add_action('admin_init', __NAMESPACE__.'\\handle_example_csv_request');

function print_example_csv(){
	$types = array_keys(Utils\get_reference_types());
	$last = array_pop($types);
	echo '"Reference type ('.implode(', ', $types).' or '.$last.')",';

	$fields = Utils\get_text_fields();
	$values = array_values($fields);
	echo implode(',', $values) . "\n";
}

function handle_csv_upload(){
	$file = Utils\check_uploaded_file('csv', array('csv' => 'text/csv', 'text'=>'text/plain'));

	if(!$file['error']){
		$csv = new Utils\CSVFile($file['filename']);
		ob_start();
		foreach ($csv as $line) {
			if(!empty($line)){
				$keys = array_keys(Utils\get_text_fields());       // get meta keys
				array_unshift( $keys, 'casa_reference_type' );     // add 'type' key
				$line = array_combine($keys, array_values($line)); // re-key uploaded array, assuming order

				save_new_reference_post($line);
			}
		}
		ob_end_clean();
	}
}

function save_new_reference_post($meta){
	$post_id = wp_insert_post(array(
		'ID'=>0,
		'post_title'=>'test',
		'post_type'=>'reference'
	), true);

	foreach ($meta as $key => $value) {
		update_post_meta( $post_id, $key, $value );
	}

	set_title($post_id, $meta);
}

function settings_page(){
	?>
	<h2>Reference Settings</h2>
	<?php
}


