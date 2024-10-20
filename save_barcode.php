<?php
// Koneksi ke database
$servername = "localhost";
$username = "root"; // Username database
$password = "";     // Password database
$dbname = "test"; // Nama database

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil data dari request AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rider_name = $_POST['rider_name'];
    $barcode = $_POST['barcode'];

    // Query untuk memasukkan data ke dalam tabel
    $sql = "INSERT INTO scanned_barcodes (rider_name, barcode) VALUES ('$rider_name', '$barcode')";

    if ($conn->query($sql) === TRUE) {
        // Ambil ID dan waktu scan terbaru
        $last_id = $conn->insert_id;
        $scanned_at = date('Y-m-d H:i:s'); // Format waktu sekarang

        // Kembalikan data sebagai JSON
        $response = array(
            'id' => $last_id,
            'rider_name' => $rider_name,
            'barcode' => $barcode,
            'scanned_at' => $scanned_at
        );

        echo json_encode($response); // Kirim respons dalam bentuk JSON
    } else {
        echo json_encode(array('error' => 'Error saving data'));
    }

    // Tutup koneksi
    $conn->close();
}
?>
