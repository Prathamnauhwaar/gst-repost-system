<?php
// Connect to the database
$conn = new mysqli("localhost", "root", "root", "gst_invoice");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $user_name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Insert order into the database
    $stmt = $conn->prepare("INSERT INTO orders (product_id, user_name, phone, address)VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $product_id, $user_name, $phone, $address);
    $stmt->execute();
}

// Fetch products to display
$result = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            fot-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
           color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        form {
            margin-top: 20px;
            text-align: center;
        }
        input[type="date"] {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
           cursor: pointer;
            margin-left: 10px;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        .receipt {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
    </style>
</head>
<body>
<h1>Order Products</h1>
<form method="post">
    <select name="product_id" required>
        <option value="">Select a product</option>
        <?php while ($row = $result->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['name']) ?> - Price: ₹<?= htmlspecialchars($row['price']) ?> - GST: <?= htmlspecialchars($row['gst_percentage']) ?>%</option>
        <?php endwhile; ?>
    </select><br>
    
    Name: <input type="text" name="name" required><br>
    Phone: <input type="text" name="phone" required><br>
    Address: <textarea name="address" required></textarea><br>
    
    <input type="submit" value="Order">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch last order details for receipt
    $last_order = $conn->insert_id;
    $order_result = $conn->query("SELECT o.*, p.name AS product_name, p.price AS product_price, p.gst_percentage FROM orders o JOIN products p ON o.product_id = p.id WHERE o.id = $last_order");
    
    if ($order_row = $order_result->fetch_assoc()) {
        // Calculate GST and Total Amount
        $price = (float)$order_row['product_price'];
        $gst_percentage = (float)$order_row['gst_percentage'];
        $gst_amount = ($gst_percentage / 100) * $price;
        $total_amount = $price + $gst_amount;

        echo "<h2>Receipt</h2>";
        echo "Name: " . htmlspecialchars($order_row['user_name']) . "<br>";
        echo "Phone: " . htmlspecialchars($order_row['phone']) . "<br>";
        echo "Address: " . htmlspecialchars($order_row['address']) . "<br>";
        echo "Product: " . htmlspecialchars($order_w['product_name']) . "<br>";
        echo "Price: ₹" . number_format($price, 2) . "<br>";
        echo "GST (" . number_format($gst_percentage, 2) . "%): ₹" . number_format($gst_amount, 2) . "<br>";
        echo "Total Amount: ₹" . number_format($total_amount, 2) . "<br>";
        echo "Order Date: " . htmlspecialchars($order_row['order_date']) . "<br>";
        echo "Status: " . htmlspecialchars($order_row['status']) . "<br>";
    }
}
?>
</body>
</html>