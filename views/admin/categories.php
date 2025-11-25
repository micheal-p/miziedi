<div class="container" style="margin-top: 50px; margin-bottom: 80px;">
    
    <div class="admin-header">
        <h1 style="font-family: var(--font-primary); font-size: 2rem;">Manage Categories</h1>
        <a href="/admin/dashboard" class="btn back-btn">Back</a>
    </div>

    <div class="admin-layout">
        
        <div class="admin-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 id="formTitle" style="margin: 0; font-family: var(--font-primary);">Add Category</h3>
                <button id="cancelEdit" onclick="resetForm()">Cancel</button>
            </div>

            <form id="catForm">
                <input type="hidden" id="catId"> <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" id="catName" required placeholder="e.g. Hiking Gear">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn" id="submitBtn" style="width: 100%;">Create Category</button>
                </div>
            </form>
        </div>

        <div class="admin-card">
            <h3>Existing Categories</h3>
            <ul id="catList" class="category-list">
                <li>Loading...</li>
            </ul>
        </div>
    </div>
</div>

<style>
    /* Layout */
    .admin-layout {
        display: grid;
        grid-template-columns: 1fr 1fr; /* Desktop: Side by Side */
        gap: 50px;
    }

    .admin-header {
        display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;
    }

    .admin-card {
        background: #f9f9f9;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        height: fit-content;
    }

    .back-btn { background: white; color: black; border: 1px solid #ddd; padding: 10px 25px; font-size: 0.9rem; }

    /* Form */
    .form-group { margin-bottom: 20px; }
    label { font-family: var(--font-primary); font-size: 0.9rem; font-weight: 600; margin-bottom: 8px; display: block; margin-left: 10px; }
    
    /* List Styling */
    .category-list { list-style: none; padding: 0; margin-top: 20px; }
    .category-item {
        padding: 15px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        margin-bottom: 10px;
        border-radius: 10px;
    }
    .category-info strong { display: block; font-size: 1rem; }
    .category-info span { color: #888; font-size: 0.8rem; }

    .edit-btn {
        background: var(--black); color: white; border: none;
        padding: 5px 15px; border-radius: 15px; cursor: pointer; font-size: 0.8rem;
    }
    
    #cancelEdit {
        background: #ddd; border: none; padding: 5px 10px; 
        border-radius: 10px; cursor: pointer; display: none; font-size: 0.8rem;
    }

    /* --- MOBILE RESPONSIVE --- */
    @media (max-width: 768px) {
        .admin-layout { grid-template-columns: 1fr; gap: 30px; } /* Stack vertically */
        .admin-header { flex-direction: column; gap: 15px; text-align: center; }
        .back-btn { width: 100%; }
        .admin-card { padding: 20px; }
    }
</style>

<script>
    // Load Categories
    async function loadCats() {
        const list = document.getElementById('catList');
        try {
            const res = await fetch('/api/categories');
            const data = await res.json();
            
            if(data.length === 0) {
                list.innerHTML = '<p style="text-align:center; color:#666;">No categories yet.</p>';
                return;
            }

            list.innerHTML = data.map(c => {
                // Store object in data attribute for easy editing
                const json = JSON.stringify(c).replace(/"/g, '&quot;');
                return `
                <li class="category-item">
                    <div class="category-info">
                        <strong>${c.name}</strong> 
                        <span>/${c.slug}</span>
                    </div>
                    <button onclick="editCategory(${json})" class="edit-btn">Edit</button>
                </li>`;
            }).join('');
        } catch (e) {
            list.innerHTML = '<p style="color:red">Error loading categories</p>';
        }
    }

    // Populate Form for Editing
    function editCategory(data) {
        document.getElementById('catId').value = data._id.$oid;
        document.getElementById('catName').value = data.name;
        
        // UI Changes
        document.getElementById('formTitle').innerText = "Edit Category";
        document.getElementById('submitBtn').innerText = "Update Category";
        document.getElementById('cancelEdit').style.display = 'block';
        
        // Scroll to form on mobile
        document.querySelector('.admin-layout').scrollIntoView({ behavior: 'smooth' });
    }

    // Reset Form
    function resetForm() {
        document.getElementById('catForm').reset();
        document.getElementById('catId').value = '';
        document.getElementById('formTitle').innerText = "Add Category";
        document.getElementById('submitBtn').innerText = "Create Category";
        document.getElementById('cancelEdit').style.display = 'none';
    }

    // Handle Submit (Create or Update)
    document.getElementById('catForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('catName').value;
        const id = document.getElementById('catId').value;
        const btn = document.getElementById('submitBtn');
        
        btn.innerText = "Processing...";
        btn.disabled = true;

        // Determine Logic
        const method = 'POST'; // We use POST for both for simplicity with JSON body
        const url = id ? `/api/admin/category/${id}` : '/api/admin/category';

        try {
            const res = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: name })
            });

            if(res.ok) {
                resetForm();
                loadCats();
            } else {
                alert('Operation failed');
            }
        } catch(err) {
            alert('Network Error');
        } finally {
            btn.disabled = false;
            // Text will reset via resetForm() or manual reset
            if(!id) btn.innerText = "Create Category";
        }
    });

    loadCats();
</script>