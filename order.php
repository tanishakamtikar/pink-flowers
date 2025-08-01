<?php
session_start();
include 'connection.php'; // $conn

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];
$totalItems = 0;
$totalAmount = 0.00;
$products = [];

if (!empty($cart)) {
    $ids = implode(',', array_map('intval', array_keys($cart)));

    // Fetch products from DB
    $result = $conn->query("SELECT id, name, price, image FROM products WHERE id IN ($ids)");

    while ($row = $result->fetch_assoc()) {
        $products[$row['id']] = $row;
    }

    // Calculate totals
    foreach ($cart as $productId => $qty) {
        if (isset($products[$productId])) {
            $totalItems += $qty;
            $totalAmount += $products[$productId]['price'] * $qty;
        }
    }
}

$orderPlaced = false;
$error = '';

// --- Order Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $payment = $_POST['payment'] ?? '';
    $user_id = $_SESSION['user_id'] ?? 0;

    if (empty($customer_name) || empty($email) || empty($address) || empty($phone) || empty($payment)) {
        $error = "Please fill in all required fields.";
    } elseif ($totalItems === 0) {
        $error = "Your cart is empty.";
    } else {
        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, email, address, phone, payment_method, total_items, total_price, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("isssssid", $user_id, $customer_name, $email, $address, $phone, $payment, $totalItems, $totalAmount);
            $stmt->execute();
            $orderId = $stmt->insert_id;
            $stmt->close();

            $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");

            foreach ($cart as $productId => $qty) {
                if (!isset($products[$productId])) continue;
                $product = $products[$productId];

                $itemStmt->bind_param("iisid", $orderId, $productId, $product['name'], $qty, $product['price']);
                $itemStmt->execute();
            }
            $itemStmt->close();

            $conn->commit();
            $_SESSION['cart'] = [];
            $orderPlaced = true;

        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            error_log("Order insertion failed: " . $e->getMessage());
            $error = "Failed to place order. Please try again.";
        }
    }
}

?>




<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="style.css">
    <style>
       
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<?php if ($orderPlaced): ?>
    <div class="confirmation">
        <h2>🌸 Thank you for your order!</h2>
        <p>Your order has been successfully placed.</p>
        <a href="products.php" class="update-btn">Continue Shopping</a>
    </div>
<?php else: ?>
    <div class="order-form">
        <h2>Checkout</h2>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="order.php" class="login-portal">
            <label>Name</label>
            <input type="text" name="name" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Address</label>
            <textarea name="address" required></textarea>

            <label>Phone</label>
            <input type="text" name="phone" required>

            <label>Payment Method</label>
            <select name="payment" required>
                <option value="cod">Cash on Delivery</option>
                <option value="card">Credit/Debit Card</option>
                <option value="paypal">PayPal</option>
            </select>

            <p><strong>Total Items:</strong> <?= $totalItems ?></p>
            <p><strong>Total Amount:</strong> €<?= number_format($totalAmount, 2) ?></p>

            <button type="submit">Place Order</button>
        </form>
    </div>
<?php endif; ?>

</body>
</html>