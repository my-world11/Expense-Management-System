</div>

<div class="footer" style="
    margin-left:230px;
    margin-top:40px;
    padding:15px;
    background:#0d6efd;
    color:white;
    text-align:center;
    font-size:16px;
">
    © <?php echo date('Y');?> Expense Management System
</div>

<script>

function change_cat(){
    var category_id=document.getElementById('category_id').value;
    window.location.href='?category_id='+category_id;
}

function delete_confir(id,page){
    var check=confirm("Are you sure you want to delete?");
    if(check){
        window.location.href=page+"?type=delete&id="+id;
    }
}

function set_to_date(){
    var from_date=document.getElementById('from_date').value;

    if(document.getElementById('to_date')){
        document.getElementById('to_date').setAttribute("min",from_date);
    }
}

</script>

<!-- Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Tom Select -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>

document.addEventListener("DOMContentLoaded",function(){

    /* Category Dropdown */
    if(document.getElementById("category_id")){

        new TomSelect("#category_id",{
            create:false,
            maxOptions:100,
            placeholder:"Select Category"
        });

    }

    /* Expense Date */
    if(document.querySelector("input[name='expense_date']")){

        flatpickr("input[name='expense_date']",{
            dateFormat:"Y-m-d",
            maxDate:"today",
            allowInput:false
        });

    }

    /* Income Date */
    if(document.querySelector("input[name='income_date']")){

        flatpickr("input[name='income_date']",{
            dateFormat:"Y-m-d",
            maxDate:"today",
            allowInput:false
        });

    }

});

</script>

</body>
</html>