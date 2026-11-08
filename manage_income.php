
<?php
include('header.php');
checkUser();
userArea();

$msg = "";
$source = "";
$income_category = "";
$amount = "";
$details = "";
$income_date = date('Y-m-d');
$label = "Add";

if (isset($_GET['id']) && $_GET['id'] > 0) {
    $label = "Edit";
    $id = get_safe_value($_GET['id']);

    $res = mysqli_query($con, "SELECT * FROM income WHERE id=$id");

    if (mysqli_num_rows($res) == 0) {
        redirect('income.php');
        die();
    }

    $row = mysqli_fetch_assoc($res);

    if ($row['added_by'] != $_SESSION['UID']) {
        redirect('income.php');
        die();
    }

    $source = $row['source'];
    $income_category = $row['income_category'];
    $amount = $row['amount'];
    $details = $row['details'];
    $income_date = $row['income_date'];
}

if (isset($_POST['submit'])) {
    $source = get_safe_value($_POST['source']);
    $income_category = get_safe_value($_POST['income_category']);
    $amount = get_safe_value($_POST['amount']);
    $details = get_safe_value($_POST['details']);
    $income_date = get_safe_value($_POST['income_date']);

    $added_on = date('Y-m-d H:i:s');
    $added_by = $_SESSION['UID'];

    if (isset($_GET['id']) && $_GET['id'] > 0) {
        mysqli_query($con, "UPDATE income SET
        source='$source',
        income_category='$income_category',
        amount='$amount',
        details='$details',
        income_date='$income_date'
        WHERE id=$id");
    } else {
        mysqli_query($con, "INSERT INTO income
        (source, income_category, amount, details, added_on, income_date, added_by)
        VALUES
        ('$source', '$income_category', '$amount', '$details', '$added_on', '$income_date', '$added_by')");
    }

    redirect('income.php');
}

/* Load Admin Income Categories */
$categoryResult = mysqli_query(
    $con,
    "SELECT name FROM category
     WHERE type = 'Income'
     AND created_by = 'Admin'
     ORDER BY id ASC"
);

include('user_header.php');
?>

<style>

.income-form-wrapper {
    padding: 25px 30px;
    background-color: #f4f7fb;
    min-height: 100vh;
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.income-card-box {
    width: 650px;
    max-width: 100%;
    margin: 0;
}

.income-header-title {
    margin-bottom: 20px;
    text-align: left;
}

.income-header-title h2 {
    font-size: 26px;
    color: #172033;
    font-weight: 800;
    margin: 0 0 4px 0;
}

.income-header-title p {
    color: #64748b;
    font-size: 14px;
    margin: 0 0 8px 0;
}

.back-to-inc {
    display: inline-block;
    color: #2563eb;
    font-size: 14px;
    text-decoration: none;
    font-weight: 600;
}

.back-to-inc:hover {
    text-decoration: underline;
}

.custom-card {
    background: #ffffff;
    border: 1px solid #dbe3ed;
    border-radius: 12px;
    padding: 18px 22px;
    margin-bottom: 16px;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
}

.custom-group {
    margin-bottom: 15px;
}

.custom-group:last-child {
    margin-bottom: 0;
}

.custom-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
}

.custom-input,
.custom-select,
.custom-textarea {
    width: 100%;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 14px;
    color: #1e293b;
    background: #ffffff;
    outline: none;
    box-sizing: border-box;
}

.custom-input::placeholder,
.custom-textarea::placeholder {
    color: #94a3b8;
}

.custom-input:focus,
.custom-select:focus,
.custom-textarea:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
}

.select-container {
    position: relative;
}

.select-container select {
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
}

.select-container::after {
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

.two-col-grid {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 15px;
}

.icon-input-box {
    position: relative;
}

.icon-input-box .left-ico {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 15px;
    pointer-events: none;
}

.icon-input-box input {
    padding-left: 36px;
}

.price-box-wrapper {
    display: flex;
    align-items: center;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    background: #ffffff;
    overflow: hidden;
}

.price-box-wrapper .currency-label {
    padding: 11px 12px;
    font-weight: 700;
    color: #047857;
    font-size: 14px;
    background: #f1f5f9;
    border-right: 1.5px solid #cbd5e1;
}

.price-box-wrapper input {
    border: none;
    background: #ffffff;
    border-radius: 0;
}

.custom-textarea {
    resize: vertical;
    min-height: 70px;
}

.date-input-container {
    position: relative;
    display: flex;
    align-items: center;
}

.date-input-container .cal-ico {
    position: absolute;
    left: 12px;
    pointer-events: none;
}

.date-input-container input {
    padding-left: 38px;
    color-scheme: light;
}

.btn-submit-form {
    background: #047857;
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

.btn-submit-form:hover {
    background: #065f46;
}

.error-msg {
    color: #dc2626;
    margin-top: 10px;
    font-weight: 600;
}

@media(max-width: 580px) {
    .income-form-wrapper {
        padding: 20px 15px;
    }

    .two-col-grid {
        grid-template-columns: 1fr;
    }

    .custom-card {
        padding: 16px;
    }
}

</style>

<div class="income-form-wrapper">
    <div class="income-card-box">

        <!-- Header -->
        <div class="income-header-title">
            <h2><?php echo $label; ?> Income 💰</h2>
            <p>Record your income details</p>
            <a class="back-to-inc" href="income.php">← Back to Income</a>
        </div>

        <form method="post">

            <!-- Category Card -->
            <div class="custom-card">
                <div class="custom-group">
                    <label>📂 Income Category</label>

                    <div class="select-container">
                        <select name="income_category" class="custom-select" required>
                            <option value="">Select Income Category</option>

                            <?php while ($categoryRow = mysqli_fetch_assoc($categoryResult)) { ?>
                                <option value="<?php echo htmlspecialchars($categoryRow['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    <?php if ($income_category == $categoryRow['name']) echo "selected"; ?>>
                                    <?php echo htmlspecialchars($categoryRow['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php } ?>

                        </select>
                    </div>
                </div>
            </div>

            <!-- Details Card -->
            <div class="custom-card">

                <div class="two-col-grid">

                    <!-- Income Source -->
                    <div class="custom-group">
                        <label>💼 Income Source</label>

                        <div class="icon-input-box">
                            <span class="left-ico">💼</span>

                            <input type="text"
                                name="source"
                                class="custom-input"
                                required
                                placeholder="e.g. ABC Company, Client Name..."
                                value="<?php echo htmlspecialchars($source); ?>">
                        </div>
                    </div>

                    <!-- Amount -->
                    <div class="custom-group">
                        <label>💵 Amount</label>

                        <div class="price-box-wrapper">
                            <span class="currency-label">Rs.</span>

                            <input type="number"
                                name="amount"
                                class="custom-input"
                                step="0.01"
                                min="0"
                                required
                                placeholder="0.00"
                                value="<?php echo htmlspecialchars($amount); ?>">
                        </div>
                    </div>

                </div>

                <!-- Description -->
                <div class="custom-group">
                    <label>📝 Details</label>

                    <textarea name="details"
                        class="custom-textarea"
                        required
                        placeholder="What is this income for?"><?php echo htmlspecialchars($details); ?></textarea>
                </div>

                <!-- Date -->
                <div class="custom-group">
                    <label>📅 Income Date</label>

                    <div class="date-input-container">
                        <span class="cal-ico">📅</span>

                        <input type="date"
                            name="income_date"
                            class="custom-input"
                            required
                            value="<?php echo htmlspecialchars($income_date); ?>">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="submit" class="btn-submit-form">
                    ✓ <?php echo $label; ?> Income
                </button>

            </div>

        </form>

        <?php if ($msg != "") {
            echo "<div class='error-msg'>$msg</div>";
        } ?>

    </div>
</div>

<?php include('footer.php'); ?>
