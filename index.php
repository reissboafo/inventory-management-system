<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Get counts for dashboard
$products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$lowStock = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity <= reorder_level")->fetchColumn();
$suppliers = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
$salesToday = $pdo->query("SELECT COUNT(*) FROM sales WHERE DATE(sale_date) = CURDATE()")->fetchColumn();
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Products</h5>
                <h2 class="card-text"><?php echo $products; ?></h2>
                <a href="products.php" class="text-white">View Products <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Low Stock</h5>
                <h2 class="card-text"><?php echo $lowStock; ?></h2>
                <a href="alerts.php" class="text-dark">View Alerts <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Suppliers</h5>
                <h2 class="card-text"><?php echo $suppliers; ?></h2>
                <a href="suppliers.php" class="text-white">View Suppliers <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Sales Today</h5>
                <h2 class="card-text"><?php echo $salesToday; ?></h2>
                <a href="sales.php" class="text-white">View Sales <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Recent Sales</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("
                            SELECT p.name, s.quantity, s.sale_price, s.sale_date 
                            FROM sales s 
                            JOIN products p ON s.product_id = p.id 
                            ORDER BY s.sale_date DESC 
                            LIMIT 5
                        ");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>
                                <td>{$row['name']}</td>
                                <td>{$row['quantity']}</td>
                                <td>$".number_format($row['sale_price'], 2)."</td>
                                <td>".date('M j, Y', strtotime($row['sale_date']))."</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Low Stock Items</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Current Qty</th>
                            <th>Reorder Level</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("
                            SELECT id, name, quantity, reorder_level 
                            FROM products 
                            WHERE quantity <= reorder_level 
                            ORDER BY quantity ASC 
                            LIMIT 5
                        ");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>
                                <td>{$row['name']}</td>
                                <td class='text-danger'>{$row['quantity']}</td>
                                <td>{$row['reorder_level']}</td>
                                <td><a href='products.php?edit={$row['id']}' class='btn btn-sm btn-warning'>Restock</a></td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>