<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_supplier'])) {
        // Add new supplier
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_person, email, phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['contact_person'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['address']
        ]);
        $_SESSION['message'] = "Supplier added successfully!";
    } elseif (isset($_POST['update_supplier'])) {
        // Update supplier
        $stmt = $pdo->prepare("UPDATE suppliers SET name=?, contact_person=?, email=?, phone=?, address=? WHERE id=?");
        $stmt->execute([
            $_POST['name'],
            $_POST['contact_person'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['address'],
            $_POST['supplier_id']
        ]);
        $_SESSION['message'] = "Supplier updated successfully!";
    } elseif (isset($_GET['delete'])) {
        // Delete supplier
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id=?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['message'] = "Supplier deleted successfully!";
    }
    header("Location: suppliers.php");
    exit();
}

// Check if editing
$editMode = false;
$supplierToEdit = [];
if (isset($_GET['edit'])) {
    $editMode = true;
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $supplierToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="card mb-4">
    <div class="card-header">
        <h5><?php echo $editMode ? 'Edit Supplier' : 'Add New Supplier'; ?></h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <?php if ($editMode): ?>
                <input type="hidden" name="supplier_id" value="<?php echo $supplierToEdit['id']; ?>">
            <?php endif; ?>
            <div class="mb-3">
                <label for="name" class="form-label">Supplier Name</label>
                <input type="text" class="form-control" id="name" name="name" required 
                    value="<?php echo $editMode ? $supplierToEdit['name'] : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="contact_person" class="form-label">Contact Person</label>
                <input type="text" class="form-control" id="contact_person" name="contact_person" 
                    value="<?php echo $editMode ? $supplierToEdit['contact_person'] : ''; ?>">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                        value="<?php echo $editMode ? $supplierToEdit['email'] : ''; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" class="form-control" id="phone" name="phone" 
                        value="<?php echo $editMode ? $supplierToEdit['phone'] : ''; ?>">
                </div>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="2"><?php echo $editMode ? $supplierToEdit['address'] : ''; ?></textarea>
            </div>
            <button type="submit" name="<?php echo $editMode ? 'update_supplier' : 'add_supplier'; ?>" class="btn btn-primary">
                <?php echo $editMode ? 'Update Supplier' : 'Add Supplier'; ?>
            </button>
            <?php if ($editMode): ?>
                <a href="suppliers.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Supplier List</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['contact_person']}</td>
                        <td>{$row['email']}</td>
                        <td>{$row['phone']}</td>
                        <td>
                            <a href='suppliers.php?edit={$row['id']}' class='btn btn-sm btn-warning'><i class='fas fa-edit'></i></a>
                            <a href='suppliers.php?delete={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'><i class='fas fa-trash'></i></a>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>