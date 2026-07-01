<?php
function prx($data){
    echo'<pre>';
    print_r($data);
    die();

 }
 function get_safe_value($data){
    global $con;
          if($data){
            return mysqli_real_escape_string ($con, $data);

          }
 }
 function redirect($link){
    ?>
    <script> 
    window.location.href="<?php echo $link?>";
    </script>
    <?php
 }
 function checkUser(){
   if(isset( $_SESSION['UID']) &&  $_SESSION['UID']!='') {

}else{
    redirect('index.php');

}
   
 }

 function getCategory($category_id='',$page=''){
   global $con;
   $res=mysqli_query($con,"select * from category order by name asc");
   
   $fun="";
   if($page=='reports'){
      $fun="onchange=change_cat()";
   }

   $html='<select required name="category_id" id="category_id" '.$fun.'>';
   $html.='<option value ="">Select Category </option>';
   
   while ($row=mysqli_fetch_assoc($res)){

      if($category_id>0 && $category_id==$row['id']){
         $html.='<option value ="'.$row['id'].'"selected>'.$row['name'].'</option>';

      }else{
          $html.='<option value ="'.$row['id'].'">'.$row['name'].'</option>';
      }
       
    
   }
   


   $html.='</select>';
   return $html;
 }
?>