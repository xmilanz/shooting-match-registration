<?php
include "./header.php";
?>
<div class="row">
    <div class="px-3">
        <h1 class="p-3">Disciplíny</h1>
    </div>
    <div class="accordion accordion-flush px-4" id="disciplines">

        <!-- Disciplína 1 začátek - zkopírovat div pro další disciplíny v accordionu -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingA">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseA" aria-expanded="false" aria-controls="collapseA">
                    A. Puška
                </button>
            </h2>
            <div id="collapseA" class="accordion-collapse collapse" aria-labelledby="headingA" data-bs-parent="#disciplines">
                <div class="accordion-body">
                    <strong>Puška</strong>
                    <ul>
                        <li>opakovací nebo samonabíjecí se zásobníkem ráže .22 LR</li>
                    </ul>
                    <strong>Přední podpěra</strong>
                    <ul>
                        <li>pevné podepření zbraně pouze v předpažbí prostřednictvím bipodu (nožičky) nebo střeleckého vaku</li>
                    </ul>
                    <strong>Zadní podpěra</strong>
                    <ul>
                        <li>pažba může být podložena pouze rukou nebo zapřena v rameni</li>
                    </ul>
                    <p><strong>Střelecké kabáty, řemeny a háky – NE</strong></p>
                </div>
            </div>
        </div>
        <!-- Disciplína 1 konec - zkopírovat div pro další disciplíny v accordionu -->

    </div>
    <div class="px-3 pt-3">
        <h1 class="p-3">Terče</h1>
    </div>
	<!-- načítá obrázky z adresáře targets - počet se nastaví v administaci - Počet situací (Zavod_stages)  -->
    <div class="portfolio-item row ps-4 pb-4">
        <?php for ($i = 1; $i <= $match_data['Zavod_stages']; $i++) {
            echo "<div class='item col-lg-3 col-md-4 col-6 col-sm'>";
            echo "<a href='./targets/target$i.png' class='fancylight popup-btn' data-fancybox-group='light'>";
            echo "<img class='img-fluid img-thumbnail' src='./targets/target$i.png' alt='Target $i'>";
            echo "</a>";
            echo "</div>";
        }; ?>
    </div>
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