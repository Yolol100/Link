document.addEventListener('DOMContentLoaded', function() {
  // Functie om bulk-delete knop te tonen/verborgen op basis van URL aanwezigheid in kolom 2 (index 2)
  function checkKeywordsTable() {
    const rows = document.querySelectorAll('#ssil-keywords-table tbody tr');
    let showButton = false;

    rows.forEach(row => {
      const urlCell = row.cells[2]; // kolom 2 = URL
      if (!urlCell) return;
      const link = urlCell.querySelector('a');
      if (link && link.textContent.trim() !== '') {
        showButton = true;
      }
    });

    const bulkDeleteBtn = document.getElementById('ssil-bulk-delete');
    if (bulkDeleteBtn) {
      bulkDeleteBtn.style.display = showButton ? '' : 'none';
    }
  }

  // Voer check uit bij laden
  checkKeywordsTable();

  // --- Jouw bestaande functionaliteit --- //

  // Toast functionaliteit met fade-in-up animatie
  window.showToast = window.showToast || function(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `ssil-toast ssil-toast-${type} ssil-toast-animate`;
    toast.innerText = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('ssil-toast-hide'), 2700);
    setTimeout(() => toast.remove(), 3200);
  };

  // Zoek naar Yoast-alert en vervang door toast
  const yoastAlert = document.querySelector('.yoast-alert-success');
  if (yoastAlert) {
    const msg = yoastAlert.textContent || yoastAlert.innerText;
    showToast(msg, 'success');
    yoastAlert.remove();
  }

  // Keyword & URL validatie + submit knop aan/uit
  const keywordInput = document.querySelector('input[name="new_word"]');
  const urlInput = document.querySelector('input[name="new_url"]');
  const submitBtn = document.querySelector('.ssil-submit-btn') || document.querySelector('.yoast-button-primary');

  if (keywordInput && urlInput) {
    const updateState = () => {
      if (submitBtn) submitBtn.disabled = !keywordInput.value.trim() || !urlInput.value.trim();
    };

    const validateUrl = (input) => {
      const urlPattern = /^https?:\/\/.+/;
      if (!input.value.trim()) {
        removeInlineError(input);
        input.classList.remove('ssil-error');
        return;
      }
      if (!urlPattern.test(input.value)) {
        input.classList.add('ssil-error');
        showInlineError(input, 'Geen geldige URL.');
        if (typeof showToast === 'function') {
          showToast('Vul een geldige URL in.', 'error');
        } else {
          alert('Vul een geldige URL in.');
        }
      } else {
        input.classList.remove('ssil-error');
        removeInlineError(input);
      }
    };

    const showInlineError = (input, msg) => {
      let error = input.parentNode.querySelector('.ssil-inline-error');
      if (!error) {
        error = document.createElement('div');
        error.className = 'ssil-inline-error';
        input.parentNode.appendChild(error);
      }
      error.innerText = msg;
    };

    const removeInlineError = (input) => {
      const error = input.parentNode.querySelector('.ssil-inline-error');
      if (error) error.remove();
    };

    [keywordInput, urlInput].forEach(inp => {
      inp.addEventListener('input', () => {
        updateState();
        if (inp === urlInput) validateUrl(urlInput);
      });
    });

    updateState();
  }

  // Highlight hover in logs-tabel
  const table = document.getElementById('ssil-logs-table');
  if (table) {
    table.addEventListener('mouseover', e => {
      const row = e.target.closest('tr');
      if (row) row.classList.add('ssil-row-hover');
    });
    table.addEventListener('mouseout', e => {
      const row = e.target.closest('tr');
      if (row) row.classList.remove('ssil-row-hover');
    });
  }

  // Rapportage tabel kopiëren
  const copyBtn = document.getElementById('ssil-copy-report');
  if (copyBtn) {
    copyBtn.addEventListener('click', () => {
      const reportTable = document.getElementById('ssil-report-table');
      if (!reportTable) return;
      let txt = '';
      for (const row of reportTable.rows) {
        const vals = Array.from(row.cells).map(cell => cell.textContent.trim());
        txt += vals.join('\t') + '\n';
      }
      navigator.clipboard.writeText(txt).then(
        () => showToast('Rapportage gekopieerd!', 'success'),
        () => showToast('Kopiëren mislukt.', 'error')
      );
    });
  }

  // Suggesties toggle en sluiten
  const suggestWrap = document.getElementById('ssil-suggest-wrap');
  const closeBtn = document.getElementById('ssil-suggest-close');
  const toggles = document.querySelectorAll('#ssil-suggest-toggle, .yoast-btn-suggest');

  toggles.forEach(toggle => {
    toggle.addEventListener('click', e => {
      e.preventDefault();
      if (toggle.href) {
        window.location = toggle.href + (toggle.href.includes('?') ? '&' : '?') + 'ssil_action=suggest_keywords';
      }
    });
  });

  if (closeBtn && suggestWrap) {
    closeBtn.addEventListener('click', () => {
      suggestWrap.style.display = 'none';
      toggles.forEach(toggle => toggle.style.display = 'inline-block');
      if (window.history.replaceState) {
        const url = new URL(window.location.href);
        url.searchParams.delete('ssil_action');
        window.history.replaceState({}, document.title, url.toString());
      }
    });
  }

  // Inline editing - waarschuwing bij dubbele edits
  document.querySelectorAll('.yoast-btn-edit').forEach(editBtn => {
    editBtn.addEventListener('click', e => {
      if (document.querySelector('.ssil-row-edit')) {
        showToast('Rond eerst je huidige bewerking af.', 'warning');
        e.preventDefault();
        return false;
      }
    });
  });

  // Live URL validatie inline edit forms
  document.querySelectorAll('.ssil-inline-edit-form input[type="url"]').forEach(urlInp => {
    urlInp.addEventListener('input', () => {
      const urlPattern = /^https?:\/\/.+/;
      if (!urlPattern.test(urlInp.value.trim())) {
        urlInp.classList.add('ssil-error');
        if (typeof showToast === 'function') {
          showToast('Vul een geldige URL in.', 'error');
        }
      } else {
        urlInp.classList.remove('ssil-error');
      }
    });
  });
});