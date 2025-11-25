<div class="container" style="margin-top: 50px; margin-bottom: 80px;">
    <div class="page-header">
        <h1>Manage Orders</h1>
        <a href="/admin/dashboard" class="btn btn-back">Back</a>
    </div>

    <div id="orders-list" class="orders-wrapper">
        <div class="loading-state">Loading orders...</div>
    </div>
</div>

<style>
    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .page-header h1 {
        font-family: var(--font-primary);
        font-size: 1.8rem;
        margin: 0;
    }
    .btn-back {
        background: white;
        color: black;
        border: 1px solid #ddd;
        padding: 10px 25px;
        border-radius: 20px;
    }

    /* Order Card Styling */
    .order-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        transition: transform 0.2s;
    }
    
    /* Header within the card */
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 15px;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    .order-id {
        font-family: var(--font-mono);
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 5px;
    }
    .order-meta {
        font-size: 0.85rem;
        color: #666;
    }
    .order-pricing {
        text-align: right;
    }
    .order-total {
        font-weight: 900;
        font-size: 1.2rem;
        display: block;
        margin-bottom: 5px;
    }
    
    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: white;
    }

    /* Grid Layout for Content */
    .order-body {
        display: grid;
        grid-template-columns: 1fr 1fr; /* Desktop: 2 columns */
        gap: 30px;
    }

    /* Section Headers */
    .section-title {
        font-size: 0.8rem;
        text-transform: uppercase;
        color: #999;
        font-weight: 700;
        margin-bottom: 12px;
        letter-spacing: 1px;
    }

    /* Items List */
    .item-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f9f9f9;
        font-size: 0.95rem;
    }
    .item-row:last-child { border-bottom: none; }

    /* Action Area */
    .action-group {
        display: flex;
        gap: 10px;
    }
    .status-select {
        flex-grow: 1;
        padding: 10px;
        border-radius: 10px;
        border: 1px solid #ccc;
        background: #fff;
        font-family: var(--font-secondary);
    }
    .update-btn {
        padding: 10px 20px;
        font-size: 0.9rem;
    }
    .note-input {
        width: 100%;
        padding: 10px;
        border-radius: 10px;
        border: 1px solid #eee;
        margin-top: 10px;
        font-family: var(--font-secondary);
    }

    .shipping-info {
        margin-top: 20px;
        font-size: 0.9rem;
        color: #555;
        background: #f9f9f9;
        padding: 10px;
        border-radius: 10px;
    }

    /* MOBILE RESPONSIVENESS */
    @media (max-width: 768px) {
        .order-body {
            grid-template-columns: 1fr; /* Stack vertically on mobile */
            gap: 20px;
        }
        
        .order-header {
            flex-direction: column;
        }
        
        .order-pricing {
            text-align: left;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }
        
        .action-group {
            flex-direction: column;
        }
        
        .update-btn {
            width: 100%;
        }
    }
</style>

<script>
    async function loadOrders() {
        const container = document.getElementById('orders-list');
        try {
            const res = await fetch('/api/admin/orders');
            const orders = await res.json();

            if (orders.length === 0) {
                container.innerHTML = '<div style="text-align:center; padding:40px; color:#666;">No orders found.</div>';
                return;
            }

            let html = '';
            orders.forEach(order => {
                // Determine color for status
                let statusBg = '#999';
                if(order.status === 'paid') statusBg = '#27ae60'; // Green
                if(order.status === 'confirmed') statusBg = '#f39c12'; // Orange
                if(order.status === 'shipped') statusBg = '#2980b9'; // Blue
                if(order.status === 'out_for_delivery') statusBg = '#8e44ad'; // Purple
                if(order.status === 'delivered') statusBg = '#000'; // Black

                // Format Items
                let itemsHtml = order.items.map(item => 
                    `<div class="item-row">
                        <span>${item.title}</span>
                        <span style="font-weight:600;">x${item.qty}</span>
                    </div>`
                ).join('');

                // Date Formatting
                let dateStr = "Unknown Date";
                // Try to parse date safely
                if(order.created_at_formatted) {
                     dateStr = order.created_at_formatted;
                } else if(order.created_at && order.created_at.$date) {
                     dateStr = new Date(parseInt(order.created_at.$date.$numberLong)).toLocaleDateString();
                }

                const oid = order._id.$oid;

                html += `
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">#${order.invoice_number}</div>
                            <div class="order-meta">${dateStr}</div>
                            <div class="order-meta">${order.customer.email}</div>
                        </div>
                        <div class="order-pricing">
                            <span class="order-total">₦${new Intl.NumberFormat().format(order.total_amount)}</span>
                            <span class="status-badge" style="background: ${statusBg};">
                                ${order.status.replace(/_/g, ' ')}
                            </span>
                        </div>
                    </div>

                    <div class="order-body">
                        <div>
                            <div class="section-title">Items</div>
                            <div style="margin-bottom: 15px;">${itemsHtml}</div>
                            
                            <div class="shipping-info">
                                <strong>Ship To:</strong><br>
                                ${order.customer.address}<br>
                                ${order.customer.phone}
                            </div>
                        </div>

                        <div>
                            <div class="section-title">Update Status</div>
                            <div class="action-group">
                                <select id="status-${oid}" class="status-select">
                                    <option value="paid" ${order.status=='paid'?'selected':''}>Paid</option>
                                    <option value="confirmed" ${order.status=='confirmed'?'selected':''}>Confirmed</option>
                                    <option value="shipped" ${order.status=='shipped'?'selected':''}>Shipped</option>
                                    <option value="out_for_delivery" ${order.status=='out_for_delivery'?'selected':''}>Out for Delivery</option>
                                    <option value="delivered" ${order.status=='delivered'?'selected':''}>Delivered</option>
                                </select>
                                <button onclick="updateStatus('${oid}')" class="btn update-btn">Update</button>
                            </div>
                            <input type="text" id="note-${oid}" class="note-input" placeholder="Add internal status note...">
                        </div>
                    </div>
                </div>`;
            });

            container.innerHTML = html;

        } catch (e) {
            console.error(e);
            container.innerHTML = '<p style="color:red; text-align:center;">Error loading orders. Please check console.</p>';
        }
    }

    async function updateStatus(id) {
        const status = document.getElementById(`status-${id}`).value;
        const note = document.getElementById(`note-${id}`).value;

        if(!confirm(`Change status to ${status}?`)) return;

        try {
            const res = await fetch(`/api/admin/order/${id}/status`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status, note })
            });
            
            if(res.ok) {
                alert('Status Updated');
                loadOrders(); // Refresh
            } else {
                alert('Update failed');
            }
        } catch(e) {
            alert('Network error');
        }
    }

    // Load on init
    loadOrders();
</script>