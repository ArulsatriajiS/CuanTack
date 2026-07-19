document.addEventListener("DOMContentLoaded", function() {
    // Mengambil elemen navbar berdasarkan ID
    const navbar = document.getElementById("mainNavbar");

    // Mendengarkan aktivitas scroll pada halaman
    window.addEventListener("scroll", function() {
        // Jika halaman di-scroll lebih dari 50px ke bawah
        if (window.scrollY > 50) {
            // Tambahkan class navbar-scrolled (navbar jadi transparan)
            navbar.classList.add("navbar-scrolled");
        } else {
            // Jika kembali ke posisi paling atas, hapus class tersebut (navbar kembali solid)
            navbar.classList.remove("navbar-scrolled");
        }
    });
});