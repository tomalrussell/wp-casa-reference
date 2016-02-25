<?php
namespace Casa\Reference\Output;
use Casa\Reference\Utils;

function print_section($ref_ids){
	?>
	<section class="references">
		<h2 class="reference-heading"><?= __('References'); ?></h2>
		<ul class="reference-list">
			<?php
			echo get_items($ref_ids);
			?>
		</ul>
	</section>
	<?php
}

function get_items($ref_ids){
	if(empty($ref_ids) || !is_array($ref_ids)){
		return '';
	}

	$items = [];
	foreach ($ref_ids as $ref_id):
		$meta = get_post_meta($ref_id);
		$items[] = '<li class="reference-item">';
		$items[] = get_default_listing($meta);
		$items[] = '</li>';
	endforeach;
	return implode('',$items);
}

function add_references($content){
	$post_types = [
		'post',
		'page'
	];
	if( in_array(get_post_type(), $post_types) ){
		$post_id = get_the_id();
		$ref_ids = get_post_meta($post_id, 'casa_reference_ids', true);

		$buf = ob_start();
		if(!empty($ref_ids)){
			print_section($ref_ids);
		}
		$references = ob_get_contents();
		ob_end_clean();
		$content .= $references;
	}
	return $content;
}
add_filter('the_content', __NAMESPACE__.'\\add_references');

function get_default_listing($meta){
	// Types - defined by 'Casa\Reference\Create\get_reference_types'
	// 'article' => 'Journal article',
	// 'chapter' => 'Book chapter',
	// 'book' => 'Book',
	// 'website' => 'Website / online resource',
	// 'other' => 'Other',

	// Fields - defined by 'Casa\Reference\Create\get_text_fields'
	// 'casa_reference_author' => 'Author(s)',
	// 'casa_reference_year' => 'Year',
	// 'casa_reference_title' => 'Title',
	// 'casa_reference_journal' => 'Journal',
	// 'casa_reference_volume' => 'Volume',
	// 'casa_reference_issue' => 'Issue',
	// 'casa_reference_pages' => 'Page numbers',
	// 'casa_reference_doi' => 'DOI number',
	// 'casa_reference_book' => 'Book title',
	// 'casa_reference_editor' => 'Editor',
	// 'casa_reference_translator' => 'Translator',
	// 'casa_reference_publisher_name' => 'Publisher',
	// 'casa_reference_publisher_location' => 'Publisher location',
	// 'casa_reference_isbn' => 'ISBN',
	// 'casa_reference_url' => 'URL',
	// 'casa_reference_date_accessed' => 'Date accessed',
	// 'casa_reference_published_url' => 'Published link',
	// 'casa_reference_preprint_url' => 'Preprint link (eg arxiv)',

	$formats = Utils\get_format_strings();

	$fields = Utils\get_text_fields();

	$type = $meta['casa_reference_type'][0];

	if(array_key_exists($type, $formats)){
		$format = $formats[$type];
	} else {
		$format = $formats['other'];
	}

	foreach ($fields as $field => $_){
		$format_key = str_replace('casa_reference_', '', $field);
		if(is_array($meta[$field])){
			$format_value = $meta[$field][0];
		} else {
			$format_value = $meta[$field];
		}
		$format = str_replace('%'.$format_key.'%', '<span class="reference-'.$format_key.'">'.$format_value."</span>", $format);
	}

	return $format;
}
