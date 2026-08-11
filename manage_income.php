<?php
include('header.php');
checkUser();
userArea();

$msg="";
$source="";
$amount="";
$details="";
$income_date=date('Y-m-d');
$label="Add";


if(isset($_GET['id']) && $_GET['id']>0){

    $label="Edit";

    $id=get_safe_value($_GET['id']);

    $res=mysqli_query($con,"select * from income where id=$id");

    if(mysqli_num_rows($res)==0){
        redirect('income.php');
        die();
    }

    $row=mysqli_fetch_assoc($res);

    if($row['added_by']!=$_SESSION['UID']){
        redirect('income.php');
        die();
    }

    $source=$row['source'];
    $amount=$row['amount'];
    $details=$row['details'];
    $income_date=$row['income_date'];
}


if(isset($_POST['submit'])){

    $source=get_safe_value($_POST['source']);
    $amount=get_safe_value($_POST['amount']);
    $details=get_safe_value($_POST['details']);
    $income_date=get_safe_value($_POST['income_date']);

    $added_on=date('Y-m-d H:i:s');
    $added_by=$_SESSION['UID'];


    if(isset($_GET['id']) && $_GET['id']>0){

        mysqli_query($con,"update income set
        source='$source',
        amount='$amount',
        details='$details',
        income_date='$income_date'
        where id=$id");

    }else{

        mysqli_query($con,"insert into income
        (source,amount,details,added_on,income_date,added_by)
        values
        ('$source','$amount','$details','$added_on','$income_date','$added_by')");
    }

    redirect('income.php');
}


include('user_header.php');
?>


<style>

.form-box{
    width:650px;
    max-width:95%;
    margin:30px auto;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

.form-box h2{
    margin-bottom:20px;
    text-align:center;
}

.form-group{
    margin-bottom:15px;
}

.form-group label{
    display:block;
    margin-bottom:6px;
    font-size:22px;
    font-weight:bold;
}

.form-group input,
.form-group textarea{
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:6px;
    box-sizing:border-box;
    font-size:20px;
}

.form-group textarea{
    resize:vertical;
    height:100px;
}

.btn{
    background:#0d6efd;
    color:#fff;
    border:none;
    padding:12px 20px;
    border-radius:6px;
    cursor:pointer;
    font-size:16px;
}

.btn:hover{
    background:#0b5ed7;
}

.back{
    display:inline-block;
    margin-bottom:20px;
    text-decoration:none;
}

.date-box{
    position:relative;
}

.date-box input{
    width:100%;
    height:55px;
    padding:0 50px 0 15px;
    font-size:20px;
    font-weight:bold;
    border:1px solid #ccc;
    border-radius:8px;
    cursor:pointer;
}

.date-box i{
    position:absolute;
    right:15px;
    top:50%;
    transform:translateY(-50%);
    font-size:22px;
    color:#0d6efd;
    pointer-events:none;
}

</style>


<div class="form-box">


<h2>
    💰 <?php echo $label; ?> Income
</h2>


<a class="back" href="income.php">
    ← Back
</a>


<form method="post">


    <div class="form-group">

        <label>Income Source</label>

        <input
            type="text"
            name="source"
            required
            placeholder="Enter income source"
            value="<?php echo $source; ?>"
        >

    </div>


    <div class="form-group">

        <label>Amount</label>

        <input
            type="number"
            name="amount"
            step="0.01"
            min="0"
            required
            placeholder="Enter amount"
            value="<?php echo $amount; ?>"
        >

    </div>


    <div class="form-group">

        <label>Details</label>

        <textarea
            name="details"
            required
            placeholder="Enter income details"><?php echo $details; ?></textarea>

    </div>


    <div class="form-group">

        <label>Income Date</label>

        <div class="date-box">

            <input
                type="text"
                name="income_date"
                required
                readonly
                value="<?php echo $income_date; ?>"
            >

            <i class="fa-solid fa-calendar-days"></i>

        </div>

    </div>


    <input
        type="submit"
        name="submit"
        value="<?php echo $label; ?> Income"
        class="btn"
    >


</form>


<?php

if($msg!=""){

    echo "<p style='color:red'>$msg</p>";

}

?>


</div>


<?php
include('footer.php');
?>