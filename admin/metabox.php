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
    <input type="text" id="ssil-meta-keyword" class="yoast-input" placeholder="Zoek een pagina..." autocomplete="off">
    <div id="ssil-meta-suggestions" class="yoast-mt-2" style="max-height:200px;overflow-y:auto;"></div>

    <script>
    jQuery(document).ready(function($){
        let timer = null;

        // Loader HTML (gebruik je eigen spinner-HTML als je wilt)
        const loader = '<div class="yoast-spinner" style="margin:12px auto;"></div>';

        $('#ssil-meta-keyword').on('input', function() {
            clearTimeout(timer);
            let val = $(this).val();

            if(val.length < 3) {
                $('#ssil-meta-suggestions').empty();
                return;
            }

            $('#ssil-meta-suggestions').html(loader);

            timer = setTimeout(function() {
                $.ajax({
                    url: ajaxurl,
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        action: 'ssil_suggest_url',
                        keyword: val
                    },
                    success: function(response) {
                        let html = '';
                        if(response && response.success && response.data && response.data.url) {
                            html += '<a href="' + response.data.url + '" target="_blank" rel="noopener" class="yoast-link">' + response.data.url + '</a>';
                        } else {
                            html = '<em class="yoast-text-muted">Geen resultaten</em>';
                        }
                        $('#ssil-meta-suggestions').html(html);
                    },
                    error: function() {
                        $('#ssil-meta-suggestions').html('<em class="yoast-text-muted">Fout bij ophalen resultaten</em>');
                        // Toast melding voor errors als je toast functie hebt:
                        if(typeof showToast === "function") showToast('Fout bij ophalen suggesties.', 'error');
                    }
                });
            }, 350); // wachttijd na laatste toetsdruk
        });
    });
    </script>
    <?php
}