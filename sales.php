<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Handle new sale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sale'])) {
    // Insert sale record
    $stmt = $pdo->prepare("INSERT INTO sales (product_id, quantity, sale_price) VALUES (?, ?, ?)");
    $stmt->execute([
        $_POST['product_id'],
        $_POST['quantity'],
        $_POST['sale_price']
    ]);
    
    // Update product quantity
    $stmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
    $stmt->execute([
        $_POST['quantity'],
        $_POST['product_id']
    ]);
    
    $_SESSION['message'] = "Sale recorded successfully!";
    header("Location: sales.php");
    exit();
}

// Get products for dropdown
$products = $pdo->query("SELECT id, name, price FROM products WHERE quantity > 0 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Record New Sale</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product</label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="">Select Product</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['price']; ?>">
                                    <?php echo $product['name']; ?> ($<?php echo number_format($product['price'], 2); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="sale_price" class="form-label">Sale Price (per unit)</label>
                        <input type="number" step="0.01" class="form-control" id="sale_price" name="sale_price" required>
                    </div>
                    <button type="submit" name="add_sale" class="btn btn-primary">Record Sale</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Sales History</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("
                            SELECT s.*, p.name as product_name 
                            FROM sales s 
                            JOIN products p ON s.product_id = p.id 
                            ORDER BY s.sale_date DESC
                        ");
                        $totalSales = 0;
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $total = $row['quantity'] * $row['sale_price'];
                            $totalSales += $total;
                            echo "<tr>
                                <td>".date('M j, Y', strtotime($row['sale_date']))."</td>
                                <td>{$row['product_name']}</td>
                                <td>{$row['quantity']}</td>
                                <td>$".number_format($row['sale_price'], 2)."</td>
                                <td>$".number_format($total, 2)."</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">Total Sales</th>
                            <th>$<?php echo number_format($totalSales, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Set sale price to product price when product is selected
document.getElementById('product_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        document.getElementById('sale_price').value = selectedOption.getAttribute('data-price');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>