  <br><br>
  <div>
    @copyright
    <?php
    echo date('Y');
    ?>
   </div>
   <script>
    function change_cat(){
      var category_id= document.getElementById('category_id').value;
      window.location.href='?category_id='+ category_id;
    }
    function delete_confir(id,page){
      var check=confirm("are you sure");
      if(check==true){
        window.location.href=page+"?type=delete&id="+id;
      }
    }
   </script>
</html>