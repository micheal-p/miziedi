<?php
// Fetch all products for the list
$pModel = new \Miziedi\Models\Product();
$allProducts = $pModel->getAll([], 100);
?>

<div class="container main-wrapper">
    
    <div class="admin-header">
        <h1>Manage Products</h1>
        <a href="/admin/dashboard" class="btn back-btn">Back</a>
    </div>

    <div class="admin-layout">
        
        <div class="admin-card" id="formSection">
            <div class="card-header-row">
                <h3 id="formTitle">Add New Product</h3>
                <button id="cancelEdit" onclick="resetForm()">Cancel</button>
            </div>

            <form id="productForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="p_id">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" name="name" id="p_name" required placeholder="e.g. Men's Thermal Jacket">
                    </div>

                    <div class="form-row-mobile">
                        <div class="form-group">
                            <label>Price (₦)</label>
                            <input type="number" name="price" id="p_price" required placeholder="45000">
                        </div>
                        <div class="form-group">
                            <label>Stock</label>
                            <input type="number" name="stock" id="p_stock" required placeholder="20" value="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" id="p_cat" required>
                            <option value="" disabled selected>Select Category...</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['slug'] ?>"><?= $cat['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Sizes</label>
                        <div class="size-checkboxes">
                            <?php foreach(['XS','S','M','L','XL','XXL'] as $sz): ?>
                                <label class="size-option">
                                    <input type="checkbox" name="sizes[]" value="<?= $sz ?>" class="size-check"> 
                                    <span><?= $sz ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Image <span id="img-hint"></span></label>
                        <input type="file" name="image" id="p_image" accept="image/*" class="file-input">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>Description</label>
                    <textarea name="description" id="p_desc" rows="4" placeholder="Product details..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn save-btn" id="submitBtn">Save Product</button>
                    <span id="msg" class="status-msg"></span>
                </div>
            </form>
        </div>

        <div class="admin-card">
            <div class="list-header">
                <h3>Inventory</h3>
                <input type="text" id="searchBox" placeholder="Search..." class="search-box">
            </div>

            <div class="table-responsive">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Img</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stk</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        <?php foreach($allProducts as $p): ?>
                            <?php 
                                $jsonProduct = htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8');
                                $stock = $p['stock'] ?? 0;
                                $stockColor = $stock > 0 ? '#27ae60' : '#e74c3c';
                            ?>
                            <tr class="product-row">
                                <td>
                                    <img src="<?= $p['image_url'] ?? '/assets/images/PLACEHOLDER.jpg' ?>" class="table-img">
                                </td>
                                <td class="fw-bold product-name-cell"><?= $p['name'] ?? $p['title'] ?></td>
                                <td>₦<?= number_format($p['price']) ?></td>
                                <td style="font-weight: bold; color: <?= $stockColor ?>;">
                                    <?= $stock ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick='editProduct(<?= $jsonProduct ?>)' class="action-btn edit-btn">Edit</button>
                                        <button onclick="deleteProduct('<?= $p['_id'] ?>')" class="action-btn delete-btn">Del</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* --- BASE LAYOUT --- */
    .main-wrapper { margin-top: 30px; margin-bottom: 60px; }
    
    .admin-header {
        display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
    }
    .admin-header h1 { font-size: 1.8rem; margin: 0; font-weight: 800; }
    .back-btn { padding: 8px 20px; font-size: 0.9rem; background: white; color: black; border: 1px solid #ddd; }

    /* Admin Card */
    .admin-card {
        background: #f9f9f9;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        margin-bottom: 30px;
    }

    /* Forms */
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .full-width { grid-column: 1 / -1; }
    
    /* Labels & Inputs */
    label { font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block; margin-left: 5px; }
    
    input, select, textarea, .file-input {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 10px; /* Reduced radius for cleaner look */
        font-size: 0.95rem;
        background: #fff;
        box-sizing: border-box; /* CRITICAL: Prevents overflow */
    }

    /* Form Group wrapper */
    .form-group { margin-bottom: 10px; }

    /* Special Mobile Row (Price/Stock side by side) */
    .form-row-mobile { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

    /* Sizes */
    .size-checkboxes { display: flex; gap: 8px; flex-wrap: wrap; }
    .size-option {
        display: flex; align-items: center; gap: 5px;
        background: white; border: 1px solid #ddd;
        padding: 6px 12px; border-radius: 8px; cursor: pointer;
        margin: 0; font-size: 0.85rem;
    }
    .size-option input { width: auto; margin: 0; }

    /* Header Row inside Card */
    .card-header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    .card-header-row h3 { margin: 0; font-size: 1.2rem; }
    #cancelEdit { background: #eee; border: none; padding: 5px 10px; border-radius: 8px; font-size: 0.8rem; cursor: pointer; display: none; }

    /* Buttons */
    .save-btn { width: 100%; padding: 12px; font-size: 1rem; margin-top: 10px; }
    .status-msg { display: block; text-align: center; margin-top: 10px; font-size: 0.9rem; }

    /* --- INVENTORY TABLE --- */
    .list-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; gap: 10px; }
    .search-box { flex: 1; max-width: 200px; }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch; /* Smooth iOS scroll */
        border-radius: 10px;
        border: 1px solid #eee;
    }

    .product-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 500px; /* Forces scroll on very small screens */
        background: white;
    }
    
    .product-table th { text-align: left; padding: 12px 10px; background: #f4f4f4; font-size: 0.8rem; font-weight: 700; color: #555; }
    .product-table td { padding: 10px; border-bottom: 1px solid #f0f0f0; font-size: 0.85rem; vertical-align: middle; }
    
    .table-img { width: 40px; height: 40px; object-fit: cover; border-radius: 6px; display: block; }
    .product-name-cell { max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .action-buttons { display: flex; gap: 5px; }
    .action-btn { border: none; padding: 5px 10px; border-radius: 6px; cursor: pointer; font-size: 0.75rem; color: white; }
    .edit-btn { background: var(--black); }
    .delete-btn { background: #ff4444; }

    /* --- MOBILE RESPONSIVENESS (MAX 768px) --- */
    @media (max-width: 768px) {
        .container { padding: 0 15px; }
        
        .admin-header { margin-top: 10px; margin-bottom: 20px; }
        .admin-header h1 { font-size: 1.5rem; }
        
        /* Card Adjustments */
        .admin-card { padding: 15px; border-radius: 15px; }
        
        /* Stack Form */
        .form-grid { grid-template-columns: 1fr; gap: 10px; }
        .form-row-mobile { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        
        /* Sizes */
        .size-checkboxes { gap: 5px; }
        .size-option { padding: 5px 10px; font-size: 0.8rem; flex: 1; justify-content: center; }
        
        /* Table area */
        .list-header { flex-direction: column; align-items: stretch; }
        .search-box { max-width: none; width: 100%; }
        
        /* Ensure table text is readable */
        .product-table th, .product-table td { padding: 8px 6px; font-size: 0.8rem; }
        
        /* Stack Actions if needed or keep small */
        .action-btn { padding: 6px 8px; }
    }
</style>

<script>
function editProduct(data) {
    document.getElementById('formSection').scrollIntoView({ behavior: 'smooth' });
    document.getElementById('formTitle').innerText = "Edit: " + (data.name || data.title);
    document.getElementById('submitBtn').innerText = "Update";
    document.getElementById('cancelEdit').style.display = 'block';
    document.getElementById('img-hint').innerText = "(Empty = keep current)";

    document.getElementById('p_id').value = data._id.$oid;
    document.getElementById('p_name').value = data.name || data.title;
    document.getElementById('p_price').value = data.price;
    document.getElementById('p_stock').value = data.stock || 0;
    document.getElementById('p_desc').value = data.description || '';
    document.getElementById('p_cat').value = data.category || data.category_slug;

    const sizeInputs = document.querySelectorAll('.size-check');
    sizeInputs.forEach(cb => cb.checked = false);
    if (data.sizes) {
        const sizes = typeof data.sizes === 'object' ? Object.values(data.sizes) : data.sizes;
        sizeInputs.forEach(cb => { if (sizes.includes(cb.value)) cb.checked = true; });
    }
}

function resetForm() {
    document.getElementById('productForm').reset();
    document.getElementById('p_id').value = '';
    document.getElementById('formTitle').innerText = "Add New Product";
    document.getElementById('submitBtn').innerText = "Save Product";
    document.getElementById('cancelEdit').style.display = 'none';
    document.getElementById('img-hint').innerText = "";
}

document.getElementById('productForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const id = document.getElementById('p_id').value;
    const btn = document.getElementById('submitBtn');
    const msg = document.getElementById('msg');
    
    btn.innerText = "Saving...";
    btn.disabled = true;
    msg.innerText = "";

    let url = id ? '/api/admin/product/' + id : '/api/admin/product';

    try {
        const res = await fetch(url, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (res.ok) {
            msg.innerText = "Saved Successfully!";
            msg.style.color = "green";
            setTimeout(() => location.reload(), 1000);
        } else {
            msg.innerText = data.error || "Error occurred";
            msg.style.color = "red";
            btn.disabled = false;
            btn.innerText = id ? "Update" : "Save Product";
        }
    } catch(err) {
        msg.innerText = "Network Error";
        msg.style.color = "red";
        btn.disabled = false;
    }
});

document.getElementById('searchBox').addEventListener('keyup', function(e) {
    const term = e.target.value.toLowerCase();
    document.querySelectorAll('.product-row').forEach(row => {
        const txt = row.innerText.toLowerCase();
        row.style.display = txt.includes(term) ? '' : 'none';
    });
});

async function deleteProduct(id) {
    if(!confirm("Delete this product?")) return;
    await fetch('/api/admin/product/' + id, { method: 'DELETE' });
    location.reload();
}
</script>