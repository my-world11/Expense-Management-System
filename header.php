<?php
include('config.php');
include('functions.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Expense Management System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<style>

body{
    background:#f4f6f9;
    font-family:Arial, Helvetica, sans-serif;
}

.header{
    background:#0d6efd;
    color:white;
    padding:15px;
    text-align:center;
    font-size:28px;
    font-weight:bold;
    box-shadow:0px 3px 10px rgba(0,0,0,.2);
}

.card{
    border:none;
    border-radius:12px;
    box-shadow:0px 0px 10px rgba(0,0,0,.1);
}

.table{
    background:white;
}

.footer{
    background:#0d6efd;
    color:white;
    text-align:center;
    padding:12px;
    margin-top:30px;
}

</style>

</head>

<body>

<div class="header">
<i class="fa-solid fa-wallet"></i>
Expense Management System
</div>

<div class="container mt-4">