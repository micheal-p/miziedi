<?php
// Fetch current settings
$db = \Database::getInstance()->getDb();
$settings = $db->settings->findOne(['type' => 'general']) ?? [];
?>

<div class="container" style="margin-top: 50px; margin-bottom: 80px;">
    
    <div class="admin-header">
        <h1>System Settings</h1>
        <a href="/admin/dashboard" class="btn btn-back">Back to Dashboard</a>
    </div>

    <div class="admin-card">
        <div class="card-header">
            <h3>Invoice & Fees Configuration</h3>
            <p>Manage global settings for your store invoices.</p>
        </div>
        
        <form id="settingsForm" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label>Delivery Fee (₦)</label>
                    <input type="number" name="delivery_fee" value="<?= $settings['delivery_fee'] ?? 10000 ?>" required placeholder="e.g. 10000">
                </div>

                <div class="form-group">
                    <label>Tax Label</label>
                    <input type="text" name="tax_label" value="<?= htmlspecialchars($settings['tax_label'] ?? 'TBD') ?>" required placeholder="e.g. VAT 7.5%">
                </div>

                <div class="form-group full-width">
                    <label>Invoice Tagline</label>
                    <input type="text" name="invoice_tagline" value="<?= htmlspecialchars($settings['invoice_tagline'] ?? 'Premium Gear.') ?>" placeholder="e.g. Premium Gear for the Bold.">
                </div>

                <div class="form-group full-width">
                    <label>CEO Signature</label>
                    <div class="file-upload-wrapper">
                        <input type="file" name="ceo_signature" accept="image/png, image/jpeg" id="sigUpload">
                        <div class="file-preview">
                            <?php if (!empty($settings['ceo_signature'])): ?>
                                <img src="<?= $settings['ceo_signature'] ?>" alt="Current Signature">
                                <span>Current Signature</span>
                            <?php else: ?>
                                <span>No signature uploaded</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-save">Save Settings</button>
                <span id="msg" class="status-msg"></span>
            </div>
        </form>
    </div>
</div>

<style>
    /* Page Header */
    .admin-header {
        display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;
    }
    .admin-header h1 { font-family: var(--font-primary); font-size: 2rem; font-weight: 700; margin: 0; }
    .btn-back { background: #fff; color: #000; border: 1px solid #ddd; padding: 10px 20px; font-size: 0.9rem; }

    /* Card Styling */
    .admin-card {
        background: #fff;
        padding: 40px;
        border-radius: 20px;
        border: 1px solid #eee;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    }
    .card-header { margin-bottom: 30px; border-bottom: 1px solid #f0f0f0; padding-bottom: 20px; }
    .card-header h3 { font-family: var(--font-primary); margin-bottom: 5px; font-size: 1.3rem; }
    .card-header p { color: #666; font-size: 0.9rem; }

    /* Form Grid */
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
    .full-width { grid-column: 1 / -1; }
    
    .form-group { display: flex; flex-direction: column; gap: 8px; }
    label { font-family: var(--font-primary); font-weight: 600; font-size: 0.9rem; margin-left: 10px; }
    
    /* File Upload Styling */
    .file-upload-wrapper {
        display: flex; align-items: center; gap: 20px;
        padding: 15px; border: 1px dashed #ccc; border-radius: 20px; background: #fbfbfb;
    }
    .file-preview { display: flex; align-items: center; gap: 10px; font-size: 0.8rem; color: #666; }
    .file-preview img { height: 40px; width: auto; border: 1px solid #ddd; padding: 2px; background: #fff; }

    /* Actions */
    .form-actions { margin-top: 40px; display: flex; align-items: center; gap: 20px; }
    .btn-save { background: var(--black); color: white; width: auto; min-width: 180px; }
    .status-msg { font-weight: 600; font-size: 0.9rem; }

    /* Responsive */
    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; gap: 20px; }
        .admin-header { flex-direction: column; align-items: flex-start; }
        .file-upload-wrapper { flex-direction: column; align-items: flex-start; }
        .admin-card { padding: 25px; }
    }
</style>

<script>
document.getElementById('settingsForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const btn = e.target.querySelector('button');
    const msg = document.getElementById('msg');
    
    btn.innerText = "Saving...";
    btn.disabled = true;
    msg.innerText = "";

    try {
        const res = await fetch('/api/admin/settings', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (res.ok) {
            msg.innerText = "Settings Saved Successfully!";
            msg.style.color = "green";
            setTimeout(() => location.reload(), 1500);
        } else {
            msg.innerText = data.error || "Error saving.";
            msg.style.color = "red";
        }
    } catch (err) {
        console.error(err);
        msg.innerText = "Network Error";
        msg.style.color = "red";
    } finally {
        btn.innerText = "Save Settings";
        btn.disabled = false;
    }
});
</script>