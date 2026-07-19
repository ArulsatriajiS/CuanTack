document.addEventListener("DOMContentLoaded", function() {
    
    // Fitur Auto-Submit saat dropdown Jenis Transaksi diganti
    const selectJenis = document.getElementById('selectJenis');
    if (selectJenis) {
        selectJenis.addEventListener('change', function() {
            this.form.submit();
        });
    }

});