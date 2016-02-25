<?php
namespace Casa\Reference\Utils;

function get_text_fields(){
	return array(
		'casa_reference_author' => 'Author(s)',
		'casa_reference_year' => 'Year',
		'casa_reference_title' => 'Title',
		'casa_reference_journal' => 'Journal',
		'casa_reference_volume' => 'Volume',
		'casa_reference_issue' => 'Issue',
		'casa_reference_pages' => 'Page numbers',
		'casa_reference_doi' => 'DOI number',
		'casa_reference_book' => 'Book title',
		'casa_reference_editor' => 'Editor',
		'casa_reference_translator' => 'Translator',
		'casa_reference_publisher_name' => 'Publisher',
		'casa_reference_publisher_location' => 'Publisher location',
		'casa_reference_isbn' => 'ISBN',
		'casa_reference_url' => 'URL',
		'casa_reference_date_accessed' => 'Date accessed',
		'casa_reference_published_url' => 'Published link',
		'casa_reference_preprint_url' => 'Preprint link (eg arxiv)',
		'casa_reference_presented_at' => 'Presented at',
		'casa_reference_other_details' => 'Other details'
	);
}

function get_reference_types(){
	return array(
		'article' => 'Journal article',
		'chapter' => 'Book chapter',
		'book' => 'Book',
		'website' => 'Website / online resource',
		'presentation' => 'Presentation / conference paper',
		'other' => 'Other',
	);
}

function get_format_strings(){
	return array(
		'article'       => '<strong>%author%</strong> (%year%) &lsquo;%title%&rsquo;. %journal%, %volume% (%issue%) %pages%. %other_details%',
		'chapter'       => '<strong>%author%</strong> (%year%) &lsquo;%title%&rsquo;, in %editor% (ed.) <em>%book%</em>, %publisher_name%, %publisher_location%. %pages%. %other_details%',
		'book'          => '<strong>%author%</strong> (%year%) <em>%title%</em>. %publisher_name%, %publisher_location%. %other_details%',
		'website'       => '<strong>%author%</strong> (%year%) &lsquo;%title%&rsquo; [online]. Available at: &lt;<a href="%url%">%url%</a>&gt;. Accessed: %date_accessed% %other_details%',
		'presentation'  => '<strong>%author%</strong> (%year%) &lsquo;%title%&rsquo;. Presented at: %presented_at%. %other_details%',
		'other'         => '<strong>%author%</strong> (%year%) %title%. %other_details%'
	);
}

class CSVFile extends \SplFileObject {
	private $keys;

	public function __construct($file) {
		parent::__construct($file);
		$this->setFlags(\SplFileObject::READ_CSV);
	}

	public function rewind() {
		parent::rewind();
		$this->keys = parent::current();
		parent::next();
	}

	public function current() {
		return array_combine($this->keys, parent::current());
	}

	public function getKeys() {
		return $this->keys;
	}
}

/**
 * Check uploaded file for errors and mime type
 *
 * @param  string $name field name of file input
 * @param  array  $mime_types array of acceptable mime types
 * @return array  boolean error, string tmp_name of file if no error
 */
function check_uploaded_file($name, $mime_types){
	try {
		// Undefined | Multiple Files | $_FILES Corruption Attack
		// If this request falls under any of them, treat it invalid.
		if (
			!isset($_FILES[$name]['error']) ||
			is_array($_FILES[$name]['error'])
		) {
			throw new RuntimeException('Invalid parameters.');
		}

		// Check $_FILES[$name]['error'] value.
		switch ($_FILES[$name]['error']) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				throw new RuntimeException('No file sent.');
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new RuntimeException('Exceeded filesize limit.');
			default:
				throw new RuntimeException('Unknown errors.');
		}

		// DO NOT TRUST $_FILES[$name]['mime'] VALUE !!
		// Check MIME Type by yourself.
		$finfo = new \finfo(FILEINFO_MIME_TYPE);
		if (false === $ext = array_search(
			$finfo->file($_FILES[$name]['tmp_name']),
			$mime_types,
			true
		)) {
			throw new RuntimeException('Invalid file format.');
		}

		return array(
			'filename'=>$_FILES[$name]['tmp_name'],
			'error'=>false
		);

	} catch (RuntimeException $e) {
		return array(
			'error' => $e->getMessage()
		);
	}
}