<?php
if (!defined('ABSPATH')) exit;

// Metabox registreren
add_action('add_meta_boxes', function() {
    add_meta_box(
        'ssil_suggest_metabox',
        'Interne Link Suggesties',
        'ssil_render_suggest_metabox',
        ['post', 'page'],  // post types waar het zichtbaar is
        'side',
        'default'
    );
});

// Metabox inhoud
function ssil_render_suggest_metabox($post) {
    ?>
    <input type="text" id="ssil-meta-keyword" class="ssil-input" placeholder="Zoek een pagina..." autocomplete="off" style="width: 100%; margin-bottom: 8px;">
    <div id="ssil-meta-suggestions" style="max-height: 200px; overflow-y: auto;"></div>

    <script>
    jQuery(document).ready(function($){
        let timer = null;

        $('#ssil-meta-keyword').on('input', function() {
            clearTimeout(timer);
            var val = $(this).val();

            if(val.length < 3) {
                $('#ssil-meta-suggestions').empty();
                return;
            }

            timer = setTimeout(function() {
                $.ajax({
                    url: ajaxurl,
                    method: 'GET',
                    data: {
                        action: 'ssil_suggest_url',
                        keyword: val
                    },
                    success: function(response) {
                        var html = '';
                        if(response.success && response.data.url) {
                            html += '<a href="' + response.data.url + '" target="_blank">' + response.data.url + '</a>';
                        } else {
                            html = '<em>Geen resultaten</em>';
                        }
                        $('#ssil-meta-suggestions').html(html);
                    },
                    error: function() {
                        $('#ssil-meta-suggestions').html('<em>Fout bij ophalen resultaten</em>');
                    }
                });
            }, 300); // wachttijd van 300ms na laatste toetsdruk
        });
    });
    </script>
    <?php
}