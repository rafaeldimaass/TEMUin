<?php
$flashStyle = [
    'success' => 'background:#d1fae5;color:#065f46;border:1px solid #10b981;',
    'error'   => 'background:#fee2e2;color:#991b1b;border:1px solid #ef4444;',
];
foreach ($flashStyle as $key => $style):
    if (empty($_SESSION[$key])) continue; ?>
    <div class="alert" style="<?= $style ?>padding:14px 20px;border-radius:8px;margin-bottom:20px;font-weight:500;display:flex;justify-content:space-between;align-items:center;">
        <span><?= htmlspecialchars($_SESSION[$key]) ?></span>
        <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;font-size:18px;cursor:pointer;color:inherit;">&times;</button>
    </div>
    <?php unset($_SESSION[$key]);
endforeach; ?>
