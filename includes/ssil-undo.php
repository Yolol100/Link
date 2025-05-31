<?php
// Bij bulkdelete:
update_option('ssil_links_backup', get_option('ssil_links', []));
// Undo-knop in admin/ssil-admin-page.php:
if (isset($_POST['ssil_undo'])) {
    update_option('ssil_links', get_option('ssil_links_backup', []));
}
