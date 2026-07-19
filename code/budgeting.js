document.addEventListener("DOMContentLoaded", function() {
    
    // 1. FITUR AUTO FORMAT RUPIAH DI INPUT ANGKA
    const inputRupiahList = document.querySelectorAll('.input-rupiah');
    inputRupiahList.forEach(function(input) {
        input.addEventListener('keyup', function(e) {
            this.value = formatRupiah(this.value);
            // Jika yang diketik pengeluaran, otomatis update hitungan darurat di form
            if (this.id === 'inputPengeluaran') {
                hitungDarurat();
            }
        });
    });

    function formatRupiah(angka) {
        let number_string = angka.replace(/[^,\d]/g, '').toString(),
            split   = number_string.split(','),
            sisa    = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa),
            ribuan  = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    // Hitung dana darurat saat pertama kali load
    hitungDarurat();
});

// 2. PILIH BULAN DANA DARURAT
function pilihBulan(element, bulan) {
    const pills = document.querySelectorAll('.btn-option-pill');
    pills.forEach(p => p.classList.remove('active'));
    element.classList.add('active');
    
    const inputHidden = document.getElementById('inputBulanDarurat');
    if (inputHidden) {
        inputHidden.value = bulan;
    }
    
    hitungDarurat();
}

// 3. KALKULASI DANA DARURAT DI FORMULIR
function hitungDarurat() {
    const inputPengeluaran = document.getElementById('inputPengeluaran');
    const inputBulan = document.getElementById('inputBulanDarurat');
    const teksDarurat = document.getElementById('teksDanaDarurat');

    if (inputPengeluaran && inputBulan && teksDarurat) {
        let nilaiBersih = inputPengeluaran.value.replace(/\./g, '');
        const pengeluaran = parseFloat(nilaiBersih) || 0;
        const bulan = parseInt(inputBulan.value) || 3;
        const total = pengeluaran * bulan;

        teksDarurat.innerText = "Dana darurat " + bulan + " Bulan: Rp " + total.toLocaleString('id-ID');
    }
}