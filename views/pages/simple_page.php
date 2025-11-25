<div class="container" style="margin-top: 60px; margin-bottom: 100px; max-width: 800px;">
    <h1 style="font-family: var(--font-primary); font-size: 2.5rem; margin-bottom: 30px; text-transform: uppercase;">
        <?= htmlspecialchars($heading) ?>
    </h1>
    
    <div style="font-size: 1.1rem; line-height: 1.8; color: #444;">
        <p><?= nl2br(htmlspecialchars($content)) ?></p>
    </div>

    <div style="margin-top: 50px;">
        <a href="/" class="btn">Back to Shop</a>
    </div>
</div>