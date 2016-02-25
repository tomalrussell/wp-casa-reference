<?php
namespace Casa\Reference\Associate;
function get_post_types(){
	// @TODO make these optional
	return [
		'post',
		'page',
		'person'
	];
}
function setup_admin(){
	$post_types = get_post_types();
	foreach($post_types as $post_type){
		add_meta_box(
			'casa-references',             // html id for the metabox
			__('References'),              // title
			__NAMESPACE__.'\\ref_metabox', // callback to print the html
			$post_type,                    // slug of relevant post-type
			'normal',                      // context (area of admin page)
			'low'                          // priority
		);
	}
}
add_action('add_meta_boxes',__NAMESPACE__.'\\setup_admin');

function ref_metabox($post){
	wp_nonce_field( 'save_references', 'casa_reference' );

	$selected_refs = get_post_meta($post->ID, 'casa_reference_ids', true);
	if(empty($selected_refs)) $selected_refs = array();

	$args = array(
		'post_type' => 'reference',
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC'
	);
	$refs = get_posts($args);

	?>
	<label for="casa_reference_ids">Include references</label>
	<select
		multiple
		data-placeholder="Select references to include"
		name="casa_reference_ids[]"
		id="casa_reference_ids">
		<?php
		foreach ($refs as $ref):
			?>
			<option value="<?= $ref->ID ?>" <?= (in_array($ref->ID, $selected_refs) )? 'selected="selected"' : '' ?> ><?= $ref->post_title ?></option>
			<?php
		endforeach;
		?>
	</select>
	<?php
}

function save_references($post_id){
	if (
		! isset( $_POST['casa_reference'] )
		|| ! wp_verify_nonce( $_POST['casa_reference'], 'save_references' )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| ! current_user_can( 'edit_post', $post_id )
		|| !in_array(get_post_type($post_id), get_post_types())
	) {
		return;
	}

	$data = $_POST['casa_reference_ids'];
	if(!is_array($data)) $data = array();
	$data = array_map("intval", $data);
	update_post_meta( $post_id, 'casa_reference_ids', $data );

}
add_action( 'save_post', __NAMESPACE__.'\\save_references' );