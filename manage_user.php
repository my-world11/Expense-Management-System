
<?php

include('header.php');

checkUser();
adminArea();

$msg = "";

$username = "";
$email = "";
$role = "User";
$label = "Add";
$id = 0;

/*
==================================================
EDIT USER
==================================================
*/

if(isset($_GET['id']) && (int)$_GET['id'] > 0){

    $label = "Edit";
    $id = (int)$_GET['id'];

    $res = mysqli_query(
        $con,
        "SELECT * FROM users WHERE id='$id'"
    );

    if(!$res){
        die("Select Error: " . mysqli_error($con));
    }

    if(mysqli_num_rows($res) == 0){
        redirect('users.php');
        die();
    }

    $row = mysqli_fetch_assoc($res);

    $username = $row['username'];
    $email = $row['email'];
    $role = $row['role'];

}

/*
==================================================
SUBMIT FORM
==================================================
*/

if(isset($_POST['submit'])){

    $username = get_safe_value($_POST['username']);
    $email = get_safe_value($_POST['email']);
    $password = get_safe_value($_POST['password']);
    $role = get_safe_value($_POST['role']);

    $sub_sql = "";

    if($id > 0){
        $sub_sql = "AND id!='$id'";
    }

    /*
    ==========================================
    CHECK USERNAME
    ==========================================
    */

    $check = mysqli_query(
        $con,
        "SELECT * FROM users
         WHERE username='$username'
         $sub_sql"
    );

    if(!$check){
        die("Username Check Error: " . mysqli_error($con));
    }

    if(mysqli_num_rows($check) > 0){

        $msg = "Username already exists";

    }else{

        /*
        ==========================================
        CHECK EMAIL
        ==========================================
        */

        $check = mysqli_query(
            $con,
            "SELECT * FROM users
             WHERE email='$email'
             $sub_sql"
        );

        if(!$check){
            die("Email Check Error: " . mysqli_error($con));
        }

        if(mysqli_num_rows($check) > 0){

            $msg = "Email already exists";

        }else{

            /*
            ==========================================
            EDIT USER
            ==========================================
            */

            if($id > 0){

                if($password != ""){

                    $password_hash = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $query = mysqli_query(
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

                }else{

                    $query = mysqli_query(
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

                if(!$query){
                    die("Update Error: " . mysqli_error($con));
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

                if($password == ""){

                    $msg = "Password is required";

                }else{

                    $password_hash = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $query = mysqli_query(
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

                    if(!$query){
                        die("Insert Error: " . mysqli_error($con));
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

/* Main page */
.manage-user-page {
    width: 100%;
    max-width: 1250px;
    min-height: 100vh;
    margin: 0 auto;
    padding: 30px 25px;
    box-sizing: border-box;
    background: #f5f7fb;
    font-family: Arial, sans-serif;
}

/* Page heading */
.manage-header {
    background: #ffffff;
    border: 1px solid #e7ebf2;
    border-radius: 14px;
    padding: 28px 30px;
    margin-bottom: 25px;
    box-shadow: 0 3px 14px rgba(30,80,140,0.07);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.manage-heading {
    display: flex;
    align-items: center;
    gap: 17px;
}

.manage-icon {
    width: 62px;
    height: 62px;
    flex-shrink: 0;
    border-radius: 14px;
    background: #eaf1ff;
    color: #2864d9;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 27px;
}

.manage-heading h1 {
    margin: 0;
    color: #1e293b;
    font-size: 27px;
    font-weight: 700;
}

.manage-heading p {
    margin: 7px 0 0;
    color: #788397;
    font-size: 14px;
    line-height: 1.5;
}

/* Back button */
.back-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 16px;
    background: #eaf1ff;
    color: #2864d9;
    border: 1px solid #d5e2fa;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    transition: 0.2s;
}

.back-btn:hover {
    background: #2864d9;
    color: #ffffff;
}

/* Form card */
.manage-form-card {
    width: 100%;
    max-width: 850px;
    margin: 0 auto;
    background: #ffffff;
    border: 1px solid #e7ebf2;
    border-radius: 14px;
    padding: 30px;
    box-sizing: border-box;
    box-shadow: 0 4px 15px rgba(30,50,80,0.05);
}

/* Form heading */
.form-heading {
    margin-bottom: 25px;
    padding-bottom: 18px;
    border-bottom: 1px solid #edf0f5;
}

.form-heading h2 {
    margin: 0;
    color: #263247;
    font-size: 21px;
    font-weight: 700;
}

.form-heading p {
    margin: 7px 0 0;
    color: #788397;
    font-size: 13px;
}

/* Form grid */
.user-form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    min-width: 0;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #344258;
    font-size: 13px;
    font-weight: 600;
}

.form-group input,
.form-group select {
    width: 100%;
    height: 45px;
    padding: 0 13px;
    box-sizing: border-box;
    border: 1px solid #dce2eb;
    border-radius: 8px;
    background: #ffffff;
    color: #263247;
    font-size: 13px;
    outline: none;
    transition: 0.2s;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #2864d9;
    box-shadow: 0 0 0 3px rgba(40,100,217,0.10);
}

.form-group input::placeholder {
    color: #a0a9b8;
}

/* Password helper */
.password-note {
    margin-top: 7px;
    color: #8490a3;
    font-size: 12px;
    line-height: 1.5;
}

/* Show password */
.show-password {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
    color: #647087;
    font-size: 12px;
    cursor: pointer;
}

.show-password input {
    width: 15px;
    height: 15px;
    margin: 0;
    accent-color: #2864d9;
}

/* Form button */
.form-actions {
    grid-column: 1 / -1;
    display: flex;
    justify-content: flex-end;
    margin-top: 8px;
}

.submit-btn {
    min-width: 180px;
    height: 45px;
    padding: 0 22px;
    border: none;
    border-radius: 8px;
    background: #2864d9;
    color: #ffffff;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
}

.submit-btn:hover {
    background: #1e50b5;
}

/* Error message */
.error {
    margin-top: 20px;
    padding: 13px 15px;
    border: 1px solid #f5c5c2;
    border-radius: 8px;
    background: #fff0ef;
    color: #c52b24;
    font-size: 13px;
    font-weight: 600;
}

/* Responsive */
@media(max-width: 768px) {

    .manage-user-page {
        padding: 20px 15px;
    }

    .manage-header {
        padding: 22px;
        flex-direction: column;
        align-items: stretch;
    }

    .manage-icon {
        width: 55px;
        height: 55px;
        font-size: 24px;
    }

    .manage-heading h1 {
        font-size: 23px;
    }

    .manage-form-card {
        padding: 22px;
    }

}

@media(max-width: 520px) {

    .manage-user-page {
        padding: 15px 10px;
    }

    .manage-header {
        padding: 18px;
    }

    .manage-heading {
        gap: 12px;
    }

    .manage-icon {
        width: 48px;
        height: 48px;
        font-size: 21px;
    }

    .manage-heading h1 {
        font-size: 20px;
    }

    .manage-heading p {
        font-size: 12px;
    }

    .back-btn {
        width: 100%;
    }

    .manage-form-card {
        padding: 18px;
    }

    .user-form {
        grid-template-columns: 1fr;
        gap: 17px;
    }

    .form-group.full-width,
    .form-actions {
        grid-column: auto;
    }

    .submit-btn {
        width: 100%;
    }

}

</style>

<div class="manage-user-page">

    <div class="manage-header">

        <div class="manage-heading">

            <div class="manage-icon">
                <i class="fa-solid fa-user-plus"></i>
            </div>

            <div>
                <h1><?php echo $label; ?> User</h1>
                <p>Manage user account information.</p>
            </div>

        </div>

        <a href="users.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Users
        </a>

    </div>

    <div class="manage-form-card">

        <div class="form-heading">

            <h2>User Information</h2>

            <p>
                <?php
                echo ($label == "Add")
                    ? "Enter the details to create a new user account."
                    : "Update the selected user's account information.";
                ?>
            </p>

        </div>

        <form method="post" class="user-form" autocomplete="off">

            <div class="form-group">

                <label>Username</label>

                <input
                    type="text"
                    name="username"
                    required
                    placeholder="Enter username"
                    value="<?php echo htmlspecialchars($username); ?>"
                >

            </div>

            <div class="form-group">

                <label>Gmail</label>

                <input
                    type="email"
                    name="email"
                    required
                    placeholder="example@gmail.com"
                    value="<?php echo htmlspecialchars($email); ?>"
                >

            </div>

            <div class="form-group full-width">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    id="password"
                    <?php if($label == "Add"){ echo "required"; } ?>
                    autocomplete="new-password"
                    placeholder="<?php echo ($label == "Edit") ? "Enter new password (optional)" : "Enter password"; ?>"
                >

                <?php if($label == "Edit"){ ?>

                    <div class="password-note">
                        Leave this field blank to keep the existing password unchanged.
                    </div>

                <?php } ?>

                <label class="show-password">

                    <input
                        type="checkbox"
                        onclick="showPassword()"
                    >

                    Show Password

                </label>

            </div>

            <div class="form-group full-width">

                <label>Role</label>

                <select name="role">

                    <option
                        value="User"
                        <?php if($role == "User"){ echo "selected"; } ?>
                    >
                        User
                    </option>

                    <option
                        value="Admin"
                        <?php if($role == "Admin"){ echo "selected"; } ?>
                    >
                        Admin
                    </option>

                </select>

            </div>

            <div class="form-actions">

                <button
                    type="submit"
                    name="submit"
                    class="submit-btn"
                >
                    <i class="fa-solid fa-floppy-disk"></i>
                    <?php echo $label; ?> User
                </button>

            </div>

        </form>

        <?php if($msg != ""){ ?>

            <div class="error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($msg); ?>
            </div>

        <?php } ?>

    </div>

</div>

<script>

function showPassword(){

    var passwordField = document.getElementById("password");

    if(passwordField.type == "password"){
        passwordField.type = "text";
    }else{
        passwordField.type = "password";
    }

}

</script>

<?php

include('footer.php');

?>
