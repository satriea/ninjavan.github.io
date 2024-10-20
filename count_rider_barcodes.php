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

// Query untuk menghitung jumlah scan per rider
$sql = "SELECT rider_name, COUNT(*) AS total FROM scanned_barcodes WHERE DATE(scanned_at)=CURDATE()GROUP BY rider_name ORDER BY scanned_at DESC";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode([]);
}

// Tutup koneksi
$conn->close();
?>
