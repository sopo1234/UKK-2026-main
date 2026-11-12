<?php
// Dropdown dengan kolom pencarian (Select2) untuk semua <select>.
// Cukup di-include sekali per halaman; sidebar sudah memuat ini.
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
    .select2-container { width: 100% !important; }
    /* Agar dropdown tetap tampil di atas modal Bootstrap */
    .select2-container--open { z-index: 99999; }
</style>
<script src="layout/searchable_select.js" defer></script>
