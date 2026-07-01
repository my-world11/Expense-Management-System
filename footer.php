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
      window.location.href='?cat_id='+ category_id;
    }
   </script>
</html>