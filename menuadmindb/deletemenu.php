<?php
require "config.php";

if(isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Ambil nama file foto untuk dihapus
    $queryFoto = "SELECT foto_menu FROM menu WHERE id = '$id'";
    $result = mysqli_query($conn, $queryFoto);
    $row = mysqli_fetch_assoc($result);
    
    // Hapus file foto jika ada (sesuaikan dengan folder img/MenuTaki/)
    if($row && $row['foto_menu'] && file_exists("img/MenuTaki/" . $row['foto_menu'])) {
        unlink("img/MenuTaki/" . $row['foto_menu']);
    }
    
    // Hapus data dari database
    $query = "DELETE FROM menu WHERE id = '$id'";
    
    if(mysqli_query($conn, $query)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "ID tidak ditemukan"]);
}
?>