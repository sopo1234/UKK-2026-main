// Filter input angka: dipakai NIS, NISN, dan NIP.
// Kolom yang dijaga: semua input dengan inputmode="numeric".
// Huruf atau simbol apa pun akan langsung dibuang saat diketik maupun ditempel.
document.addEventListener('DOMContentLoaded', function () {
    var inputs = document.querySelectorAll('input[inputmode="numeric"]');

    inputs.forEach(function (input) {
        function bersihkan() {
            if (/[^\d]/.test(input.value)) {
                input.value = input.value.replace(/\D+/g, '');
            }
        }

        // Ketikan langsung
        input.addEventListener('input', bersihkan);

        // Paste (juga menjaga batas maxlength)
        input.addEventListener('paste', function (e) {
            var teks = (e.clipboardData || window.clipboardData).getData('text') || '';
            if (/\D/.test(teks)) {
                e.preventDefault();
                var awal = input.selectionStart || 0;
                var akhir = input.selectionEnd || 0;
                var nilai = input.value.substring(0, awal) +
                            teks.replace(/\D+/g, '') +
                            input.value.substring(akhir);
                input.value = nilai;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    });
});
