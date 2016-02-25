<?php
use Casa\Reference\Output;

function cr_the_references($post_id = 0){
	echo cr_get_references($post_id);
}

function cr_get_references($post_id = 0){
	if(!$post_id){
		$post_id = get_the_id();
	}
	$ref_ids = get_post_meta($post_id, 'casa_reference_ids', true);
	$references = Output\get_items($ref_ids);

	return $references;
}
