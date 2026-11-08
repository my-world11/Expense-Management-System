
<?php
include('header.php');
checkUser();
adminArea();

$msg = "";
$category = "";
$category_type = "Expense";
$label = "Add";
$id = 0;

// Load category details for editing
if (isset($_GET['id']) && $_GET['id'] > 0) {
    $label = "Edit";
    $id = (int)$_GET['id'];

    $stmt = mysqli_prepare(
        $con,
        "SELECT * FROM category WHERE id = ? AND created_by = 'Admin'"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($res) == 0) {
        redirect('category.php');
        die();
    }

    $row = mysqli_fetch_assoc($res);
    $category = $row['name'];
    $category_type = $row['type'];
}

// Save category
if (isset($_POST['submit'])) {

    $name = get_safe_value($_POST['name']);
    $type = get_safe_value($_POST['type']);

    if (!in_array($type, array('Expense', 'Income'))) {
        $msg = "Invalid category type";
    } else {

        // Check duplicate category name within Admin categories
        $stmt = mysqli_prepare(
            $con,
            "SELECT id FROM category
             WHERE name = ? AND type = ?
             AND created_by = 'Admin' AND id != ?"
        );

        mysqli_stmt_bind_param($stmt, "ssi", $name, $type, $id);
        mysqli_stmt_execute($stmt);

        $res = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($res) > 0) {
            $msg = "Category already exists in this type";
        } else {

            if ($id > 0) {

                // Update existing Admin category
                $stmt = mysqli_prepare(
                    $con,
                    "UPDATE category
                     SET name = ?, type = ?
                     WHERE id = ? AND created_by = 'Admin'"
                );

                mysqli_stmt_bind_param($stmt, "ssi", $name, $type, $id);

            } else {

                // Insert new Admin category
                $stmt = mysqli_prepare(
                    $con,
                    "INSERT INTO category (name, type, created_by)
                     VALUES (?, ?, 'Admin')"
                );

                mysqli_stmt_bind_param($stmt, "ss", $name, $type);
            }

            if (mysqli_stmt_execute($stmt)) {
                redirect('category.php');
                die();
            } else {
                $msg = "Unable to save category";
            }
        }
    }

    $category = $name;
    $category_type = $type;
}

include('user_header.php');
?>

<h2><?php echo $label; ?> Category</h2>

<a href="category.php">Back</a>

<br><br>

<form method="post">

    <table>

        <tr>
            <td>Category Name</td>
            <td>
                <input
                    type="text"
                    name="name"
                    required
                    value="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>"
                >
            </td>
        </tr>

        <tr>
            <td>Category Type</td>
            <td>
                <select name="type" required>
                    <option value="Expense" <?php if ($category_type == 'Expense') echo 'selected'; ?>>
                        Expense Category
                    </option>

                    <option value="Income" <?php if ($category_type == 'Income') echo 'selected'; ?>>
                        Income Category
                    </option>
                </select>
            </td>
        </tr>

        <tr>
            <td></td>
            <td>
                <input type="submit" name="submit" value="Submit">
            </td>
        </tr>

    </table>

</form>

<p><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></p>

<?php
include('footer.php');
?>
