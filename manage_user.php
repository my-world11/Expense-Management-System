<?php

include('header.php');

checkUser();
adminArea();


$msg="";

$username="";

$email="";

$role="User";

$status="Active";

$label="Add";

$id=0;


/*
==================================================
EDIT USER
==================================================
*/

if(
    isset($_GET['id']) &&
    (int)$_GET['id']>0
){

    $label="Edit";

    $id=(int)$_GET['id'];


    $res=mysqli_query(
        $con,
        "SELECT *
         FROM users
         WHERE id='$id'"
    );


    if(!$res){

        die(
            "Select Error: ".
            mysqli_error($con)
        );

    }


    if(mysqli_num_rows($res)==0){

        redirect('users.php');

        die();

    }


    $row=mysqli_fetch_assoc($res);


    $username=$row['username'];

    $email=$row['email'];

    $role=$row['role'];


    /*
    ==========================================
    STATUS
    ==========================================
    */

    if(
        strtolower($row['status'])=="suspended"
    ){

        $status="Suspended";

    }
    else{

        $status="Active";

    }

}


/*
==================================================
SUBMIT
==================================================
*/

if(isset($_POST['submit'])){


    $username=get_safe_value(
        $_POST['username']
    );


    $email=get_safe_value(
        $_POST['email']
    );


    $password=get_safe_value(
        $_POST['password']
    );


    $role=get_safe_value(
        $_POST['role']
    );


    $status=get_safe_value(
        $_POST['status']
    );


    /*
    ==========================================
    STANDARDIZE STATUS
    ==========================================
    */

    if(
        strtolower($status)=="suspended"
    ){

        $status="Suspended";

    }
    else{

        $status="Active";

    }


    $sub_sql="";


    if($id>0){

        $sub_sql="AND id!='$id'";

    }


    /*
    ==========================================
    CHECK USERNAME
    ==========================================
    */

    $check=mysqli_query(
        $con,
        "SELECT *
         FROM users
         WHERE username='$username'
         $sub_sql"
    );


    if(!$check){

        die(
            "Username Check Error: ".
            mysqli_error($con)
        );

    }


    if(mysqli_num_rows($check)>0){

        $msg="Username already exists";

    }
    else{


        /*
        ==========================================
        CHECK EMAIL
        ==========================================
        */

        $check=mysqli_query(
            $con,
            "SELECT *
             FROM users
             WHERE email='$email'
             $sub_sql"
        );


        if(!$check){

            die(
                "Email Check Error: ".
                mysqli_error($con)
            );

        }


        if(mysqli_num_rows($check)>0){

            $msg="Email already exists";

        }
        else{


            /*
            ==========================================
            EDIT USER
            ==========================================
            */

            if($id>0){


                /*
                ==========================================
                PASSWORD PROVIDED
                ==========================================
                */

                if($password!=""){


                    $password_hash=password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                    /*
                    ==========================================
                    SUSPENDED
                    ==========================================
                    */

                    if($status=="Suspended"){


                        /*
                        20 DAYS AFTER TODAY
                        */

                        $suspend_until=date(
                            'Y-m-d',
                            strtotime('+20 days')
                        );


                        $query=mysqli_query(
                            $con,
                            "UPDATE users
                             SET username='$username',
                                 email='$email',
                                 password='$password_hash',
                                 role='$role',
                                 status='Suspended',
                                 suspend_until='$suspend_until'
                             WHERE id='$id'"
                        );

                    }


                    /*
                    ==========================================
                    ACTIVE
                    ==========================================
                    */

                    else{


                        $query=mysqli_query(
                            $con,
                            "UPDATE users
                             SET username='$username',
                                 email='$email',
                                 password='$password_hash',
                                 role='$role',
                                 status='Active',
                                 suspend_until=NULL
                             WHERE id='$id'"
                        );

                    }

                }


                /*
                ==========================================
                PASSWORD NOT PROVIDED
                ==========================================
                */

                else{


                    /*
                    ==========================================
                    SUSPENDED
                    ==========================================
                    */

                    if($status=="Suspended"){


                        /*
                        20 DAYS AFTER TODAY
                        */

                        $suspend_until=date(
                            'Y-m-d',
                            strtotime('+20 days')
                        );


                        $query=mysqli_query(
                            $con,
                            "UPDATE users
                             SET username='$username',
                                 email='$email',
                                 role='$role',
                                 status='Suspended',
                                 suspend_until='$suspend_until'
                             WHERE id='$id'"
                        );

                    }


                    /*
                    ==========================================
                    ACTIVE
                    ==========================================
                    */

                    else{


                        $query=mysqli_query(
                            $con,
                            "UPDATE users
                             SET username='$username',
                                 email='$email',
                                 role='$role',
                                 status='Active',
                                 suspend_until=NULL
                             WHERE id='$id'"
                        );

                    }

                }


                if(!$query){

                    die(
                        "Update Error: ".
                        mysqli_error($con)
                    );

                }


                redirect('users.php');

                die();

            }


            /*
            ==========================================
            ADD NEW USER
            ==========================================
            */

            else{


                if($password==""){

                    $msg="Password is required";

                }
                else{


                    $password_hash=password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                    /*
                    ==========================================
                    ADD SUSPENDED USER
                    ==========================================
                    */

                    if($status=="Suspended"){


                        $suspend_until=date(
                            'Y-m-d',
                            strtotime('+20 days')
                        );


                        $query=mysqli_query(
                            $con,
                            "INSERT INTO users
                            (
                                username,
                                email,
                                password,
                                role,
                                status,
                                suspend_until,
                                last_login
                            )
                            VALUES
                            (
                                '$username',
                                '$email',
                                '$password_hash',
                                '$role',
                                'Suspended',
                                '$suspend_until',
                                NULL
                            )"
                        );

                    }


                    /*
                    ==========================================
                    ADD ACTIVE USER
                    ==========================================
                    */

                    else{


                        $query=mysqli_query(
                            $con,
                            "INSERT INTO users
                            (
                                username,
                                email,
                                password,
                                role,
                                status,
                                suspend_until,
                                last_login
                            )
                            VALUES
                            (
                                '$username',
                                '$email',
                                '$password_hash',
                                '$role',
                                'Active',
                                NULL,
                                NULL
                            )"
                        );

                    }


                    if(!$query){

                        die(
                            "Insert Error: ".
                            mysqli_error($con)
                        );

                    }


                    redirect('users.php');

                    die();

                }

            }

        }

    }

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

    box-sizing:border-box;

}

