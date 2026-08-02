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
        (source,amount,details,income_date,added_on,added_by)
        values
        ('$source','$amount','$details','$income_date','$added_on','$added_by')");

    }

    redirect('income.php');
}

include('user_header.php');
?>
<style>

.form-box{
    width:450px;
    margin:30px auto;
    background:#fff;
    border:1px solid #ddd;
    border-radius:8px;
    padding:20px;
}

.form-box h2{
    text-align:center;
    margin-bottom:20px;
}

.form-box label{
    display:block;
    font-weight:bold;
    margin-top:12px;
}

.form-box input,
.form-box textarea{
    width:100%;
    padding:10px;
    margin-top:5px;
    border:1px solid #ccc;
    border-radius:5px;
    box-sizing:border-box;
    font-size:15px;
}

.form-box textarea{
    resize:vertical;
    height:90px;
}

.btn{
    width:100%;
    padding:10px;
    margin-top:20px;
    background:#0d6efd;
    color:#fff;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-size:16px;
}

.btn:hover{
    background:#0954c6;
}

.back{
    text-decoration:none;
}

.error{
    color:red;
    text-align:center;
    margin-top:10px;
    font-weight:bold;
}

</style>

<div class="form-box">

<h2><?php echo $label; ?> Income</h2>

<a class="back" href="income.php">← Back</a>

<form method="post">

<label>Income Source</label>
<input
type="text"
name="source"
required
value="<?php echo $source; ?>">

<label>Amount</label>
<input
type="number"
step="0.01"
name="amount"
required
value="<?php echo $amount; ?>">

<label>Details</label>
<textarea
name="details"
required><?php echo $details; ?></textarea>

<label>Income Date</label>
<input
type="date"
name="income_date"
required
max="<?php echo date('Y-m-d'); ?>"
value="<?php echo $income_date; ?>">
<input
type="submit"
name="submit"
value="<?php echo $label; ?> Income"
class="btn">

</form>

<?php
if($msg!=""){
?>
<div class="error">
<?php echo $msg; ?>
</div>
<?php
}
?>

</div>

<?php
include('footer.php');
?>