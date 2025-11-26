<?php
require "config.php";

$nama   = $_POST['nama_menu'];
$kategori = $_POST['kategori_id'];
$harga  = $_POST['harga'];

$fotoName = null;

if(isset($_FILES['foto_menu']) && $_FILES['foto_menu']['error'] === 0){
    $ext = pathinfo($_FILES['foto_menu']['name'], PATHINFO_EXTENSION);
    $fotoName = "menu_" . time() . "." . $ext;
    move_uploaded_file($_FILES['foto_menu']['tmp_name'], "uploads/" . $fotoName);
}

$query = "INSERT INTO menu (nama_menu, kategori_id, harga, foto_menu)
          VALUES ('$nama', '$kategori', '$harga', '$fotoName')";

if(mysqli_query($conn, $query)){
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
}
?>
