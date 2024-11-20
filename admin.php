<?php
// Connect to the database
$conn = new mysqli("localhost", "root", "root", "gst_invoice");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all orders
$result = $conn->query("SELECT o.*, p.name AS product_name, p.price AS product_price, p.gst_percentage FROM orders o JOIN products p ON o.product_id = p.id");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update order status
    if (isset($_POST['shipped'])) {
        $order_id = $_POST['order_id'];
        $stmt = $conn->prepare("UPDATE orders SET status='shipped' WHERE id=?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #3498db;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
        }
       select, input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #2980b9;
       }
        .receipt {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
            margin-top: 20px;
        }
        .receipt h2 {
            color: #27ae60; /* Green color for receipt title */
        }
        .sidebar {
            width: 250px;
            background: #0e0e11;
            color: #fff;
            position: fixed;
            height: 100%;
            padding: 20px;
       }
        .sidebar h2 {
            font-size: 22px;
            margin-bottom: 20px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin: 15px 0;
        }
        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
        }
    </style>
</head>
<body><div class="sidebar">
        <h2>KYC Verification</h2>
        <ul>
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">Accounts</a></li>
            <li><a href="#">Deposit</a></li>
            <li><a href="#">Transfer</a></li>
            <li><a href="#">Withdraw</a></li>
            <li><a href="#">Affiliate</a></li>
            <li><a href="#">Leaderboards</a></li>
            <li><a href="#">FAQ</a></li>
            <li><a href="#">Contact Us</a></li>
            <li><a href="#">Legal Documents</a></li>
        </ul>
    </div>

<h1>Manage Orders</h1>

<table>
<tr>
<th>ID</th>
<th>Product Name</th>
<th>User Name</th>
<th>Phone</th>
<th>Address</th>
<th>Order Date</th>
<th>Status</th>
<th>GST Amount (₹)</th>
<th>Total Amount (₹)</th>
<th>Action</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['id']) ?></td>
<td><?= htmlspecialchars($row['product_name']) ?></td>
<td><?= htmlspecialchars($row['user_name']) ?></td>
<td><?= htmlspecialchars($row['phone']) ?></td>
<td><?= htmlspecialchars($row['address']) ?></td>
<td><?= htmlspecialchars($row['order_date']) ?></td>
<td><?= htmlspecialchars($row['status']) ?></td>

<?php
// Calculate GST and Total Amount
$price = (float)$row['product_price'];
$gst_percentage = (float)$row['gst_percentage'];
$gst_amount = ($gst_percentage / 100) * $price;
$total_amount = $price + $gst_amount;
?>

<td>₹<?= number_format($gst_amount, 2) ?></td>
<td>₹<?= number_format($total_amount, 2) ?></td>

<td>
<form method="post">
<input type="hidden" name="order_id" value="<?= htmlspecialchars($row['id']) ?>">
<button type="submit" name="shipped" style="background-color:#007bff; colorwhite; border:none; padding:5px; border-radius:5px; cursor:pointer;">Mark as Shipped</button>
</form>
</td>
</tr>
<?php endwhile; ?>
</table>

<h2>Fetch Orders by Date:</h2>

<form method="get">
<label for="date">Select Date:</label><input type="date" name="date" id="date" required>
<input type="submit" value="Fetch Orders">
</form>

<?php
if (isset($_GET['date'])) {
    // Fetch orders by selected date
    $selected_date = $_GET['date'];
    
    // Adjust query to fetch by date only
    $orders_by_date_result = $conn->query("SELECT o.*, p.name AS product_name, p.price AS product_price, p.gst_percentage FROM orders o JOIN products p ON o.product_id = p.id WHERE DATE(order_date) = '$selected_date'");
    
    echo "<h3>Orders on " . htmlspecialchars($selected_date) . "</h3>";
    
    while ($row =  $orders_by_date_result->fetch_assoc()) {
        // Calculate GST and Total Amount for fetched date orders
        $price = (float)$row['product_price'];
        $gst_percentage = (float)$row['gst_percentage'];
        $gst_amount = ($gst_percentage / 100) * $price;
        $total_amount = $price + $gst_amount;

        echo "<p>ID: ".htmlspecialchars($row['id'])." - Product: ".htmlspecialchars($row['product_name'])." - User: ".htmlspecialchars($row['user_name'])." - GST: ₹".number_format($gst_amount, 2). " - Status: ".htmlspecialchars($row['status'])."</p>";
    }
}
?>

</body>
</html>
