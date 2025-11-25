<div class="container main-wrapper">
    
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <a href="/" class="btn view-store-btn">View Store</a>
    </div>

    <div class="dashboard-grid">
        
        <a href="/admin/products" class="dashboard-card">
            <h3>Manage Products</h3>
            <p>Add, Edit, Delete & Stock</p>
        </a>

        <a href="/admin/categories" class="dashboard-card">
            <h3>Categories</h3>
            <p>Create & Manage Categories</p>
        </a>

        <a href="/admin/orders" class="dashboard-card">
            <h3>Orders</h3>
            <p>View Status & History</p>
        </a>

        <a href="/admin/settings" class="dashboard-card">
            <h3>Settings</h3>
            <p>Fees, Tax & Invoice Config</p>
        </a>

    </div>
</div>

<style>
    /* --- LAYOUT --- */
    .main-wrapper {
        margin-top: 50px;
        margin-bottom: 80px;
    }

    /* Header */
    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
        flex-wrap: wrap; /* Allow wrapping on small screens */
        gap: 20px;
    }

    .admin-header h1 {
        font-family: var(--font-primary);
        font-size: 2rem;
        font-weight: 800;
        margin: 0;
        line-height: 1.2;
    }

    /* View Store Button */
    .view-store-btn {
        background: white;
        color: var(--black);
        border: 1px solid #ddd;
        padding: 10px 25px;
        font-size: 0.9rem;
        border-radius: 30px;
        white-space: nowrap;
    }
    .view-store-btn:hover {
        background: var(--black);
        color: white;
        border-color: var(--black);
    }

    /* --- DASHBOARD GRID --- */
    .dashboard-grid {
        display: grid;
        /* Auto-fit columns: Minimum 240px, otherwise fill space */
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 25px;
        width: 100%;
    }

    /* Card Styling */
    .dashboard-card {
        background: #f9f9f9;
        padding: 40px 25px;
        border-radius: 25px;
        text-align: center;
        border: 1px solid #eee;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.03);
    }

    .dashboard-card:hover {
        background: var(--black);
        color: white;
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        border-color: var(--black);
    }

    .dashboard-card:active {
        transform: scale(0.98);
    }

    .dashboard-card h3 {
        font-family: var(--font-primary);
        font-size: 1.4rem;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .dashboard-card p {
        font-size: 0.9rem;
        opacity: 0.7;
        margin: 0;
        font-family: var(--font-secondary);
    }

    /* --- MOBILE RESPONSIVENESS (Max 768px) --- */
    @media (max-width: 768px) {
        .main-wrapper {
            margin-top: 30px;
            padding: 0 20px;
        }

        /* Header Stacking */
        .admin-header {
            flex-direction: column;
            align-items: stretch;
            text-align: center;
            gap: 15px;
        }

        .admin-header h1 {
            font-size: 1.8rem;
        }

        .view-store-btn {
            width: 100%; /* Full width button */
        }

        /* Grid Adjustments */
        .dashboard-grid {
            grid-template-columns: 1fr; /* Force single column stack */
            gap: 15px;
        }

        /* Card Adjustments */
        .dashboard-card {
            padding: 30px 20px;
            flex-direction: row; /* Horizontal layout on mobile for cleaner look? */
            /* Let's keep vertical but adjust alignment */
            text-align: left;
            align-items: flex-start;
            justify-content: flex-start;
        }
        
        /* If you prefer cards to look like list items on mobile: */
        .dashboard-card {
            display: block;
            text-align: center;
        }
        
        .dashboard-card h3 {
            font-size: 1.2rem;
        }
    }
</style>