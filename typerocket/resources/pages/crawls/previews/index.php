<?php
/** @noinspection PhpUnhandledExceptionInspection */

/**@var $form \TypeRocket\Elements\Form */
$form->useUrl( 'POST', '/api/v1/wp-crawler/preview' );

$crawl_categories = [];
$options          = ( new \App\Models\CrawlCategory )->findAll()->get() ?? [];
foreach ( $options as $option ) {
	$item['id']              = $option->id;
	$item['text']            = $option->category_url;
	$item['crawl_domain_id'] = $option->crawl_domain_id;
	$crawl_categories[]      = $item;
}

$domains = $form->select( 'domain_id' )->setLabel( 'Domain URL' );
$domains->setModelOptions( new \App\Models\CrawlDomain, 'domain_url', 'id' );
$domains->setAttribute( 'class', 'select2' );
$domains->setAttribute( 'id', 'domain' );

$catogories = $form->select( 'category_id' )->setLabel( 'Category URL' );
$catogories->setAttribute( 'class', 'select2' );
$catogories->setAttribute( 'id', 'category' );

?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css">

<div class="wrap">
    <h1 class="wp-heading-inline"><?= __( 'Preview' ) ?></h1>
    <hr class="wp-header-end">
    <br>
    <div class="flex md:flex-row flex-wrap">
        <div class="w-full md:w-2/5">
			<?php echo $form->open( [ 'id' => 'preview_form' ] ) ?>
            <fieldset>
                <div class="mb-4">
					<?php echo $domains; ?>
                </div>
                <div class="mb-4">
					<?php echo $catogories; ?>
                </div>
                <div class="mb-4">
					<?php echo $form->submit( 'Preview' )->setAttribute( 'id', 'preview_btn' ); ?>
                </div>
            </fieldset>
			<?php echo $form->close() ?>
        </div>
        <div class="w-full md:w-3/5 p-4">
            <div id="preview_show"></div>
        </div>
    </div>
</div>

<script>
  (function($) {
    'use strict';
    $(function() {
      var categories = <?php echo json_encode( $crawl_categories );?>;
      var $result = $('#preview_show');
      var $category = $('#category');
      var $domain = $('#domain');

      getCategoriesBelongsToDomain($domain.val());

      function getCategoriesBelongsToDomain(domainId) {
        // Empty options
        $category.empty();

        // Get categories belongs to domain
        var options = _.reduce(categories, function(result, value) {
          if (+value['crawl_domain_id'] === +domainId) {
            result.push(new Option(value['text'], value['id']));
          }
          return result;
        }, []);

        // Append new options
        $category.append(options);
      }

      $domain.change(function(e) {
        e.preventDefault();
        getCategoriesBelongsToDomain($(this).val());
      });

      function render(resource) {
        var items = [];
        _.each(resource, function(obj) {
          var div = document.createElement('div');
          div.className = 'mb-4';
          div.classList.add('bg-white', 'shadow-md', 'p-4');

          var block = document.createElement('div');
          block.className = 'control-group';

          var keys = _.keys(obj) || [];
          _.each(keys, function(key) {
            var label = document.createElement('p');
            label.className = 'font-bold';
            label.innerHTML = key;

            var content = null;
            var type = obj[key]['type'];
            var value = obj[key]['value'] || '';

            if (type === 'link') {
              content = document.createElement('a');
              content.href = value;
              content.target = 'blank';
              content.innerHTML = value;
            }

            if (type === 'image') {
              content = document.createElement('img');
              content.src = value;
              content.classList.add('object-cover', 'w-1/4', 'h-1/4');
            }

            if (['text', 'html'].indexOf(type) > -1) {
              content = document.createElement('p');
              content.innerHTML = value;
            }

            // Append content to item
            $(block).append(label, content);
          });

          // Append item to list
          items.push($(div).append(block));
        });

        $result.fadeIn('slow', function() {
          $(this).append(_.assign([], items));
        });
      }

      $('#preview_form').submit(function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $form = $(this);

        var request = $.ajax({
          type: $form.attr('method'),
          url: $form.attr('action'),
          data: $form.serialize(),
          context: this,
          beforeSend: function() {
            $.LoadingOverlay('show', {maxSize: 50});
            $result.fadeOut('slow', function() {
              $(this).empty();
            });
          },
        });

        request.done(function(response) {
          if (_.isString(response)) {
            swal('Oops', 'Something went wrong!', 'error');
          }

          if (response.messageType === 'error') {
            swal('Oops', response.message, 'error');
          }

          if (!response.data.resource) {
            swal('Oops', 'Resource not found!', 'error');
          }

          if (response.data.resource.length > 0) {
            render(response.data.resource);
          }

          $.LoadingOverlay('hide');
        });

        request.fail(function(response) {
          $.LoadingOverlay('hide');
          if (response.status === 404) {
            return swal('Oops', 'Resource not found!', 'error');
          }
        });

      });
    });
  })(jQuery);
</script>