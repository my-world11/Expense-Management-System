
<?php

ob_start();

include('header.php');

checkUser();
adminArea();

$search = "";

if(isset($_GET['search']) && $_GET['search'] != ""){
    $search = get_safe_value($_GET['search']);
}

$sql_normal = "
    SELECT *
    FROM users
    WHERE role='User'
";

if($search != ""){
    $sql_normal .= "
        AND username LIKE '%$search%'
    ";
}

$sql_normal .= " ORDER BY id DESC";

$res_normal = mysqli_query($con, $sql_normal);

if(!$res_normal){
    die("User Error: " . mysqli_error($con));
}

include('user_header.php');

?>

<style>

/* Main page frame - matches Admin Dashboard */
.users-page {
    width: 100%;
    max-width: 1250px;
    min-height: 100vh;
    margin: 0 auto;
    padding: 30px 25px;
    box-sizing: border-box;
    background: #f5f7fb;
    font-family: Arial, sans-serif;
}

/* Header card */
.users-header-card {
    width: 100%;
    background: #ffffff;
    border-radius: 14px;
    padding: 28px 30px;
    box-sizing: border-box;
    box-shadow: 0 3px 14px rgba(30,80,140,0.07);
    border: 1px solid #e7ebf2;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

/* Title */
.users-title-area {
    display: flex;
    align-items: center;
    gap: 17px;
    min-width: 0;
}

.users-title-icon {
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

.users-title-text h1 {
    margin: 0;
    color: #1e293b;
    font-size: 27px;
    font-weight: 700;
}

.users-title-text p {
    margin: 7px 0 0;
    color: #788397;
    font-size: 14px;
    line-height: 1.5;
}

/* Top actions */
.users-top-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* Search */
.search-form {
    display: flex;
    align-items: center;
    gap: 8px;
}

.search {
    width: 230px;
    height: 42px;
    box-sizing: border-box;
    padding: 0 13px;
    border: 1px solid #dce2eb;
    border-radius: 8px;
    background: #fff;
    color: #263d59;
    font-size: 13px;
    outline: none;
}

.search:focus {
    border-color: #3975e8;
    box-shadow: 0 0 0 3px rgba(57,117,232,0.10);
}

.search-btn,
.add-user {
    height: 42px;
    padding: 0 16px;
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    cursor: pointer;
    text-decoration: none;
    box-sizing: border-box;
    transition: 0.2s;
    white-space: nowrap;
}

.search-btn {
    background: #2864d9;
}

.search-btn:hover {
    background: #1e50b5;
}

.add-user {
    background: #16a36a;
}

.add-user:hover {
    background: #118455;
    color: white;
}

/* Users card */
.users-card {
    width: 100%;
    background: #ffffff;
    border: 1px solid #e7ebf2;
    border-radius: 14px;
    padding: 25px;
    box-sizing: border-box;
    box-shadow: 0 4px 15px rgba(30,50,80,0.05);
}

/* Card header */
.users-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
    padding-bottom: 18px;
    border-bottom: 1px solid #edf0f5;
}

.users-card-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.users-card-title-icon {
    color: #2864d9;
    font-size: 20px;
}

.users-card-title h2 {
    margin: 0;
    font-size: 21px;
    color: #263247;
    font-weight: 700;
}

.user-count {
    background: #eaf1ff;
    color: #2864d9;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

/* Table */
.table-wrapper {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #e8edf4;
    border-radius: 9px;
}

.user-table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    min-width: 750px;
}

.user-table th {
    background: #f5f7fb;
    color: #647087;
    padding: 14px 15px;
    font-size: 12px;
    font-weight: 700;
    text-align: left;
    border-bottom: 1px solid #e8edf4;
    white-space: nowrap;
}

.user-table th i {
    margin-right: 5px;
}

.user-table td {
    padding: 15px;
    color: #475569;
    font-size: 13px;
    border-bottom: 1px solid #edf0f5;
    vertical-align: middle;
}

.user-table tbody tr:last-child td {
    border-bottom: none;
}

.user-table tbody tr:hover {
    background: #f9fbff;
}

/* Serial number */
.id-cell {
    color: #8792a5 !important;
    font-weight: 600;
    width: 55px;
}

/* Username */
.username-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #263247;
    font-weight: 600;
}

