```php
<?php

ob_start();

include('header.php');

checkUser();
adminArea();


/*
==================================================
AUTO ACTIVATE SUSPENDED USERS AFTER 20 DAYS
==================================================
*/

mysqli_query(
    $con,
    "UPDATE users
     SET status='Active',
         suspend_until=NULL
     WHERE role='User'
     AND status='Suspended'
     AND suspend_until IS NOT NULL
     AND suspend_until <= CURDATE()"
);


/*
==================================================
SUSPEND USER
==================================================
*/

if (
    isset($_GET['type']) &&
    $_GET['type'] == 'suspend' &&
    isset($_GET['id']) &&
    (int)$_GET['id'] > 0
) {

    $id = (int)$_GET['id'];

    /*
    20 DAYS SUSPENSION
    */

    $suspend_until = date(
        'Y-m-d',
        strtotime('+20 days')
    );


    /*
    CHECK USER
    */

    $check_user = mysqli_query(
        $con,
        "SELECT id, username, role
         FROM users
         WHERE id='$id'
         AND role='User'"
    );


    if (!$check_user) {

        die(
            "User Check Error: " .
            mysqli_error($con)
        );

    }


    if (mysqli_num_rows($check_user) == 0) {

        die("User not found.");

    }


    /*
    SUSPEND EXACT USER
    */

    $query = mysqli_query(
        $con,
        "UPDATE users
         SET status='Suspended',
             suspend_until='$suspend_until'
         WHERE id='$id'
         AND role='User'"
    );


    if (!$query) {

        die(
            "Suspend Error: " .
            mysqli_error($con)
        );

    }


    header("Location: users.php?suspend=1");

    exit;
}


/*
==================================================
ACTIVATE USER
==================================================
*/

if (
    isset($_GET['type']) &&
    $_GET['type'] == 'active' &&
    isset($_GET['id']) &&
    (int)$_GET['id'] > 0
) {

    $id = (int)$_GET['id'];


    $query = mysqli_query(
        $con,
        "UPDATE users
         SET status='Active',
             suspend_until=NULL
         WHERE id='$id'
         AND role='User'"
    );


    if (!$query) {

        die(
            "Activate Error: " .
            mysqli_error($con)
        );

    }


    header("Location: users.php?activate=1");

    exit;
}


/*
==================================================
SEARCH
==================================================
*/

$search = "";


if (
    isset($_GET['search']) &&
    $_GET['search'] != ""
) {

    $search = get_safe_value(
        $_GET['search']
    );

}


/*
==================================================
NORMAL USERS
ACTIVE USERS ONLY

Inactive is only display based on last login.
It is NOT database suspension.
==================================================
*/

$sql_normal = "
    SELECT *
    FROM users
    WHERE role='User'
    AND (
        status='Active'
        OR status IS NULL
        OR status=''
    )
";


if ($search != "") {

    $sql_normal .= "
        AND username LIKE '%$search%'
    ";

}


$sql_normal .= " ORDER BY id DESC";


$res_normal = mysqli_query(
    $con,
    $sql_normal
);


if (!$res_normal) {

    die(
        "Normal User Error: " .
        mysqli_error($con)
    );

}


/*
==================================================
SUSPENDED USERS
==================================================
*/

$sql_suspend = "
    SELECT *
    FROM users
    WHERE role='User'
    AND status='Suspended'
    AND suspend_until IS NOT NULL
    AND suspend_until > CURDATE()
";


if ($search != "") {

    $sql_suspend .= "
        AND username LIKE '%$search%'
    ";

}


$sql_suspend .= " ORDER BY id DESC";


$res_suspend = mysqli_query(
    $con,
    $sql_suspend
);


if (!$res_suspend) {

    die(
        "Suspended User Error: " .
        mysqli_error($con)
    );

}


include('user_header.php');

?>


<style>

/* =========================================
   CONTAINER
========================================= */

.container{

    margin-left:230px;

    width:calc(100% - 230px);

    padding:25px;

    box-sizing:border-box;

}


/* =========================================
   TITLE
========================================= */

.title{

    font-size:28px;

    font-weight:700;

    color:#263238;

    margin-bottom:20px;

}


/* =========================================
   TOP
========================================= */

.top{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:28px;

    gap:15px;

    flex-wrap:wrap;

}


/* =========================================
   BUTTON
========================================= */

.btn{

    padding:10px 17px;

    text-decoration:none;

    border-radius:5px;

    color:white;

    font-size:15px;

    font-weight:600;

}


.add{

    background:#198754;

}


.add:hover{

    background:#157347;

    color:white;

}


/* =========================================
   SEARCH
========================================= */

.search{

    padding:10px 12px;

    width:220px;

    border:1px solid #ccc;

    border-radius:5px;

    font-size:15px;

    outline:none;

}


.search:focus{

    border-color:#0d6efd;

}


/* =========================================
   SECTION TITLE
========================================= */

.section-title{

    font-size:22px;

    font-weight:700;

    color:#263238;

    margin-bottom:15px;

    margin-top:10px;

}


.section-title i{

    margin-right:8px;

}


/* =========================================
   SUSPENDED SECTION
========================================= */

.suspended-section{

    margin-top:38px;

}


/* =========================================
   TABLE BOX
========================================= */

.table-box{

    width:100%;

    overflow-x:auto;

    margin-bottom:20px;

}


/* =========================================
   TABLE
========================================= */

.user-table{

    width:100%;

    border-collapse:collapse;

    background:white;

    font-size:16px;

}


.user-table th{

    background:#0d6efd;

    color:white;

    padding:13px 10px;

    font-size:17px;

    font-weight:600;

    text-align:center;

    border:1px solid #ddd;

}


.user-table td{

    border:1px solid #ddd;

    padding:12px 10px;

    text-align:center;

    vertical-align:middle;

    height:55px;

}


.user-table tr:hover{

    background:#f7f9fc;

}


/* =========================================
   ACTION
========================================= */

.action{

    display:flex;

    justify-content:center;

    align-items:center;

    gap:18px;

    white-space:nowrap;

}


.edit{

    color:#0d6efd;

    text-decoration:none;

    font-weight:600;

}


.edit:hover{

    color:#084298;

}


.suspend{

    color:#dc3545;

    text-decoration:none;

    font-weight:600;

}


.suspend:hover{

    color:#bb2d3b;

}


.active{

    color:#198754;

    text-decoration:none;

    font-weight:600;

}


.active:hover{

    color:#146c43;

}


/* =========================================
   STATUS
========================================= */

.status-active{

    color:#198754;

    font-weight:600;

    display:inline-flex;

    flex-direction:column;

    align-items:center;

    gap:3px;

}


.status-inactive{

    color:#fd7e14;

    font-weight:600;

    display:inline-flex;

    flex-direction:column;

    align-items:center;

    gap:3px;

}


.status-suspend{

    color:#dc3545;

    font-weight:600;

    display:inline-flex;

    flex-direction:column;

    align-items:center;

    gap:3px;

}


.status-active i,
.status-inactive i,
.status-suspend i{

    font-size:20px;

}


/* =========================================
   SUSPEND UNTIL
========================================= */

.suspend-date{

    color:#dc3545;

    font-weight:600;

}


/* =========================================
   REMAINING
========================================= */

.remaining{

    color:#dc3545;

    font-weight:600;

    line-height:1.5;

}


.remaining-small{

    font-size:13px;

    color:#777;

    display:block;

}


/* =========================================
   INFO
========================================= */

.suspend-info{

    background:#fff5f5;

    border:1px solid #f1b0b7;

    color:#842029;

    padding:15px;

    border-radius:6px;

    margin-top:10px;

    margin-bottom:20px;

}


/* =========================================
   MESSAGE
========================================= */

.success-message{

    background:#d1e7dd;

    color:#0f5132;

    padding:12px 18px;

    border:1px solid #badbcc;

    border-radius:6px;

    margin-bottom:20px;

    font-weight:600;

}


.activate-message{

    background:#cfe2ff;

    color:#084298;

    padding:12px 18px;

    border:1px solid #9ec5fe;

    border-radius:6px;

    margin-bottom:20px;

}


/* =========================================
   NO DATA
========================================= */

.no-data{

    background:white;

    border:1px solid #ddd;

    padding:40px;

    text-align:center;

    font-size:19px;

    color:#777;

}


/* =========================================
   RESPONSIVE
========================================= */

@media(max-width:900px){

    .container{

        margin-left:0;

        width:100%;

        padding:15px;

    }


    .table-box{

        overflow-x:auto;

    }


    .user-table{

        min-width:1000px;

    }

}

</style>


<div class="container">


<!-- =========================================
     PAGE TITLE
========================================= -->

<div class="title">

    <i class="fa-solid fa-users"></i>

    Users Management

</div>


<!-- =========================================
     SUCCESS MESSAGE
========================================= -->

<?php

if(isset($_GET['suspend'])){

?>

<div class="success-message">

    <i class="fa-solid fa-circle-check"></i>

    User suspended successfully and added to
    Suspended Users list.

</div>

<?php

}


if(isset($_GET['activate'])){

?>

<div class="activate-message">

    <i class="fa-solid fa-circle-check"></i>

    User activated successfully and added to
    Users list.

</div>

<?php

}

?>


<!-- =========================================
     TOP
========================================= -->

<div class="top">


<a
    href="manage_user.php"
    class="btn add"
>

    <i class="fa-solid fa-user-plus"></i>

    Add User

</a>


<form method="get">

    <input
        type="text"
        name="search"
        class="search"
        placeholder="Search Username..."
        value="<?php echo htmlspecialchars($search); ?>"
    >

    <input
        type="submit"
        value="Search"
    >

</form>


</div>


<!-- =========================================
     USERS
========================================= -->

<div class="section-title">

    <i class="fa-solid fa-users"></i>

    Users

</div>


<?php

if(mysqli_num_rows($res_normal)>0){

?>


<div class="table-box">

<table class="user-table">


<tr>

    <th>ID</th>

    <th>Username</th>

    <th>Email</th>

    <th>Last Login</th>

    <th>Status</th>

    <th>Action</th>

</tr>


<?php

while($row=mysqli_fetch_assoc($res_normal)){

?>


<tr>


<td>

    <?php echo $row['id']; ?>

</td>


<td>

    <?php

    echo htmlspecialchars(
        $row['username']
    );

    ?>

</td>


<td>

    <?php

    echo htmlspecialchars(
        $row['email']
    );

    ?>

</td>


<td>

<?php

if(
    !empty($row['last_login'])
){

    echo date(
        'd-m-Y h:i A',
        strtotime($row['last_login'])
    );

}
else{

    echo "Never Login";

}

?>

</td>


<td>

<?php

/*
==========================================
DISPLAY ACTIVE / INACTIVE
==========================================

Inactive only means user has not logged in
for more than 7 days.

It does NOT block login.
*/

if(
    !empty($row['last_login']) &&
    strtotime($row['last_login']) >= strtotime('-7 days')
){

?>

<span class="status-active">

    <i class="fa-solid fa-circle-check"></i>

    Active

</span>

<?php

}
else{

?>

<span class="status-inactive">

    <i class="fa-solid fa-circle-xmark"></i>

    Inactive

</span>

<?php

}

?>

</td>


<td>

<div class="action">


<a
    class="edit"
    href="manage_user.php?id=<?php echo $row['id']; ?>"
>

    <i class="fa-solid fa-pen-to-square"></i>

    Edit

</a>


<a
    class="suspend"
    href="users.php?type=suspend&id=<?php echo $row['id']; ?>"
    onclick="return confirm('Are you sure you want to suspend this user for 20 days?');"
>

    <i class="fa-solid fa-ban"></i>

    Suspend

</a>


</div>

</td>


</tr>


<?php

}

?>


</table>

</div>


<?php

}
else{

?>


<div class="no-data">

    <i class="fa-solid fa-folder-open"></i>

    <br><br>

    No User Found

</div>


<?php

}

?>


<!-- =========================================
     SUSPENDED USERS
========================================= -->

<div class="suspended-section">


<div class="section-title">

    <i
        class="fa-solid fa-ban"
        style="color:#dc3545;"
    ></i>

    Suspended Users

</div>


<?php

if(mysqli_num_rows($res_suspend)>0){

?>


<div class="table-box">

<table class="user-table">


<tr>

    <th>ID</th>

    <th>Username</th>

    <th>Email</th>

    <th>Last Login</th>

    <th>Status</th>

    <th>Suspend Until</th>

    <th>Remaining</th>

    <th>Action</th>

</tr>


<?php

while($row=mysqli_fetch_assoc($res_suspend)){

?>


<tr>


<!-- ID -->

<td>

    <?php echo $row['id']; ?>

</td>


<!-- USERNAME -->

<td>

    <?php

    echo htmlspecialchars(
        $row['username']
    );

    ?>

</td>


<!-- EMAIL -->

<td>

    <?php

    echo htmlspecialchars(
        $row['email']
    );

    ?>

</td>


<!-- LAST LOGIN -->

<td>

<?php

if(
    !empty($row['last_login'])
){

    echo date(
        'd-m-Y h:i A',
        strtotime($row['last_login'])
    );

}
else{

    echo "Never Login";

}

?>

</td>


<!-- STATUS -->

<td>

<span class="status-suspend">

    <i class="fa-solid fa-ban"></i>

    Suspended

</span>

</td>


<!-- SUSPEND UNTIL -->

<td>

<span class="suspend-date">

<?php

echo date(
    'd-m-Y',
    strtotime($row['suspend_until'])
);

?>

</span>

</td>


<!-- REMAINING -->

<td>

<?php

$today = new DateTime();

$end = new DateTime(
    $row['suspend_until']
);

if($end > $today){

    $difference = $today->diff($end);

?>

<div class="remaining">

    <?php echo $difference->days; ?> Days Left

    <span class="remaining-small">

        Until
        <?php

        echo date(
            'd-m-Y',
            strtotime($row['suspend_until'])
        );

        ?>

    </span>

</div>

<?php

}
else{

?>

<span class="remaining">

    Expired

</span>

<?php

}

?>

</td>


<!-- ACTION -->

<td>

<div class="action">


<a
    class="edit"
    href="manage_user.php?id=<?php echo $row['id']; ?>"
>

    <i class="fa-solid fa-pen-to-square"></i>

    Edit

</a>


<a
    class="active"
    href="users.php?type=active&id=<?php echo $row['id']; ?>"
    onclick="return confirm('Are you sure you want to activate this user?');"
>

    <i class="fa-solid fa-circle-check"></i>

    Activate

</a>


</div>

</td>


</tr>


<?php

}

?>


</table>

</div>


<div class="suspend-info">

    <i class="fa-solid fa-circle-info"></i>

    Suspended users cannot login until their
    suspension period ends.

    After 20 days, the account automatically becomes
    <strong>Active</strong>.

</div>


<?php

}
else{

?>


<div class="no-data">

    <i class="fa-solid fa-circle-check"></i>

    <br><br>

    No Suspended Users

</div>


<?php

}

?>


</div>


</div>


<?php

include('footer.php');

?>
```
