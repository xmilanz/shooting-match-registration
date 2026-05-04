<div class="accordion accordion-flush px-4" id="Disciplines">
  <!-- Sedící liška -->
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingFox">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFox" aria-expanded="false" aria-controls="collapseFox">
        Sedící liška
      </button>
    </h2>
    <div id="collapseFox" class="accordion-collapse collapse" aria-labelledby="headingFox" data-bs-parent="#Disciplines">
      <div class="accordion-body">
        <ul>
          <li>poloha vsedě</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Srnec -->
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingDeer">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDeer" aria-expanded="false" aria-controls="collapseDeer">
        Srnec
      </button>
    </h2>
    <div id="collapseDeer" class="accordion-collapse collapse" aria-labelledby="headingDeer" data-bs-parent="#Disciplines">
      <div class="accordion-body">
        <ul>
          <li>poloha vstoje s oporou pevné tyče</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Kamzík -->
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingChamois">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseChamois" aria-expanded="false" aria-controls="collapseChamois">
        Kamzík
      </button>
    </h2>
    <div id="collapseChamois" class="accordion-collapse collapse" aria-labelledby="headingChamois" data-bs-parent="#Disciplines">
      <div class="accordion-body">
        <ul>
          <li>poloha vstoje s oporou volně stojící tyče</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Kňour -->
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingBoar">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBoar" aria-expanded="false" aria-controls="collapseBoar">
        Kňour
      </button>
    </h2>
    <div id="collapseBoar" class="accordion-collapse collapse" aria-labelledby="headingBoar" data-bs-parent="#Disciplines">
      <div class="accordion-body">
        <ul>
          <li>poloha vstoje bez opory</li>
        </ul>
      </div>
    </div>
  </div>
</div>
          <div class="px-3 pt-3">
              <h1 class="p-3">Terče</h1>
          </div>
          <div class="portfolio-item row ps-4 pb-4">
              <?php for ($i = 1; $i <= $match_data['Zavod_stages']; $i++) {
				echo "<div class='item col-lg-3 col-md-4 col-6 col-sm'>";
					echo "<a href='./targets/k4m/target$i.png' class='fancylight popup-btn' data-fancybox-group='light'>";
						echo "<img class='img-fluid img-thumbnail' src='./targets/k4m/target$i.png' alt='Target $i'>";
					echo "</a>";
				echo "</div>";
  }; ?>
          </div>