.username-icon {
    width: 29px;
    height: 29px;
    flex-shrink: 0;
    border-radius: 7px;
    background: #eaf1ff;
    color: #2864d9;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

/* Email and login */
.email-cell {
    color: #647087;
    word-break: break-word;
}

.login-cell {
    color: #647087;
    white-space: nowrap;
}

.login-cell i {
    color: #8490a3;
    margin-right: 5px;
}

.never-login {
    color: #8490a3;
}

/* Status */
.status-active,
.status-inactive {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 11px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

.status-active {
    color: #128052;
    background: #e7f8ef;
}

.status-inactive {
    color: #a66a12;
    background: #fff4df;
}

.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: block;
}

.status-active .status-dot {
    background: #18a874;
}

.status-inactive .status-dot {
    background: #f28a16;
}

/* Edit button */
.edit-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 13px;
    border-radius: 7px;
    background: #eaf1ff;
    color: #2864d9;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid #d5e2fa;
    transition: 0.2s;
    white-space: nowrap;
}

.edit-btn:hover {
    background: #2864d9;
    color: white;
    border-color: #2864d9;
}

/* Information box */
.info-box {
    margin-top: 22px;
    padding: 15px 18px;
    background: #eef6ff;
    border: 1px solid #d5e5fa;
    border-radius: 9px;
    color: #34577e;
    font-size: 13px;
    line-height: 1.7;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.info-icon {
    color: #2864d9;
    font-size: 16px;
    margin-top: 2px;
    flex-shrink: 0;
}

.info-box strong {
    color: #1e4778;
}

/* No data */
.no-data {
    padding: 45px 20px;
    text-align: center;
    color: #8490a3;
}

.no-data-icon {
    width: 55px;
    height: 55px;
    margin: 0 auto 12px;
    border-radius: 50%;
    background: #eef6ff;
    color: #2864d9;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

.no-data h3 {
    margin: 0 0 7px;
    color: #344258;
    font-size: 17px;
}

.no-data p {
    margin: 0;
    font-size: 13px;
}

/* Tablet */
@media(max-width: 1100px) {
    .users-header-card {
        flex-direction: column;
        align-items: stretch;
    }

    .users-top-actions {
        justify-content: space-between;
    }
}

/* Dashboard responsive spacing */
@media(max-width: 768px) {
    .users-page {
        padding: 20px 15px;
    }

    .users-header-card {
        padding: 22px;
        margin-bottom: 20px;
    }

    .users-title-icon {
        width: 55px;
        height: 55px;
        font-size: 24px;
    }

    .users-title-text h1 {
        font-size: 23px;
    }

    .users-card {
        padding: 18px;
    }

    .users-card-title h2 {
        font-size: 19px;
    }
}

/* Small mobile */
@media(max-width: 420px) {
    .users-page {
        padding: 15px 10px;
    }

    .users-header-card {
        padding: 18px;
    }

    .users-title-area {
        gap: 12px;
    }

    .users-title-icon {
        width: 48px;
        height: 48px;
        font-size: 21px;
    }

    .users-title-text h1 {
        font-size: 20px;
    }

    .users-title-text p {
        font-size: 12px;
    }

    .users-top-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .search-form {
        width: 100%;
    }

    .search-wrapper {
        flex: 1;
        min-width: 0;
    }

    .search {
        width: 100%;
        min-width: 0;
    }

    .search-btn {
        flex-shrink: 0;
        padding: 0 10px;
    }

    .add-user {
        width: 100%;
    }

    .users-card {
        padding: 15px;
    }

    .users-card-title h2 {
        font-size: 17px;
    }

    .info-box {
        font-size: 12px;
        padding: 13px;
    }
}

</style>

<div class="users-page">

    <div class="users-header-card">

        <div class="users-title-area">

            <div class="users-title-icon">
                <i class="fa-solid fa-users"></i>
            </div>

            <div class="users-title-text">
                <h1>Users Management</h1>
                <p>Manage your system users and their account information.</p>
            </div>

        </div>

        <div class="users-top-actions">

            <a href="manage_user.php" class="add-user">
                <i class="fa-solid fa-user-plus"></i>
                Add User
            </a>

            <form method="get" class="search-form">

                <div class="search-wrapper">
                    <input
                        type="text"
                        name="search"
                        class="search"
                        placeholder="Search Username..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                </div>

                <button type="submit" class="search-btn">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Search
                </button>

            </form>

        </div>

    </div>

    <div class="users-card">

        <div class="users-card-header">

            <div class="users-card-title">
                <span class="users-card-title-icon">
                    <i class="fa-solid fa-users"></i>
                </span>
                <h2>Users</h2>
            </div>

            <div class="user-count">
                <?php echo mysqli_num_rows($res_normal); ?>
                <?php echo (mysqli_num_rows($res_normal) == 1 ? ' User' : ' Users'); ?>
            </div>

        </div>

        <?php if(mysqli_num_rows($res_normal) > 0){ ?>

            <div class="table-wrapper">

                <table class="user-table">

                    <thead>
                        <tr>
                            <th> ID</th>
                            <th><i class="fa-solid fa-user"></i> Username</th>
                            <th><i class="fa-solid fa-envelope"></i> Email</th>
                            <th><i class="fa-regular fa-calendar"></i> Last Login</th>
                            <th><i class="fa-solid fa-shield-halved"></i> Status</th>
                            <th><i class="fa-solid fa-gear"></i> Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php $i = 1; ?>

                        <?php while($row = mysqli_fetch_assoc($res_normal)){ ?>

                            <tr>

                                <td class="id-cell">
                                    <?php echo $i++; ?>
                                </td>

                                <td>
                                    <div class="username-cell">
                                        <span class="username-icon">
                                            <i class="fa-solid fa-user"></i>
                                        </span>

                                        <?php echo htmlspecialchars($row['username']); ?>
                                    </div>
                                </td>

                                <td class="email-cell">
                                    <?php echo htmlspecialchars($row['email']); ?>
                                </td>

                                <td class="login-cell">

                                    <?php if(!empty($row['last_login'])){ ?>

                                        <i class="fa-regular fa-clock"></i>

                                        <?php
                                        echo date(
                                            'd-m-Y h:i A',
                                            strtotime($row['last_login'])
                                        );
                                        ?>

                                    <?php }else{ ?>

                                        <span class="never-login">
                                            <i class="fa-regular fa-clock"></i>
                                            Never Login
                                        </span>

                                    <?php } ?>

                                </td>

                                <td>

                                    <?php
                                    if(
                                        !empty($row['last_login']) &&
                                        strtotime($row['last_login']) >= strtotime('-7 days')
                                    ){
                                    ?>

                                        <span class="status-active">
                                            <span class="status-dot"></span>
                                            Active
                                        </span>

                                    <?php }else{ ?>

                                        <span class="status-inactive">
                                            <span class="status-dot"></span>
                                            Inactive
                                        </span>

                                    <?php } ?>

                                </td>

                                <td>
                                    <a
                                        class="edit-btn"
                                        href="manage_user.php?id=<?php echo $row['id']; ?>"
                                    >
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        Edit
                                    </a>
                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

            <div class="info-box">

                <div class="info-icon">
                    <i class="fa-solid fa-circle-info"></i>
                </div>

                <div>
                    <strong>User Status:</strong>
                    Active means the user has logged in within the last 7 days.
                    Inactive means the user has not logged in for more than 7 days.

                    <br>

                    <strong>Note:</strong>
                    Inactive status is only for display and does not prevent the user from logging in.
                </div>

            </div>

        <?php }else{ ?>

            <div class="no-data">

                <div class="no-data-icon">
                    <i class="fa-solid fa-users-slash"></i>
                </div>

                <h3>No User Found</h3>

                <p>No users match your current search.</p>

            </div>

        <?php } ?>

    </div>

</div>

<?php
include('footer.php');
?>
