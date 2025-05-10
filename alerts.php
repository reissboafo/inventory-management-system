<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Get low stock items
$stmt = $pdo->query("
    SELECT p.*, s.name as supplier_name, s.contact_person, s.phone as supplier_phone 
    FROM products p 
    LEFT JOIN suppliers s ON p.supplier_id = s.id 
    WHERE p.quantity <= p.reorder_level 
    ORDER BY p.quantity ASC
");
$lowStockItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header">
        <h5>Low Stock Alerts</h5>
    </div>
    <div class="card-body">
        <?php if (empty($lowStockItems)): ?>
            <div class="alert alert-success">No low stock items at this time.</div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> You have <?php echo count($lowStockItems); ?> item(s) below or at reorder level.
            </div>
            
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Current Qty</th>
                        <th>Reorder Level</th>
                        <th>Supplier</th>
                        <th>Contact</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lowStockItems as $item): ?>
                        <tr>
                            <td><?php echo $item['name']; ?></td>
                            <td class="text-danger fw-bold"><?php echo $item['quantity']; ?></td>
                            <td><?php echo $item['reorder_level']; ?></td>
                            <td>
                                <?php if ($item['supplier_name']): ?>
                                    <?php echo $item['supplier_name']; ?>
                                <?php else: ?>
                                    <span class="text-muted">No supplier</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['supplier_phone']): ?>
                                    <a href="tel:<?php echo $item['supplier_phone']; ?>"><?php echo $item['supplier_phone']; ?></a>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="products.php?edit=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">Restock</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>