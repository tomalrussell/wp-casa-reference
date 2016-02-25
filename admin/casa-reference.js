jQuery(document).ready(function($){
	$('#casa_reference_ids').chosen({
		no_results_text:'No references found.',
		width: '100%'
	});

	var fields_by_type = {
		'article': [
			'casa_reference_author',
			'casa_reference_year',
			'casa_reference_title',
			'casa_reference_journal',
			'casa_reference_volume',
			'casa_reference_issue',
			'casa_reference_pages',
			'casa_reference_doi',
			'casa_reference_published_url',
			'casa_reference_preprint_url'
		],
		'chapter': [
			'casa_reference_author',
			'casa_reference_year',
			'casa_reference_title',
			'casa_reference_pages',
			'casa_reference_book',
			'casa_reference_editor',
			'casa_reference_publisher_name',
			'casa_reference_publisher_location',
			'casa_reference_isbn',
			'casa_reference_published_url',
			'casa_reference_preprint_url'
		],
		'book': [
			'casa_reference_author',
			'casa_reference_year',
			'casa_reference_title',
			'casa_reference_publisher_name',
			'casa_reference_publisher_location',
			'casa_reference_isbn',
			'casa_reference_published_url',
			'casa_reference_preprint_url'
		],
		'website': [
			'casa_reference_author',
			'casa_reference_year',
			'casa_reference_title',
			'casa_reference_url',
			'casa_reference_date_accessed',
		],
		'other': [] // default to all
	};

	function hide_show_fields(){
		var selected_type = $('[name="casa_reference_type"]:checked').val();
		if( selected_type &&
			fields_by_type[selected_type] &&
			fields_by_type[selected_type].length){

			$('.casa-reference-fields .casa-meta-wrap').addClass('hidden');
			$.each(fields_by_type[selected_type], function(index, item){
				$('#'+item).parents('.casa-meta-wrap').removeClass('hidden');
			});

		} else {
			$('.casa-reference-fields .casa-meta-wrap').removeClass('hidden');
		}
	}

	function update_preview(){
		var format = $('[name="casa_reference_type"]:checked').data('reference-format');
		if(!format) return;

		var preview = format.replace(/%([^%]+)%/g, function(match, group){
			var swap = $('#casa_reference_'+group).val();
			return (swap === '')? match : swap;
		});

		$('#casa-reference-preview').html(preview);
	}

	$('[name="casa_reference_type"]').change(update_preview);
	$('.casa-reference-fields input').on("keyup", update_preview);
	$('[name="casa_reference_type"]').change(hide_show_fields);
	hide_show_fields();
	update_preview();
});