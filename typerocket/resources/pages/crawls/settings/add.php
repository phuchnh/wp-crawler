<?php
include_once __DIR__ . '/../shared/form.php';
include_once __DIR__ . '/../shared/field.php';

/**
 * @var $form \TypeRocket\Elements\Form
 */
$form->useOld();

$domains = get_options_crawl_domain( $form );

$select = $form->select( 'category_id' );
$select->setAttribute( 'class', 'select2' );
$select->setLabel( 'Category URL' );

$categories = new \App\Models\CrawlCategory();
$categories = (array) $categories->findAll()->get() ?: [];
$options    = array_map( function ( \App\Models\CrawlCategory $value ) {
	return [
		'id'        => $value->id,
		'text'      => $value->category_url,
		'domain_id' => $value->crawl_domain_id,
	];
}, $categories );

$repeater = $form->repeater( 'categories' );
$repeater->setFields( [ $form->row( $select, $form->text( 'page' )->setLabel( 'Page' )->setDefault( 1 ) ) ] );
$repeater->setLabel( 'Categories' );

?>

<div class="wrap">
	<?= set_form_title( $form, __( 'Add Setting' ) ) ?>
	<?= $form->open() ?>
	<?= $domains ?>
	<?= $repeater ?>
	<?= set_form_submit( $form ) ?>
	<?= $form->close() ?>
</div>

<script>
  (function($) {
    'use strict';
    $(function() {
      var categories = <?php echo json_encode( $options );?>;
      var $domain = $('#domain');

      function getCategoriesBelongsToDomain(domainId, $dom) {
        // Empty options
        $dom.empty();

        // Get categories belongs to domain
        var options = _.reduce(categories, function(result, value) {
          if (+value['domain_id'] === +domainId) {
            result.push(new Option(value['text'], value['id']));
          }
          return result;
        }, []);

        // Append new options
        $dom.append(options);
      }

      TypeRocket.repeaterCallbacks.push(function($template) {
        var $select = $template.find('select').select2({width: '100%'});

        // Remove unused element after re-init select2
        $select.last().next().next().remove();

        getCategoriesBelongsToDomain($domain.val(), $select);
      });

      $domain.change(function(e) {
        e.preventDefault();
        $('.tr-repeater-fields').empty();
      });
    });
  })(jQuery);
</script>
