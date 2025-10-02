<?php
require_once("connection.php");

if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $query = "DELETE FROM budgets WHERE id = '$id'";
  mysqli_query($conn, $query);

  header("Location: budget_list.php");
  exit;
}
?>