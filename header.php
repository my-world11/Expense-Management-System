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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, Helvetica, sans-serif;
}

body{
    background:#f4f6f9;
}

.header{
    width:100%;
    background:#0d6efd;
    color:#fff;
    padding:18px;
    text-align:center;
    font-size:28px;
    font-weight:bold;
    box-shadow:0 2px 10px rgba(0,0,0,.2);
}

.container{
    width:100%;
    padding:20px;
}

input[type=text],
input[type=email],
input[type=password],
input[type=date],
input[type=number],
select,
textarea{
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:5px;
    outline:none;
}

input[type=submit],
button{
    background:#0d6efd;
    color:white;
    border:none;
    padding:10px 20px;
    border-radius:5px;
    cursor:pointer;
}

input[type=submit]:hover,
button:hover{
    background:#0b5ed7;
}

table{
    width:100%;
    border-collapse:collapse;
}

table td,
table th{
    padding:10px;
}

a{
    color:#0d6efd;
    text-decoration:none;
}

a:hover{
    text-decoration:underline;
}

</style>

</head>

<body>

<div class="header">
    <i class="fa-solid fa-wallet"></i>
    Expense Management System
</div>

<div class="container">