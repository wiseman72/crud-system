<?php
require_once("connection.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM budgets WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
}

if (isset($_POST['submit'])) {
    $id = $_POST['id'];
    $category = $_POST['category'];
    $allocated = $_POST['allocated'];
    $actual = $_POST['actual'];
    $variance = $allocated - $actual;
    $percentage = ($allocated != 0) ? ($actual / $allocated) * 100 : 0;

    $query = "UPDATE budgets SET category = '$category', allocated = '$allocated', actual = '$actual', variance = '$variance', percentage = '$percentage' WHERE id = '$id'";
    mysqli_query($conn, $query);

    header("Location: budget_list.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Budget</title>
    <style>
        /* General body styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f8;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Form container */
        form {
            background-color: #fff;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        /* Form labels */
        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        /* Form inputs */
        form input[type="text"],
        form input[type="number"] {
            width: 100%;
            padding: 12px 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        form input[type="text"]:focus,
        form input[type="number"]:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 5px rgba(74, 144, 226, 0.5);
            outline: none;
        }

        /* Submit button */
        form input[type="submit"] {
            width: 100%;
            padding: 14px;
            background-color: #4a90e2;
            color: #fff;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        form input[type="submit"]:hover {
            background-color: #357ab8;
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 480px) {
            form {
                padding: 20px 15px;
            }

            form input[type="submit"] {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <form action="" method="post">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

        <label>Category:</label>
        <input type="text" name="category" value="<?php echo $row['category']; ?>" required>

        <label>Allocated:</label>
        <input type="number" name="allocated" value="<?php echo $row['allocated']; ?>" required>

        <label>Actual:</label>
        <input type="number" name="actual" value="<?php echo $row['actual']; ?>" required>

        <input type="submit" name="submit" value="Update Budget">
    </form>
</body>
</html>
