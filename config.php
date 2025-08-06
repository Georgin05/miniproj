<?php 

$conn=new mysqli("localhost","root","","warehouse");
if($conn->connect_error){
    die("connection failed".$conn->connect_error);
}
?>
