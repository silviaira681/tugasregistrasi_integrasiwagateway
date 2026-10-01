<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    isset($_SESSION['flash_message'])
) {

    $message =
        $_SESSION['flash_message'];

    $type =
        $_SESSION['flash_type'] ?? 'success';

    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);

    $background =
        $type === 'success'
        ? '#dcfce7'
        : '#fee2e2';

    $color =
        $type === 'success'
        ? '#166534'
        : '#991b1b';
?>

<div
    id="notification"
    style="
        position:fixed;
        top:20px;
        right:20px;
        z-index:9999;
        background:<?= $background ?>;
        color:<?= $color ?>;
        padding:15px 20px;
        border-radius:12px;
        box-shadow:0 10px 30px rgba(0,0,0,.15);
        font-family:Arial,sans-serif;
        max-width:350px;
    "
>
    <?= htmlspecialchars($message) ?>
</div>

<script>

setTimeout(function () {

    const notif =
        document.getElementById('notification');

    if (notif) {
        notif.style.display = 'none';
    }

}, 4000);

</script>

<?php
}
?>