<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Scanner with Auto Save and Display</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Batas tinggi untuk membuat tabel bisa di-scroll */
        #riderScanCountsWrapper {
            max-height: 350px; /* Tentukan tinggi maksimal */
            overflow-y: auto;  /* Tambahkan scroll vertikal jika melebihi batas tinggi */
        }
        #scannedTableWrapper {
            max-height: 150px; /* Tentukan tinggi maksimal */
            overflow-y: auto;  /* Tambahkan scroll vertikal jika melebihi batas tinggi */
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-2">
        <!-- Toast Notification -->
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
            <div id="messageToast" class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <!-- Pesan akan dimunculkan di sini -->
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <!-- Dashboard - Total Scan per Rider (di bawah form dan tabel) -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header">
                    Dashboard - Total Scan per Rider
                    <span id="totalScanCount" class="float-end"></span> <!-- Menampilkan total count di sini -->
                    </div>
                    <div class="card-body">
                        <div class="row" id="riderScanCountsWrapper">
                            <!-- Hasil jumlah scan per rider akan ditampilkan di sini -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row untuk Form Input dan Tabel -->
        <div class="row mb-4">
            <!-- Form untuk Rider dan Barcode (kolom kiri) -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Form Scanned Barcodes</div>
                        <form id="barcodeForm" class="form-control">
                            <!-- Input untuk Nama Rider -->
                            <div class="mb-1">
                                <label for="riderName" class="form-label">Rider Name</label>
                                <select class="form-select" id="riderName" required>
                                    <option value="" disabled selected>Select rider name</option>
                                    <!-- Opsi akan diisi melalui AJAX -->
                                </select>
                            </div>

                            <!-- Input otomatis dari hasil scan barcode -->
                            <div class="mb-1">
                                <label for="barcodeResult" class="form-label">Barcode Scan Result</label>
                                <input type="text" class="form-control" id="barcodeResult" placeholder="Scan barcode here" required>
                            </div>

                            <!-- Tombol Submit -->
                            <button type="submit" class="btn btn-primary" style="display:none;">Submit</button>
                        </form>
                </div>
            </div>

            <!-- Tabel untuk Menampilkan Hasil Scan (kolom kanan) -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Scanned Barcodes</div>
                    <div class="card-body" id="scannedTableWrapper" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-bordered mt-1" style="width: 100%; border-collapse: collapse;">
                            <thead style="position: sticky; top: 0; background-color: white; z-index: 1;">
                                <tr>
                                    <th>#</th>
                                    <th>Rider Name</th>
                                    <th>Barcode</th>
                                    <th>Scanned At</th>
                                </tr>
                            </thead>
                            <tbody id="scannedTable">
                                <!-- Hasil scan akan ditampilkan di sini -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <!-- jQuery untuk AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Script AJAX untuk menyimpan data dan menampilkan hasil di tabel -->
    <script>
