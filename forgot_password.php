</div>

<div class="footer">

© <?php echo date('Y');?> Expense Management System

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

function change_cat(){

var category_id=document.getElementById('category_id').value;

window.location.href='?category_id='+category_id;

}

function delete_confir(id,page){

var check=confirm("Are you sure?");

if(check){

window.location.href=page+"?type=delete&id="+id;

}

}

function set_to_date(){

var from_date=document.getElementById('from_date').value;

document.getElementById('to_date').setAttribute("min",from_date);

}

</script>

</body>
</html>