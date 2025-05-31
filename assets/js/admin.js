document.addEventListener('DOMContentLoaded', function() {
    // ===== Keyword suggestie & auto-URL invullen =====
    const keywordInput = document.querySelector('input[name="new_word"]');
    const urlInput = document.querySelector('input[name="new_url"]');
    const submitBtn = document.querySelector('.ssil-submit-btn, .ssil-btn-primary');

    let timeout = null;
    let loading = false;

    if (keywordInput && urlInput) {
        keywordInput.addEventListener('input', function() {
            clearTimeout(timeout);
            const query = this.value.trim();

            // Reset & disable submit knop als keyword te kort is
            if (query.length < 3) {
                urlInput.value = '';
                if (submitBtn) submitBtn.disabled = true;
                urlInput.classList.remove('ssil-loading');
                return;
            }

            if (submitBtn) submitBtn.disabled = false;
            urlInput.classList.add('ssil-loading');

            timeout = setTimeout(() => {
                loading = true;
                fetch(ajaxurl + '?action=ssil_suggest_url&keyword=' + encodeURIComponent(query), {
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.url) {
                        urlInput.value = data.url;
                    } else {
                        urlInput.value = '';
                    }
                })
                .catch(() => {
                    urlInput.value = '';
                })
                .finally(() => {
                    urlInput.classList.remove('ssil-loading');
                    loading = false;
                });
            }, 450);
        });

        // Zet submitbutton uit als keyword of url leeg is
        [keywordInput, urlInput].forEach(inp => {
            inp.addEventListener('input', function() {
                if (submitBtn) submitBtn.disabled = !keywordInput.value.trim() || !urlInput.value.trim();
            });
        });
        // Init state
        if (submitBtn) submitBtn.disabled = !keywordInput.value.trim() || !urlInput.value.trim();
    }

    // ===== Highlight bij hover in logs-tabel =====
    const table = document.getElementById('ssil-logs-table');
    if (table) {
        table.addEventListener('mouseover', function(e) {
            const row = e.target && e.target.closest('tr');
            if (row) row.classList.add('ssil-row-hover');
        });
        table.addEventListener('mouseout', function(e) {
            const row = e.target && e.target.closest('tr');
            if (row) row.classList.remove('ssil-row-hover');
        });
    }

    // ===== Rapportage tabel kopiëren =====
    const copyBtn = document.getElementById('ssil-copy-report');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            const table = document.getElementById('ssil-report-table');
            let txt = '';
            for (let row of table.rows) {
                let vals = [];
                for (let cell of row.cells) vals.push(cell.textContent.trim());
                txt += vals.join('\t') + "\n";
            }
            navigator.clipboard.writeText(txt).then(() => {
                alert('Rapportage gekopieerd!');
            });
        });
    }

    // ===== Suggesties toggle + sluiten =====
    const toggle = document.getElementById('ssil-suggest-toggle');
    const suggestWrap = document.getElementById('ssil-suggest-wrap');
    const closeBtn = document.getElementById('ssil-suggest-close');

    if (toggle && suggestWrap) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            // Herlaad pagina met parameter voor suggesties tonen
            window.location = toggle.href + '&ssil_action=suggest_keywords';
        });
    }

    if (closeBtn && suggestWrap) {
        closeBtn.addEventListener('click', function() {
            // Verberg suggesties en toon toggle weer
            suggestWrap.style.display = 'none';
            if (toggle) toggle.style.display = 'inline-block';
            // Verwijder URL-parameter zodat suggesties bij refresh weg zijn
            if (window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.delete('ssil_action');
                window.history.replaceState({}, document.title, url.toString());
            }
        });
    }
});