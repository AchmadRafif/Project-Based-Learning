<?php
require "config.php";

$query = "SELECT menu.*, kategori.nama_kategori 
          FROM menu
          JOIN kategori ON menu.kategori_id = kategori.id
          ORDER BY menu.id DESC";

$result = mysqli_query($conn, $query);

$data = [];

while($row = mysqli_fetch_assoc($result)){
    $data[] = $row;
}

echo json_encode($data);
?>
