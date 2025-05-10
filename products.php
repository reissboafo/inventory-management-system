<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        // Add new product
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, quantity, reorder_level, supplier_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['price'],
            $_POST['quantity'],
            $_POST['reorder_level'],
            $_POST['supplier_id']
        ]);
        $_SESSION['message'] = "Product added successfully!";
    } elseif (isset($_POST['update_product'])) {
        // Update product
        $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, quantity=?, reorder_level=?, supplier_id=? WHERE id=?");
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['price'],
            $_POST['quantity'],
            $_POST['reorder_level'],
            $_POST['supplier_id'],
            $_POST['product_id']
        ]);
        $_SESSION['message'] = "Product updated successfully!";
    } elseif (isset($_GET['delete'])) {
        // Delete product
        $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['message'] = "Product deleted successfully!";
    }
    header("Location: products.php");
    exit();
}

// Get suppliers for dropdown
$suppliers = $pdo->query("SELECT id, name FROM suppliers")->fetchAll(PDO::FETCH_ASSOC);

// Check if editing
$editMode = false;
$productToEdit = [];
if (isset($_GET['edit'])) {
    $editMode = true;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $productToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="card mb-4">
    <div class="card-header">
        <h5><?php echo $editMode ? 'Edit Product' : 'Add New Product'; ?></h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <?php if ($editMode): ?>
                <input type="hidden" name="product_id" value="<?php echo $productToEdit['id']; ?>">
            <?php endif; ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="name" name="name" required 
                        value="<?php echo $editMode ? $productToEdit['name'] : ''; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" required 
                        value="<?php echo $editMode ? $productToEdit['price'] : ''; ?>">
                </div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="2"><?php echo $editMode ? $productToEdit['description'] : ''; ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" required 
                        value="<?php echo $editMode ? $productToEdit['quantity'] : ''; ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="reorder_level" class="form-label">Reorder Level</label>
                    <input type="number" class="form-control" id="reorder_level" name="reorder_level" required 
                        value="<?php echo $editMode ? $productToEdit['reorder_level'] : '5'; ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="supplier_id" class="form-label">Supplier</label>
                    <select class="form-select" id="supplier_id" name="supplier_id">
                        <option value="">Select Supplier</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?php echo $supplier['id']; ?>" 
                                <?php if ($editMode && $productToEdit['supplier_id'] == $supplier['id']) echo 'selected'; ?>>
                                <?php echo $supplier['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" name="<?php echo $editMode ? 'update_product' : 'add_product'; ?>" class="btn btn-primary">
                <?php echo $editMode ? 'Update Product' : 'Add Product'; ?>
            </button>
            <?php if ($editMode): ?>
                <a href="products.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Product List</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Reorder</th>
                    <th>Supplier</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("
                    SELECT p.*, s.name as supplier_name 
                    FROM products p 
                    LEFT JOIN suppliers s ON p.supplier_id = s.id 
                    ORDER BY p.name
                ");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $qtyClass = ($row['quantity'] <= $row['reorder_level']) ? 'text-danger fw-bold' : '';
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>".substr($row['description'], 0, 30)."...</td>
                        <td>$".number_format($row['price'], 2)."</td>
                        <td class='$qtyClass'>{$row['quantity']}</td>
                        <td>{$row['reorder_level']}</td>
                        <td>".($row['supplier_name'] ?? 'N/A')."</td>
                        <td>
                            <a href='products.php?edit={$row['id']}' class='btn btn-sm btn-warning'><i class='fas fa-edit'></i></a>
                            <a href='products.php?delete={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'><i class='fas fa-trash'></i></a>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>