<?php
require "config.php";

$id = $_POST['id'];
$nama = $_POST['nama_menu'];
$kategori = $_POST['kategori_id'];
$harga = $_POST['harga'];

// Cek apakah ada foto baru yang diupload
if(isset($_FILES['foto_menu']) && $_FILES['foto_menu']['error'] === 0) {
    // Hapus foto lama
    $queryFotoLama = "SELECT foto_menu FROM menu WHERE id = '$id'";
    $result = mysqli_query($conn, $queryFotoLama);
    $row = mysqli_fetch_assoc($result);
    
    if($row && $row['foto_menu'] && file_exists("uploads/" . $row['foto_menu'])) {
        unlink("uploads/" . $row['foto_menu']);
    }
    
    // Upload foto baru
    $ext = pathinfo($_FILES['foto_menu']['name'], PATHINFO_EXTENSION);
    $fotoName = "menu_" . time() . "." . $ext;
    move_uploaded_file($_FILES['foto_menu']['tmp_name'], "uploads/" . $fotoName);
    
    $query = "UPDATE menu SET 
              nama_menu = '$nama',
              kategori_id = '$kategori',
              harga = '$harga',
              foto_menu = '$fotoName'
              WHERE id = '$id'";
} else {
    // Update tanpa mengubah foto
    $query = "UPDATE menu SET 
              nama_menu = '$nama',
              kategori_id = '$kategori',
              harga = '$harga'
              WHERE id = '$id'";
}

if(mysqli_query($conn, $query)) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
}
?>