
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}

body {
    background: #f4f6f9;
}

.header {
    width: 100%;
    min-height: 65px;
    background: #0d6efd;
    color: #fff;
    padding: 15px 20px;
    text-align: center;
    font-size: 28px;
    font-weight: bold;
    box-shadow: 0 2px 10px rgba(0,0,0,.2);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.header-title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.header-profile {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
}

.header-profile a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    color: #0d6efd;
    padding: 10px 15px;
    border-radius: 7px;
    font-size: 14px;
    font-weight: bold;
    text-decoration: none;
    transition: 0.2s;
}

.header-profile a:hover {
    background: #e8f1ff;
    transform: translateY(-2px);
}

.container {
    width: 100%;
    padding: 20px;
}

input[type=text],
input[type=email],
input[type=password],
input[type=number],
textarea {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    outline: none;
    font-size: 18px;
}

input[type=date] {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    outline: none;
    font-size: 18px;
}

select {
    width: 100%;
    height: 55px;
    padding: 12px 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 18px;
    background: #fff;
}

input[type=submit],
button {
    background: #0d6efd;
    color: #fff;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 18px;
}

input[type=submit]:hover,
button:hover {
    background: #0b5ed7;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th,
table td {
    border: 1px solid #ddd;
    padding: 10px;
}

a {
    text-decoration: none;
    color: #0d6efd;
}

/* Tom Select */

.ts-control {
    min-height: 55px;
    font-size: 20px;
    border-radius: 8px !important;
}

.ts-dropdown {
    font-size: 20px;
}

.ts-dropdown .option {
    padding: 12px;
}

/* Flatpickr */

.flatpickr-calendar {
    font-size: 18px;
}

.flatpickr-current-month {
    font-size: 18px;
}

.flatpickr-monthDropdown-months {
    font-size: 18px !important;
}

.flatpickr-weekday {
    font-size: 16px;
}

.flatpickr-day {
    height: 42px;
    line-height: 42px;
    font-size: 18px;
}

/* Dropdown Arrow */

.ts-wrapper.single .ts-control {
    position: relative;
    padding-right: 45px !important;
}

.ts-wrapper.single .ts-control::after {
    content: "\f107";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #555;
    font-size: 18px;
    pointer-events: none;
}

/* Calendar Icon */

.date-box {
    position: relative;
}

.date-box i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #0d6efd;
    font-size: 20px;
    pointer-events: none;
}

/* Mobile Header */

@media(max-width:650px) {

    .header {
        flex-direction: column;
        gap: 12px;
        padding: 15px;
        font-size: 21px;
    }

    .header-profile {
        position: static;
        transform: none;
    }

}

</style>

<?php
// Load page-specific CSS only on the category page
if (basename($_SERVER['PHP_SELF']) === 'category.php') {
?>
<link rel="stylesheet" href="admin_category.css?v=3">
<?php
}
?>

</head>

<body>

<div class="header">

    <div class="header-title">
        <i class="fa-solid fa-wallet"></i>
        Expense Management System
    </div>

    <?php if(isset($_SESSION['UID']) && $_SESSION['UID'] != '') { ?>

        <div class="header-profile">

            <a href="change_email.php">
                <i class="fa-solid fa-envelope"></i>
                Change Gmail
            </a>

        </div>

    <?php } ?>

</div>

<div class="container">