.form-box h2{

    text-align:center;

    margin-bottom:20px;

}

.form-box label{

    display:block;

    margin-top:10px;

    font-weight:bold;

}

.form-box input,
.form-box select{

    width:100%;

    padding:10px;

    margin-top:5px;

    border:1px solid #ccc;

    border-radius:5px;

    box-sizing:border-box;

}

.form-box input[type=checkbox]{

    width:auto;

}

.btn{

    width:100%;

    padding:10px;

    margin-top:20px;

    background:#0d6efd;

    color:white;

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

    color:#0d6efd;

}

.error{

    color:red;

    text-align:center;

    margin-top:10px;

    font-weight:bold;

}

</style>


<div class="form-box">


<h2>

<?php

echo $label;

?>

 User

</h2>


<a
    class="back"
    href="users.php"
>

    ← Back

</a>


<form method="post">


<label>

    Username

</label>


<input
    type="text"
    name="username"
    required
    value="<?php echo htmlspecialchars($username); ?>"
>


<label>

    Gmail

</label>


<input
    type="email"
    name="email"
    required
    placeholder="example@gmail.com"
    value="<?php echo htmlspecialchars($email); ?>"
>


<label>

    Password

</label>


<input
    type="password"
    name="password"
    id="password"

<?php

if($label=="Add"){

    echo "required";

}

?>

>


<label
    style="margin-top:8px;font-weight:normal;"
>

<input
    type="checkbox"
    onclick="showPassword()"
>

Show Password

</label>


<label>

    Role

</label>


<select name="role">


<option
    value="User"

<?php

if($role=="User"){

    echo "selected";

}

?>

>

User

</option>


<option
    value="Admin"

<?php

if($role=="Admin"){

    echo "selected";

}

?>

>

Admin

</option>


</select>


<label>

    Status

</label>


<select name="status">


<option
    value="Active"

<?php

if($status=="Active"){

    echo "selected";

}

?>

>

Active

</option>


<option
    value="Suspended"

<?php

if($status=="Suspended"){

    echo "selected";

}

?>

>

Suspended

</option>


</select>


<input
    type="submit"
    name="submit"
    value="<?php echo $label; ?> User"
    class="btn"
>


</form>


<?php

if($msg!=""){

?>

<div class="error">

<?php

echo $msg;

?>

</div>

<?php

}

?>


</div>


<script>

function showPassword(){

    var x=document.getElementById("password");


    if(x.type=="password"){

        x.type="text";

    }
    else{

        x.type="password";

    }

}

</script>


<?php

include('footer.php');

?>