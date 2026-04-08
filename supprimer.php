<?php
$conn = new mysqli("localhost", "root", "", "gestion_client");

if(isset($_GET['id'])){

    $id = $_GET['id'];

    // نحذف الصورة من folder
    $result = $conn->query("SELECT image FROM client WHERE code_client=$id");
    $row = $result->fetch_assoc();

    if($row['image']){
        unlink("images/".$row['image']);
    }

    // نحذف من database
    $conn->query("DELETE FROM client WHERE code_client=$id");

    header("Location: index.php");
}
?>