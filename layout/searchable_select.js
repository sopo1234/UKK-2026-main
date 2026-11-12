// Semua <select> memakai Select2 supaya bisa dicari, panah dropdown tetap ada.
// Dimuat lewat layout/searchable_select.php.
(function () {
    var jqueryUrl = 'https://code.jquery.com/jquery-3.7.1.min.js';
    var select2Url = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';

    function loadScript(src, next) {
        var s = document.createElement('script');
        s.src = src;
        s.onload = next;
        document.head.appendChild(s);
    }

    function init() {
        var $ = window.jQuery;
        if (!$ || !$.fn.select2) return;

        $(function () {
            $('select').each(function () {
                var $el = $(this);
                if ($el.data('select2')) return;

                $el.select2({
                    width: '100%',
                    // Opsi kosong pertama dipakai sebagai placeholder.
                    placeholder: $el.find('option[value=""]').first().text() || '',
                    language: {
                        noResults: function () { return 'Data tidak ditemukan'; }
                    }
                });
            });
        });
    }

    if (!window.jQuery) {
        loadScript(jqueryUrl, function () { loadScript(select2Url, init); });
    } else if (!window.jQuery.fn.select2) {
        loadScript(select2Url, init);
    } else {
        init();
    }
})();
