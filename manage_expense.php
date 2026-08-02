<?php
include('header.php');
checkUser();
userArea();

$msg="";
$category_id="";
$item="";
$price="";
$details="";
$expense_date=date('Y-m-d');
$label="Add";

if(isset($_GET['id']) && $_GET['id']>0){

    $label="Edit";

    $id=get_safe_value($_GET['id']);

    $res=mysqli_query($con,"select * from expense where id=$id");

    if(mysqli_num_rows($res)==0){
        redirect('expense.php');
        die();
    }

    $row=mysqli_fetch_assoc($res);

    if($row['added_by']!=$_SESSION['UID']){
        redirect('expense.php');
    }

    $category_id=$row['category_id'];
    $item=$row['item'];
    $price=$row['price'];
    $details=$row['details'];
    $expense_date=$row['expense_date'];
}

if(isset($_POST['submit'])){

    $category_id=get_safe_value($_POST['category_id']);
    $item=get_safe_value($_POST['item']);
    $price=get_safe_value($_POST['price']);
    $details=get_safe_value($_POST['details']);
    $expense_date=get_safe_value($_POST['expense_date']);

    $added_on=date('Y-m-d H:i:s');
    $added_by=$_SESSION['UID'];

    if(isset($_GET['id']) && $_GET['id']>0){

        mysqli_query($con,"update expense set
        category_id='$category_id',
        item='$item',
        price='$price',
        details='$details',
        expense_date='$expense_date'
        where id=$id");

    }else{

        mysqli_query($con,"insert into expense
        (category_id,item,price,details,added_on,expense_date,added_by)
        values
        ('$category_id','$item','$price','$details','$added_on','$expense_date','$added_by')");
    }

    redirect('expense.php');
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
    font-weight:bold;
}

.form-group input,
.form-group select,
.form-group textarea{
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:6px;
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

.form-group label{
    font-size:22px;
    font-weight:bold;
}

.form-group select{
    height:55px;
    font-size:20px;
    font-weight:bold;
}

.form-group option{
    font-size:20px;
}

</style>

<div class="form-box">

<h2>💸 <?php echo $label; ?> Expense</h2>

<a class="back" href="expense.php">← Back</a>

<form method="post">

<div class="form-group">
<label>Category</label>

<select name="category_id" required>

    <option value="">Select Category</option>

    <?php
    $cat_res = mysqli_query($con,"SELECT * FROM category ORDER BY name ASC");

    while($cat = mysqli_fetch_assoc($cat_res)){
    ?>

    <option value="<?php echo $cat['id']; ?>"
        <?php if($category_id==$cat['id']) echo "selected"; ?>>
        <?php echo $cat['name']; ?>
    </option>

    <?php } ?>

</select>

</div>

<div class="form-group">
<label>Item</label>
<input
type="text"
name="item"
required
value="<?php echo $item; ?>">
</div>

<div class="form-group">
<label>Price</label>
<input
type="number"
name="price"
step="0.01"
min="0"
required
value="<?php echo $price; ?>">
</div>

<div class="form-group">
<label>Details</label>
<textarea
name="details"
required><?php echo $details; ?></textarea>
</div>

<div class="form-group">
<label>Expense Date</label>

<div class="date-box">

<input
type="text"
name="expense_date"
required
readonly
value="<?php echo $expense_date; ?>">

<i class="fa-solid fa-calendar-days"></i>

</div>

</div>
<input
type="submit"
name="submit"
value="<?php echo $label; ?> Expense"
class="btn">

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