<?php
// 1. Fetch Order Logic
$invoice = $_GET['invoice'] ?? '';
$order = null; $error = null;

if ($invoice) {
    $orderModel = new \Miziedi\Models\Order();
    $order = $orderModel->getByInvoice($invoice);
    if (!$order) $error = "Order with invoice #$invoice not found.";
}

// 2. Fetch Settings
$db = Database::getInstance()->getDb();
$settings = $db->settings->findOne(['type' => 'general']);

// 3. Data Normalization (Fix for Undefined Keys)
if ($order) {
    // Recalculate Subtotal if missing
    $subtotal = $order['subtotal'] ?? 0;
    if ($subtotal == 0 && isset($order['items'])) {
        foreach($order['items'] as $item) {
            $subtotal += $item['price'] * $item['qty'];
        }
    }
    
    // Delivery Fee Logic
    $delivery = $order['delivery_fee'] ?? 0;
    if ($delivery == 0) $delivery = $settings['delivery_fee'] ?? 10000;

    $taxLabel = $order['tax_label'] ?? $settings['tax_label'] ?? 'TBD';
    
    $total = $subtotal + $delivery; 
}
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<div class="container" style="max-width: 900px; margin-top: 40px; margin-bottom: 80px;">
    
    <div style="text-align: center; margin-bottom: 40px;">
        <h1 style="font-family: var(--font-primary); font-size: 1.8rem;">Order Tracking</h1>
        <form action="/track" method="GET" class="track-form">
            <input type="text" name="invoice" value="<?= htmlspecialchars($invoice) ?>" placeholder="INV-..." class="track-input">
            <button type="submit" class="btn track-btn">Search</button>
        </form>
        <?php if($error): ?>
            <p style="color: red; margin-top: 10px; font-size: 0.9rem;"><?= $error ?></p>
        <?php endif; ?>
    </div>

    <?php if ($order): ?>
        
        <div class="status-card">
            <div class="status-header">
                <h3 style="margin: 0; font-family: var(--font-primary);">
                    Status: <span style="color: var(--black); text-transform: uppercase;"><?= str_replace('_', ' ', $order['status']) ?></span>
                </h3>
                <span style="font-size: 0.9rem; color: #666;"><?= $order['created_at']->toDateTime()->format('M d, H:i') ?></span>
            </div>

            <?php 
                $stages = ['paid', 'confirmed', 'shipped', 'out_for_delivery', 'delivered'];
                $currentStatus = strtolower($order['status']);
                $passed = true; 
            ?>
            <div class="timeline-wrapper">
                <div class="timeline-line"></div>
                
                <?php foreach($stages as $stage): ?>
                    <?php 
                        $isCurrent = ($stage === $currentStatus);
                        $isCompleted = $passed && !$isCurrent;
                        
                        // Colors
                        $dotColor = '#ccc'; // Future = Grey
                        $textColor = '#999';
                        
                        if ($isCompleted) {
                            $dotColor = '#2ecc71'; // Completed = Green
                            $textColor = '#2ecc71';
                        } elseif ($isCurrent) {
                            $dotColor = '#2ecc71'; // Current = Green
                            $textColor = '#000'; // Text = Black
                            $passed = false; 
                        } else {
                            $passed = false;
                        }
                    ?>
                    <div class="timeline-item">
                        <div class="timeline-dot" style="background: <?= $dotColor ?>; box-shadow: 0 0 0 1px <?= $dotColor ?>;"></div>
                        <div class="timeline-text" style="color: <?= $textColor ?>;">
                            <?= str_replace('_', ' ', $stage) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="text-align: right; margin-bottom: 20px;">
            <button onclick="downloadPDF()" class="btn" style="background: #333; font-size: 0.85rem;">
                Download PDF
            </button>
        </div>

        <div id="invoice-doc">
            <div class="inv-header">
                <div class="inv-company">
                    <h2>Miziedi</h2>
                    <p>Premium Outdoor Gear<br>support@miziedi.com</p>
                </div>
                <div class="inv-logo">
                    <img src="/assets/images/logo.svg" alt="Miziedi">
                </div>
            </div>

            <div class="inv-title-bar">
                <div class="left">INVOICE NO. <?= htmlspecialchars($order['invoice_number']) ?></div>
                <div class="right">DATE <?= $order['created_at']->toDateTime()->format('d/m/Y') ?></div>
            </div>

            <div class="inv-address-grid">
                <div class="col">
                    <strong>BILL TO</strong>
                    <p><?= htmlspecialchars($order['customer']['name']) ?></p>
                    <p><?= htmlspecialchars($order['customer']['email']) ?></p>
                    <p><?= htmlspecialchars($order['customer']['phone']) ?></p>
                </div>
                <div class="col">
                    <strong>SHIP TO</strong>
                    <p><?= htmlspecialchars($order['customer']['address']) ?></p>
                </div>
                <div class="col instructions">
                    <strong>SUMMARY</strong>
                    <p>Status: <span style="text-transform: uppercase; font-weight: bold;"><?= str_replace('_', ' ', $order['status']) ?></span></p>
                    <p>Total Paid: <strong>₦<?= number_format($total) ?></strong></p>
                </div>
            </div>

            <div class="inv-table-header">
                <div class="col-desc">DESCRIPTION</div>
                <div class="col-qty">QTY</div>
                <div class="col-price">UNIT PRICE</div>
                <div class="col-total">TOTAL</div>
            </div>

            <div class="inv-items">
                <?php foreach ($order['items'] as $item): ?>
                <div class="inv-item-row">
                    <div class="col-desc">
                        <?= htmlspecialchars($item['title']) ?>
                        <div style="font-size: 10px; color: #777;">
                            <?= isset($item['size']) ? 'Size: ' . $item['size'] : 'Standard Delivery' ?>
                        </div>
                    </div>
                    <div class="col-qty"><?= $item['qty'] ?></div>
                    <div class="col-price">₦<?= number_format($item['price']) ?></div>
                    <div class="col-total">₦<?= number_format($item['price'] * $item['qty']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="inv-totals">
                <div class="total-row">
                    <span>SUBTOTAL</span>
                    <span>₦<?= number_format($subtotal) ?></span>
                </div>
                <div class="total-row">
                    <span>SHIPPING & HANDLING</span>
                    <span>₦<?= number_format($delivery) ?></span>
                </div>
                <div class="total-row">
                    <span>TAX (<?= $taxLabel ?>)</span>
                    <span>-</span>
                </div>
                <div class="total-row final">
                    <span>TOTAL DUE</span>
                    <span>₦<?= number_format($total) ?></span>
                </div>
            </div>

            <div class="inv-footer">
                Thank you for shopping with Miziedi!
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    /* --- TRACKING PAGE RESPONSIVE STYLES --- */
    
    /* Form Styles */
    .track-form { display: inline-flex; gap: 10px; margin-top: 15px; }
    .track-input { padding: 10px 20px; border-radius: 30px; border: 1px solid #ccc; width: 250px; }
    .track-btn { padding: 10px 25px; font-size: 0.9rem; }

    /* Status Card */
    .status-card { margin-bottom: 50px; padding: 30px; background: #f9f9f9; border-radius: 20px; }
    .status-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }

    /* Timeline */
    .timeline-wrapper { display: flex; justify-content: space-between; position: relative; margin-top: 30px; }
    .timeline-line { position: absolute; top: 10px; left: 0; width: 100%; height: 3px; background: #e0e0e0; z-index: 0; }
    .timeline-item { position: relative; z-index: 1; text-align: center; flex: 1; }
    .timeline-dot { width: 24px; height: 24px; border-radius: 50%; margin: 0 auto 10px; border: 4px solid #fff; }
    .timeline-text { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2; }

    /* INVOICE STYLING (PDF) */
    #invoice-doc {
        background: white; padding: 40px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        color: #333; font-size: 12px; line-height: 1.4; border: 1px solid #eee;
        width: 800px; max-width: 100%; margin: 0 auto; box-sizing: border-box;
    }
    .inv-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
    .inv-company h2 { font-weight: bold; font-size: 20px; margin: 0 0 5px 0; color: #000; text-transform: uppercase; }
    .inv-company p { color: #666; font-size: 11px; margin: 0; }
    .inv-logo img { height: 40px; }
    .inv-title-bar { background: #555; color: white; padding: 8px 15px; display: flex; justify-content: space-between; font-weight: bold; font-size: 13px; margin-bottom: 30px; text-transform: uppercase; }
    .inv-address-grid { display: flex; margin-bottom: 40px; border-bottom: 1px solid #ddd; padding-bottom: 20px; }
    .inv-address-grid .col { flex: 1; padding-right: 20px; min-width: 0; }
    .inv-address-grid strong { display: block; font-size: 10px; color: #999; margin-bottom: 5px; text-transform: uppercase; }
    .inv-address-grid p { margin: 0 0 2px 0; font-size: 11px; word-wrap: break-word; }
    .inv-table-header { background: #f4f4f4; color: #333; display: flex; padding: 10px 15px; font-weight: bold; font-size: 10px; text-transform: uppercase; border-top: 1px solid #ccc; border-bottom: 1px solid #ccc; }
    .col-desc { width: 50%; } .col-qty { width: 10%; text-align: center; } .col-price { width: 20%; text-align: right; } .col-total { width: 20%; text-align: right; }
    .inv-item-row { display: flex; padding: 15px; border-bottom: 1px solid #eee; font-size: 11px; }
    .inv-totals { margin-top: 20px; margin-left: auto; width: 280px; }
    .total-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 11px; }
    .total-row.final { border-top: 1px solid #333; font-weight: bold; font-size: 14px; margin-top: 10px; padding-top: 10px; }
    .inv-footer { text-align: center; margin-top: 50px; font-size: 10px; color: #999; }
    
    /* --- MOBILE RESPONSIVE OVERRIDES --- */
    @media (max-width: 768px) {
        /* Search */
        .track-form { flex-direction: column; width: 100%; }
        .track-input { width: 100%; box-sizing: border-box; }
        .track-btn { width: 100%; }

        /* Status Header */
        .status-header { flex-direction: column; align-items: flex-start; }
        
        /* Timeline Scaling */
        .timeline-text { font-size: 0.55rem; margin-top: 5px; }
        .timeline-dot { width: 16px; height: 16px; border-width: 3px; margin-bottom: 5px; }
        .timeline-line { top: 7px; } /* Adjust line to match smaller dots */
        
        /* Invoice Mobile Scroll */
        #invoice-doc { overflow-x: auto; }
    }
</style>

<script>
function downloadPDF() {
    const element = document.getElementById('invoice-doc');
    const opt = {
        margin:       0.5,
        filename:     'Invoice_<?= $order['invoice_number'] ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 3, useCORS: true, scrollY: 0 },
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().set(opt).from(element).save();
}
</script>