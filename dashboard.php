<?php
include('header.php');
checkUser();
userArea();
include('user_header.php');
?>

<h2 style="margin-bottom:20px;">
🏠 Dashboard
</h2>

<style>

.dashboard{
display:flex;
flex-wrap:wrap;
gap:20px;
margin-top:20px;
}

.card{
width:220px;
padding:20px;
border-radius:12px;
color:#fff;
box-shadow:0 4px 10px rgba(0,0,0,.25);
transition:.3s;
}

.card:hover{
transform:translateY(-5px);
}

.card h3{
margin:0;
font-size:18px;
}

.card p{
font-size:28px;
font-weight:bold;
margin:15px 0;
}

.details-btn{
display:inline-block;
padding:8px 16px;
background:#fff;
color:#000;
text-decoration:none;
border-radius:5px;
font-weight:bold;
border-radius:5px;
}

.details-btn:hover{
background:#f1f1f1;
}

.blue{background:#2196F3;}
.green{background:#4CAF50;}
.red{background:#F44336;}
.orange{background:#FF9800;}
.purple{background:#9C27B0;}
.dark{background:#34495e;}

.chart-box{
width:700px;
max-width:100%;
margin:30px auto;
background:#fff;
padding:20px;
border-radius:10px;
box-shadow:0 0 10px rgba(0,0,0,.2);
}

</style>

<div class="dashboard">

<div class="card blue">
<h3>💸 Today's Expense</h3>
<p><?php echo getDashboardExpense('today'); ?></p>
<a class="details-btn" href="dashboard_report.php?from=<?php echo date('Y-m-d');?>&to=<?php echo date('Y-m-d');?>">Details</a>
</div>

<div class="card green">
<h3>💰 Today's Income</h3>
<p><?php echo getDashboardIncome('today'); ?></p>
<a class="details-btn" href="income_report.php?from=<?php echo date('Y-m-d');?>&to=<?php echo date('Y-m-d');?>">Details</a>
</div>

<div class="card red">
<h3>📅 Month Expense</h3>
<p><?php echo getDashboardExpense('month'); ?></p>
<a class="details-btn" href="dashboard_report.php?from=<?php echo date('Y-m-d',strtotime('-1 month')); ?>&to=<?php echo date('Y-m-d');?>">Details</a>
</div>

<div class="card orange">
<h3>💵 Month Income</h3>
<p><?php echo getDashboardIncome('month'); ?></p>
<a class="details-btn" href="income_report.php?from=<?php echo date('Y-m-d',strtotime('-1 month')); ?>&to=<?php echo date('Y-m-d');?>">Details</a>
</div>

<div class="card purple">
<h3>💳 Total Expense</h3>
<p><?php echo getDashboardExpense('total'); ?></p>
<a class="details-btn" href="expense.php">Details</a>
</div>

<div class="card dark">
<h3>🏦 Savings</h3>
<p><?php echo getSaving(); ?></p>
<a class="details-btn" href="reports.php">Details</a>
</div>

</div>

<?php

$chart=mysqli_query($con,"
SELECT category.name,SUM(expense.price) AS total
FROM expense
JOIN category ON expense.category_id=category.id
WHERE expense.added_by='".$_SESSION['UID']."'
GROUP BY category.name
");

$labels=array();
$data=array();

while($row=mysqli_fetch_assoc($chart)){
    $labels[]=$row['name'];
    $data[]=$row['total'];
}

?>

<div class="chart-box">

<h2 align="center">📊 Expense by Category</h2>

<canvas id="expenseChart"></canvas>

</div>

<?php
include('footer.php');
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const chartCanvas=document.getElementById("expenseChart");

if(chartCanvas){

new Chart(chartCanvas,{

type:'pie',

data:{

labels:<?php echo json_encode($labels); ?>,

datasets:[{

data:<?php echo json_encode($data); ?>,

backgroundColor:[
'#2196F3',
'#4CAF50',
'#FF9800',
'#E91E63',
'#9C27B0',
'#009688',
'#F44336',
'#3F51B5',
'#795548',
'#607D8B'
],

borderWidth:2

}]

},

options:{

responsive:true,

plugins:{

legend:{
position:'bottom'
}

}

}

});

}

</script>