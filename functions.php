<?php

function prx($data){
    echo '<pre>';
    print_r($data);
    die();
}


function get_safe_value($data){
    global $con;

    if($data){
        return mysqli_real_escape_string($con, $data);
    }

    return $data;
}


function redirect($link){
    ?>
    <script>
        window.location.href="<?php echo $link; ?>";
    </script>
    <?php
}


/* =========================================================
   CHECK USER LOGIN
   ========================================================= */

function checkUser(){

    if(isset($_SESSION['UID']) && $_SESSION['UID'] != ''){

        // User is logged in.
        // Suspension/status checking removed because
        // status and suspend_until columns are no longer used.

    }else{

        redirect('index.php');

    }
}


/* =========================================================
   CATEGORY DROPDOWN
   ========================================================= */

function getCategory($category_id='', $page=''){

    global $con;

    $res = mysqli_query(
        $con,
        "select * from category order by name asc"
    );

    $fun = "required";

    if($page == 'reports'){
        $fun = "";
    }

    $html = '<select '.$fun.' name="category_id" id="category_id">';

    $html .= '<option value="">Select Category</option>';

    while($row = mysqli_fetch_assoc($res)){

        if($category_id > 0 && $category_id == $row['id']){

            $html .= '<option value="'.$row['id'].'" selected>'
                   .htmlspecialchars($row['name']).
                   '</option>';

        }else{

            $html .= '<option value="'.$row['id'].'">'
                   .htmlspecialchars($row['name']).
                   '</option>';

        }
    }

    $html .= '</select>';

    return $html;
}


/* =========================================================
   DASHBOARD EXPENSE
   ========================================================= */

function getDashboardExpense($type){

    global $con;

    $today = date('Y-m-d');

    if($type == 'today'){

        $sub_sql = "and expense_date='$today' ";
        $from = $today;
        $to = $today;

    }
    else if($type == 'yesterday'){

        $yesterday = date(
            'Y-m-d',
            strtotime('yesterday')
        );

        $sub_sql = "and expense_date='$yesterday' ";
        $from = $yesterday;
        $to = $yesterday;

    }
    else if(
        $type == 'week' ||
        $type == 'month' ||
        $type == 'year'
    ){

        $from = date(
            'Y-m-d',
            strtotime("-1 $type")
        );

        $sub_sql = "and expense_date between '$from' and '$today' ";

        $to = $today;

    }
    else{

        $sub_sql = " ";
        $from = '';
        $to = '';

    }


    $res = mysqli_query(
        $con,
        "select sum(price) as price
         from expense
         where added_by='".$_SESSION['UID']."'
         $sub_sql"
    );

    $row = mysqli_fetch_assoc($res);

    $p = 0;
    $link = "";

    if($row['price'] > 0){

        $p = $row['price'];

        $link = "&nbsp;&nbsp;&nbsp;
        <a href='dashboard_report.php?from=".$from."&to=".$to."'>
        Details
        </a>";

    }

    return $p.$link;
}


/* =========================================================
   ADMIN AREA
   ========================================================= */

function adminArea(){

    if($_SESSION['UROLE'] != 'Admin'){

        redirect('dashboard.php');

    }
}


/* =========================================================
   USER AREA
   ========================================================= */

function userArea(){

    if($_SESSION['UROLE'] != 'User'){

        redirect('category.php');

    }
}


/* =========================================================
   DASHBOARD INCOME
   ========================================================= */

function getDashboardIncome($type){

    global $con;

    $today = date('Y-m-d');

    if($type == 'today'){

        $sub_sql = "and income_date='$today' ";

    }
    else if($type == 'yesterday'){

        $yesterday = date(
            'Y-m-d',
            strtotime('yesterday')
        );

        $sub_sql = "and income_date='$yesterday' ";

    }
    else if(
        $type == 'week' ||
        $type == 'month' ||
        $type == 'year'
    ){

        $from = date(
            'Y-m-d',
            strtotime("-1 $type")
        );

        $sub_sql = "and income_date between '$from' and '$today' ";

    }
    else{

        $sub_sql = "";

    }


    $res = mysqli_query(
        $con,
        "select sum(amount) as amount
         from income
         where added_by='".$_SESSION['UID']."'
         $sub_sql"
    );

    $row = mysqli_fetch_assoc($res);


    if($row['amount'] == ""){

        return 0;

    }else{

        return $row['amount'];

    }
}


/* =========================================================
   SAVING
   ========================================================= */

function getSaving(){

    global $con;

    $income = 0;
    $expense = 0;


    /* Total Income */

    $res = mysqli_query(
        $con,
        "select sum(amount) as total_income
         from income
         where added_by='".$_SESSION['UID']."'"
    );

    $row = mysqli_fetch_assoc($res);

    if($row['total_income'] != ""){

        $income = $row['total_income'];

    }


    /* Total Expense */

    $res = mysqli_query(
        $con,
        "select sum(price) as total_expense
         from expense
         where added_by='".$_SESSION['UID']."'"
    );

    $row = mysqli_fetch_assoc($res);

    if($row['total_expense'] != ""){

        $expense = $row['total_expense'];

    }


    return $income - $expense;
}

?>