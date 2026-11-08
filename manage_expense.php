
<?php
include('header.php');
checkUser();
userArea();

$msg="";
$category_id="";
$expense_type="Need";
$item="";
$price="";
$details="";
$expense_date=date('Y-m-d');
$label="Add";

if(isset($_GET['id']) && $_GET['id']>0){
    $label="Edit";
    $id=get_safe_value($_GET['id']);
    $res=mysqli_query($con,"select * from expense where id=$id");

    if(mysqli_num_rows($res)==0){
        redirect('expense.php');
        die();
    }

    $row=mysqli_fetch_assoc($res);

    if($row['added_by']!=$_SESSION['UID']){
        redirect('expense.php');
        die();
    }

    $category_id=$row['category_id'];
    $expense_type=$row['expense_type'];
    $item=$row['item'];
    $price=$row['price'];
    $details=$row['details'];
    $expense_date=$row['expense_date'];
}

if(isset($_POST['submit'])){
    $category_id=get_safe_value($_POST['category_id']);
    $expense_type=get_safe_value($_POST['expense_type']);
    $item=get_safe_value($_POST['item']);
    $price=get_safe_value($_POST['price']);
    $details=get_safe_value($_POST['details']);
    $expense_date=get_safe_value($_POST['expense_date']);

    $added_on=date('Y-m-d H:i:s');
    $added_by=$_SESSION['UID'];

    if(isset($_GET['id']) && $_GET['id']>0){
        mysqli_query($con,"update expense set
        category_id='$category_id',
        expense_type='$expense_type',
        item='$item',
        price='$price',
        details='$details',
        expense_date='$expense_date'
        where id=$id");
    }else{
        mysqli_query($con,"insert into expense
        (category_id,expense_type,item,price,details,added_on,expense_date,added_by)
        values
        ('$category_id','$expense_type','$item','$price','$details','$added_on','$expense_date','$added_by')");
    }

    redirect('expense.php');
}

/* Load Admin Expense Categories */
$cat_res=mysqli_query(
    $con,
    "SELECT * FROM category
     WHERE type = 'Expense'
     AND created_by = 'Admin'
     ORDER BY name ASC"
);

include('user_header.php');
?>

<style>
.expense-form-page {
    padding: 25px 30px;
    background-color: #f4f7fb;
    min-height: 100vh;
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.form-box {
    width: 650px;
    max-width: 100%;
    margin: 0;
}

/* Header */
.form-header h2 {
    font-size: 26px;
    color: #172033;
    font-weight: 800;
    margin: 0 0 4px 0;
}

.form-header p {
    color: #64748b;
    font-size: 14px;
    margin: 0 0 8px 0;
}

.back-link {
    display: inline-block;
    color: #2563eb;
    font-size: 14px;
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 18px;
}

.back-link:hover {
    text-decoration: underline;
}

/* Card Sections */
.form-card {
    background: #ffffff;
    border: 1px solid #dbe3ed;
    border-radius: 12px;
    padding: 18px 22px;
    margin-bottom: 16px;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
}

.form-group {
    margin-bottom: 15px;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
}

/* Base Inputs */
input[type="text"],
input[type="number"],
input[type="date"],
select,
textarea {
    width: 100%;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 14px;
    color: #1e293b;
    background: #ffffff;
    outline: none;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

input::placeholder,
textarea::placeholder {
    color: #94a3b8;
}

input:focus,
select:focus,
textarea:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
}

/* Select Box */
.select-wrapper {
    position: relative;
}

.select-wrapper select {
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
}

.select-wrapper::after {
    content: "⌵";
    position: absolute;
    right: 14px;
    top: 38%;
    transform: translateY(-50%);
    pointer-events: none;
    font-size: 18px;
    font-weight: bold;
    color: #64748b;
}

/* Need / Want Pill Buttons */
.type-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.type-pill {
    position: relative;
    cursor: pointer;
    margin: 0;
}

.type-pill input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.pill-body {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 11px;
    border-radius: 25px;
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    transition: all 0.2s ease;
}

.pill-title {
    font-size: 14px;
    font-weight: 600;
    color: #475569;
}

.pill-desc {
    font-size: 12px;
    color: #94a3b8;
}

/* Active Need Pill */
.need-pill input:checked + .pill-body {
    background: #ecfdf5;
    border-color: #059669;
}

.need-pill input:checked + .pill-body .pill-title,
.need-pill input:checked + .pill-body .pill-desc {
    color: #047857;
}

/* Active Want Pill */
.want-pill input:checked + .pill-body {
    background: #fff7ed;
    border-color: #f59e0b;
}

.want-pill input:checked + .pill-body .pill-title,
.want-pill input:checked + .pill-body .pill-desc {
    color: #b45309;
}

/* 2 Column Layout (Item & Price) */
.form-row-2col {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 15px;
}

/* Item Field Icon */
.input-with-icon {
    position: relative;
}

.input-with-icon .left-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 15px;
    pointer-events: none;
}

.input-with-icon input {
    padding-left: 36px;
}

/* Price Box */
.price-input-box {
    display: flex;
    align-items: center;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    background: #ffffff;
    overflow: hidden;
}

.price-input-box .currency-tag {
    padding: 11px 12px;
    font-weight: 700;
    color: #dc2626;
    font-size: 14px;
    background: #f1f5f9;
    border-right: 1.5px solid #cbd5e1;
}

.price-input-box input {
    border: none;
    background: #ffffff;
    border-radius: 0;
}

.price-input-box input:focus {
    box-shadow: none;
    background: #ffffff;
}

/* Textarea */
textarea {
    resize: vertical;
    min-height: 70px;
}

/* Custom Date Box */
.date-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.date-input-wrapper .cal-icon {
    position: absolute;
    left: 12px;
    pointer-events: none;
}

.date-input-wrapper input {
    padding-left: 38px;
    color-scheme: light;
}

/* Submit Button */
.submit-btn {
    background: #dc2626;
    color: #ffffff;
    border: none;
    padding: 12px 22px;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    width: 100%;
    transition: background 0.2s;
    margin-top: 5px;
}

.submit-btn:hover {
    background: #b91c1c;
}

.form-error {
    color: #dc2626;
    margin-top: 10px;
    font-weight: 600;
}

@media(max-width: 580px) {
    .expense-form-page {
        padding: 20px 15px;
    }

    .form-row-2col {
        grid-template-columns: 1fr;
    }

    .form-card {
        padding: 16px;
    }
}
</style>

<div class="expense-form-page">
    <div class="form-box">
        
        <!-- Header -->
        <div class="form-header">
            <h2><?php echo $label; ?> Expense 💸</h2>
            <p>Record where your money was spent</p>
            <a class="back-link" href="expense.php">← Back to Expense</a>
        </div>

        <form method="post">

            <!-- Category Card -->
            <div class="form-card">
                <div class="form-group">
                    <label>📂 Expense Category</label>

                    <div class="select-wrapper">
                        <select name="category_id" required>
                            <option value="">Select Expense Category</option>

                            <?php while($cat=mysqli_fetch_assoc($cat_res)){ ?>
                                <option value="<?php echo $cat['id']; ?>"
                                    <?php if($category_id==$cat['id']) echo "selected"; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php } ?>

                        </select>
                    </div>
                </div>
            </div>

            <!-- Need / Want Toggle Card -->
            <div class="form-card">
                <div class="form-group">
                    <label>🎯 Expense Type</label>

                    <div class="type-options">

                        <label class="type-pill need-pill">
                            <input type="radio" name="expense_type" value="Need"
                                <?php if($expense_type=="Need") echo "checked"; ?>>

                            <div class="pill-body">
                                <span class="pill-title">✓ Need</span>
                                <span class="pill-desc">Necessary expense</span>
                            </div>
                        </label>

                        <label class="type-pill want-pill">
                            <input type="radio" name="expense_type" value="Want"
                                <?php if($expense_type=="Want") echo "checked"; ?>>

                            <div class="pill-body">
                                <span class="pill-title">⭐ Want</span>
                                <span class="pill-desc">Optional expense</span>
                            </div>
                        </label>

                    </div>
                </div>
            </div>

            <!-- Details Card -->
            <div class="form-card">

                <div class="form-row-2col">

                    <!-- Item -->
                    <div class="form-group">
                        <label>🛒 Item</label>

                        <div class="input-with-icon">
                            <span class="left-icon">🛒</span>

                            <input type="text"
                                name="item"
                                required
                                placeholder="e.g. Lunch, Bus ticket, Shoes..."
                                value="<?php echo htmlspecialchars($item); ?>">
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="form-group">
                        <label>💰 Price</label>

                        <div class="price-input-box">
                            <span class="currency-tag">Rs.</span>

                            <input type="number"
                                name="price"
                                step="0.01"
                                min="0"
                                required
                                placeholder="0.00"
                                value="<?php echo htmlspecialchars($price); ?>">
                        </div>
                    </div>

                </div>

                <!-- Description -->
                <div class="form-group">
                    <label>📝 What was this expense for?</label>

                    <textarea name="details"
                        required
                        placeholder="Describe what you spent this money on..."><?php echo htmlspecialchars($details); ?></textarea>
                </div>

                <!-- Date -->
                <div class="form-group">
                    <label>📅 Expense Date</label>

                    <div class="date-input-wrapper">
                        <span class="cal-icon">📅</span>

                        <input type="date"
                            name="expense_date"
                            required
                            value="<?php echo htmlspecialchars($expense_date); ?>">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="submit" class="submit-btn">
                    ✓ <?php echo $label; ?> Expense
                </button>

            </div>

        </form>

        <?php if($msg!=""){ echo "<div class='form-error'>$msg</div>"; } ?>

    </div>
</div>

<?php include('footer.php'); ?>
