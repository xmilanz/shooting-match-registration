<?php
include "./header.php";
?>
<div class="row">
    <div class="px-3">
        <h1 class="p-3">Disciplíny</h1>
    </div>

    <?php


if (strpos($table, 'k4m') !== false) {
    include_once __DIR__ . '/include/discipliny_k4m.php';
}
elseif (strpos($table, 'manevry') !== false) {
    include_once __DIR__ . '/include/discipliny_manevry.php';
}
elseif (strpos($table, 'odstrelovacka') !== false) {
include_once __DIR__ . '/include/discipliny_odstrelovacka.php';
}
else {
}

?>


</div>
<script>
    $('.portfolio-menu ul li').click(function() {
        $('.portfolio-menu ul li').removeClass('active');
        $(this).addClass('active');

        var selector = $(this).attr('data-filter');
        $('.portfolio-item').isotope({
            filter: selector
        });
        return false;
    });
    $(document).ready(function() {
        var popup_btn = $('.popup-btn');
        popup_btn.magnificPopup({
            type: 'image',
            gallery: {
                enabled: true
            }
        });
    });
</script>
<?php include "./footer.php"; ?>