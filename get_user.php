<?php
// Koneksi ke database
$servername = "localhost";
$username = "root"; // Username database
$password = "";     // Password database
$dbname = "test"; // Nama database
// Koneksi ke database
$mysqli = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Query untuk mengambil username dari tabel users
$query = "SELECT employ_username FROM users";
$result = $mysqli->query($query);

$users = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row['employ_username'];
    }
}

// Mengirimkan data ke client-side dalam format JSON
echo json_encode($users);

// Tutup koneksi
$mysqli->close();
?>
