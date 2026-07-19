document.addEventListener("DOMContentLoaded", function() {
    
    // ==========================================
    // 1. FITUR AUTO FORMAT RUPIAH
    // ==========================================
    const inputJumlah = document.getElementById('inputJumlah');
    if (inputJumlah) {
        inputJumlah.addEventListener('keyup', function(e) {
            this.value = formatRupiah(this.value);
        });
    }

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

    // ==========================================
    // 2. FITUR GANTI KATEGORI DINAMIS (SOLUSI BUG)
    // ==========================================
    const dataKategori = {
        "Pengeluaran": [
            { nama: "Kebutuhan Pokok", ikon: "bi-cart3" },
            { nama: "Transportasi", ikon: "bi-scooter" },
            { nama: "Kesehatan", ikon: "bi-capsule" },
            { nama: "Hiburan", ikon: "bi-film" },
            { nama: "Belanja", ikon: "bi-bag-heart" },
            { nama: "Lainnya", ikon: "bi-box-seam" }
        ],
        "Pemasukan": [
            { nama: "Gaji", ikon: "bi-wallet-fill" },
            { nama: "Bonus", ikon: "bi-gift" },
            { nama: "Investasi", ikon: "bi-graph-up-arrow" },
            { nama: "Freelance", ikon: "bi-laptop" },
            { nama: "Hadiah", ikon: "bi-envelope-paper-heart" },
            { nama: "Lainnya", ikon: "bi-box-seam" }
        ]
    };

    const radioJenis = document.querySelectorAll('input[name="jenis"]');
    const wadahKategori = document.getElementById('wadahKategori');

    radioJenis.forEach(function(radio) {
        radio.addEventListener('change', function() {
            renderKategori(this.value);
        });
    });

    function renderKategori(jenis) {
        if (!wadahKategori || !dataKategori[jenis]) return;
        
        wadahKategori.innerHTML = ""; // Kosongkan kategori lama
        
        dataKategori[jenis].forEach(function(item, index) {
            let isChecked = (index === 0) ? 'checked' : ''; // Otomatis pilih kotak pertama
            let idKategori = 'kat_' + jenis + '_' + index;
            
            let htmlBox = `
                <input type="radio" class="btn-check" name="kategori" id="${idKategori}" value="${item.nama}" ${isChecked}>
                <label class="btn btn-kategori-box d-flex flex-column align-items-center" for="${idKategori}">
                    <i class="bi ${item.ikon} fs-4 mb-1"></i>
                    <span style="font-size: 0.7rem;">${item.nama}</span>
                </label>
            `;
            
            wadahKategori.insertAdjacentHTML('beforeend', htmlBox);
        });
    }

    // ==========================================
    // 3. KALENDER INTERAKTIF (OTOMATIS HARI INI)
    // ==========================================
    const bulanTeks = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    let tanggalSekarang = new Date();
    let bulanAktif = tanggalSekarang.getMonth();
    let tahunAktif = tanggalSekarang.getFullYear();
    
    const inputTanggal = document.getElementById('inputTanggal');
    let tanggalDipilih = tanggalSekarang.toISOString().split('T')[0];
    if (inputTanggal) {
        inputTanggal.value = tanggalDipilih;
    }

    function renderKalender(bulan, tahun) {
        document.getElementById('bulanTahunTeks').innerText = bulanTeks[bulan] + " " + tahun;
        const kalenderAngka = document.getElementById('kalenderAngka');
        kalenderAngka.innerHTML = "";

        let hariPertama = new Date(tahun, bulan, 1).getDay();
        let totalHari = new Date(tahun, bulan + 1, 0).getDate();

        for (let i = 0; i < hariPertama; i++) {
            let emptyDiv = document.createElement("div");
            emptyDiv.className = "calendar-day empty-day";
            kalenderAngka.appendChild(emptyDiv);
        }

        for (let hari = 1; hari <= totalHari; hari++) {
            let dayDiv = document.createElement("div");
            dayDiv.className = "calendar-day";
            dayDiv.innerText = hari;

            let formatBulan = String(bulan + 1).padStart(2, '0');
            let formatHari  = String(hari).padStart(2, '0');
            let stringTanggalIni = `${tahun}-${formatBulan}-${formatHari}`;

            if (stringTanggalIni === tanggalDipilih) {
                dayDiv.classList.add("active-day");
            }

            dayDiv.addEventListener("click", function() {
                document.querySelectorAll('.calendar-day').forEach(el => el.classList.remove('active-day'));
                this.classList.add("active-day");
                
                tanggalDipilih = stringTanggalIni;
                inputTanggal.value = tanggalDipilih;
            });

            kalenderAngka.appendChild(dayDiv);
        }
    }

    renderKalender(bulanAktif, tahunAktif);

    document.getElementById('btnPrevMonth').addEventListener('click', function() {
        bulanAktif--;
        if (bulanAktif < 0) { bulanAktif = 11; tahunAktif--; }
        renderKalender(bulanAktif, tahunAktif);
    });

    document.getElementById('btnNextMonth').addEventListener('click', function() {
        bulanAktif++;
        if (bulanAktif > 11) { bulanAktif = 0; tahunAktif++; }
        renderKalender(bulanAktif, tahunAktif);
    });

});