$(document).ready(function() {
    var totalScans = 0; // Variabel untuk menyimpan total scans
    // Fokus otomatis ke input barcode saat halaman dimuat
    $('#barcodeResult').focus();

    // Submit form dengan AJAX ketika barcode di-scan
    $('#barcodeForm').on('submit', function(e) {
        e.preventDefault(); // Mencegah submit form biasa

        var riderName = $('#riderName').val();
        var barcodeResult = $('#barcodeResult').val();

        $.ajax({
            url: 'save_barcode.php', // Endpoint untuk menyimpan data
            type: 'POST',
            data: {
                rider_name: riderName,
                barcode: barcodeResult
            },
            success: function(response) {
                // Tampilkan pesan toast di kanan atas
                $('.toast-body').text('Data saved successfully!');
                var toast = new bootstrap.Toast($('#messageToast')); // Inisialisasi Bootstrap toast
                toast.show(); // Tampilkan toast
                
                // Kosongkan input barcode dan fokus ulang
                $('#barcodeResult').val('');
                $('#barcodeResult').focus();  
                
                // Tambahkan data baru ke tabel
                var result = JSON.parse(response); // Response dari PHP harus berupa JSON
                addToTable(result);

                // Update jumlah total scan per rider
                updateRiderScanCounts();
            },
            error: function() {
                // Tampilkan pesan toast dengan error message
                $('.toast-body').text('Error saving data!');
                var toast = new bootstrap.Toast($('#messageToast')); // Inisialisasi toast untuk error
                toast.show(); // Tampilkan toast
            }
        });
    });

    function addToTable(data) {
        // Konversi waktu ke format Indonesia dengan penyesuaian zona waktu (WIB)
        var date = new Date(data.scanned_at); 
        
        // Tambahkan offset waktu 7 jam (UTC+7 untuk WIB)
        date.setHours(date.getHours() + 5);

        // Format tanggal dan waktu secara manual: yyyy-mm-dd hh:mm:ss
        var year = date.getFullYear();
        var month = ('0' + (date.getMonth() + 1)).slice(-2); // Bulan dimulai dari 0, jadi tambahkan 1
        var day = ('0' + date.getDate()).slice(-2);
        var hours = ('0' + date.getHours()).slice(-2);
        var minutes = ('0' + date.getMinutes()).slice(-2);
        var seconds = ('0' + date.getSeconds()).slice(-2);

        var formattedDate = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

        var newRow = `
            <tr>
                <td>${data.id}</td>
                <td>${data.rider_name}</td>
                <td>${data.barcode}</td>
                <td>${formattedDate}</td>
            </tr>
        `;
        $('#scannedTable').prepend(newRow); // Menambahkan data di baris pertama tabel

        // Update total scan count
        totalScans++; // Increment total scan
        $('#totalScanCount').text(`Total Scans: ${totalScans}`); // Update text di header

        // Scroll otomatis ke bawah
        var tableWrapper = document.getElementById('scannedTableWrapper');
        tableWrapper.scrollTop = tableWrapper.scrollHeight;
    }

    // Fungsi untuk mengupdate total scan per rider
    function updateRiderScanCounts() {
        $.ajax({
            url: 'count_rider_barcodes.php', // Endpoint untuk mengambil jumlah total scan per rider
            type: 'GET',
            success: function(response) {
                var data = JSON.parse(response);
                var dashboardHtml = '';
                
                // Loop untuk setiap rider
                data.forEach(function(item) {
                    dashboardHtml += `
                        <div class="col-sm-6 col-md-4 col-lg-3 mb-3">
                            <div class="card text-white bg-info mb-1">
                                <div class="card-header">${item.rider_name}</div>
                                <div class="card-body">
                                    <h2 class="card-title">${item.total}</h2>
                                    <p class="card-text">Total Scans</p>
                                </div>
                            </div>
                        </div>
                    `;
                });

                // Tampilkan hasil ke dashboard
                $('#riderScanCountsWrapper').html(dashboardHtml);

                // Update total scan keseluruhan dari data yang diterima
                var totalScanOverall = data.reduce((sum, item) => sum + parseInt(item.total, 10), 0);
                totalScans = totalScanOverall; // Update total keseluruhan scan
                $('#totalScanCount').text(`Total Scans: ${totalScans}`); // Update text di header

            },
            error: function() {
                console.log('Error retrieving rider scan counts');
            }
        });
    }

    // Lakukan polling untuk memperbarui dashboard secara realtime
    setInterval(updateRiderScanCounts, 5000); // Setiap 5 detik
});

    $(document).ready(function() {
        // Mengambil data dari file PHP melalui AJAX
        $.ajax({
            url: 'get_user.php', // File PHP yang berisi query untuk mengambil users
            type: 'GET',
            success: function(response) {
                var users = JSON.parse(response); // Parsing hasil JSON
                var select = $('#riderName'); // Select element

                // Loop melalui hasil dan tambahkan ke dalam <select>
                users.forEach(function(user) {
                    select.append('<option value="' + user + '">' + user + '</option>');
                });
            },
            error: function() {
                console.log("Error fetching user data");
            }
        });
    });
    </script>
</body>
</html>
