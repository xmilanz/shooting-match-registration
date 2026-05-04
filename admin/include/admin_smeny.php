<?php

if (!isset($ajax_path))
    $ajax_path = './include/ajax_move_shooter.php';

$pocetSmen = isset($match_data['Pocet_smen']) && (int) $match_data['Pocet_smen'] > 0
    ? (int) $match_data['Pocet_smen']
    : 10;

$stmt = $conn->prepare("
    SELECT Cislo, Jmeno, Prijmeni, Stav, Disciplina
    FROM $table
    WHERE UPPER(Disciplina) != 'VYRAZENO'
    ORDER BY Stav
");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$smenMatrix = [];
while ($row = $result->fetch_assoc()) {
    $stav = (int) $row['Stav'];
    $shift = intdiv($stav, 100);
    $pos = $stav % 100;
    if ($shift < 1 || $pos < 1 || $pos > 10)
        continue;
    $smenMatrix[$shift][$pos] = [
        'cislo' => $row['Cislo'],
        'stav' => $stav,
        'name' => trim($row['Jmeno'] . ' ' . $row['Prijmeni']),
        'disc' => $row['Disciplina'],
    ];
}

$allShiftKeys = range(1, $pocetSmen);
?>

<!-- MODAL -->
<div class="modal fade" id="admin_smeny" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false"
    aria-hidden="true">
    <div class="modal-dialog modal-extraLarge">
        <div class="modal-content">

            <div class="modal-header bg-primary text-center">
                <h4 class="modal-title text-white w-100 fw-bold py-2">Zařazení závodníků do směn</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <div id="ddStatus" class="alert d-none mb-3" role="alert"></div>
                <div class="alert alert-info small mt-0" role="alert">
                    Závodníka přesunete tažením myši na volný stav. Přesun se uloží okamžitě po uvolnění tlačítka myši. Změnu lze kdykoliv vrátit zpět.
                </div>

                <div class="table-responsive-shifts">
                    <table class="table table-bordered shift-table text-center align-middle">
                        <thead>
                            <tr>
                                <th style="background-color: #ffffff !important"></th>
                                <?php for ($c = 1; $c <= 10; $c++): ?>
                                    <th class="text-center align-middle text-white" style="min-width:110px">
                                        Stav&nbsp;<?= $c ?></th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allShiftKeys as $shift): ?>
                                <tr>
                                    <th class="text-nowrap column1st">Směna <?= $shift ?></th>
                                    <?php for ($c = 1; $c <= 10; $c++):
                                        $stavCislo = $shift * 100 + $c;
                                        $occupied = isset($smenMatrix[$shift][$c]);
                                        $comp = $occupied ? $smenMatrix[$shift][$c] : null;
                                        ?>
                                        <td class="droptarget shift-cell <?= $occupied ? 'occupied' : '' ?>"
                                            data-stav="<?= $stavCislo ?>" style="height:55px;">
                                            <?php if ($occupied): ?>
                                                <div class="draggable-shooter d-flex flex-column align-items-center
                                                            justify-content-center border rounded p-1 bg-white shadow-sm"
                                                    draggable="true" data-cislo="<?= $comp['cislo'] ?>"
                                                    data-stav="<?= $comp['stav'] ?>"
                                                    style="cursor:grab; font-size:.78rem; min-height:50px;">
                                                    <strong><?= htmlspecialchars($comp['name']) ?></strong>
                                                    <span class="text-muted"><?= htmlspecialchars($comp['disc']) ?></span>
                                                    <span class="d-none badge bg-secondary mt-1"><?= $comp['stav'] ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small"></span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Zavřít</button>
            </div>

        </div>
    </div>
</div>

<script>
    (function () {
        var ajaxPath = <?= json_encode($ajax_path) ?>;
        var draggedEl = null;
        var dragData = {};

        function showStatus(msg, type) {
            var el = document.getElementById('ddStatus');
            el.className = 'alert alert-' + type + ' mb-2';
            el.textContent = msg;
            el.classList.remove('d-none');
            setTimeout(function () { el.classList.add('d-none'); }, 3000);
        }

        document.addEventListener('dragstart', function (e) {
            var shooter = e.target.closest('.draggable-shooter');
            if (!shooter) return;
            draggedEl = shooter;
            dragData = { cislo: shooter.dataset.cislo, stavOld: parseInt(shooter.dataset.stav) };
            shooter.style.opacity = '0.4';
            e.dataTransfer.effectAllowed = 'move';
        });

        document.addEventListener('dragend', function (e) {
            var shooter = e.target.closest('.draggable-shooter');
            if (shooter) shooter.style.opacity = '';
            draggedEl = null;
        });

        document.addEventListener('dragover', function (e) {
            if (e.target.closest('.droptarget')) e.preventDefault();
        });

        document.addEventListener('dragenter', function (e) {
            var cell = e.target.closest('.droptarget');
            if (cell) cell.classList.add('table-info');
        });

        document.addEventListener('dragleave', function (e) {
            var cell = e.target.closest('.droptarget');
            if (cell) cell.classList.remove('table-info');
        });

        document.addEventListener('drop', function (e) {
            e.preventDefault();
            var cell = e.target.closest('.droptarget');
            if (!cell || !draggedEl) return;
            cell.classList.remove('table-info');

            var stavNew = parseInt(cell.dataset.stav);
            if (stavNew === dragData.stavOld) return;

            if (cell.querySelector('.draggable-shooter')) {
                showStatus('Cílový stav je obsazen.', 'warning');
                return;
            }

            var srcCell = draggedEl.closest('.droptarget');

            // Okamžitá aktualizace DOM
            draggedEl.dataset.stav = stavNew;
            draggedEl.querySelector('.badge').textContent = stavNew;
            cell.innerHTML = '';
            cell.appendChild(draggedEl);
            cell.classList.add('bg-light');
            srcCell.innerHTML = '<span class="text-muted small">\u2014\u00a0voln\u00e9\u00a0\u2014</span>';
            srcCell.classList.remove('bg-light');

            draggedEl.style.opacity = '';

            // AJAX uložení
            fetch(ajaxPath, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'cislo=' + encodeURIComponent(dragData.cislo) + '&stav=' + encodeURIComponent(stavNew)
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        showStatus('Závodník byl přesunut do stavu ' + stavNew + '.', 'success');
                    } else {
                        showStatus('Chyba: ' + (data.error || 'neznámá chyba'), 'danger');
                        location.reload();
                    }
                })
                .catch(function () {
                    showStatus('Chyba spojení se serverem.', 'danger');
                    location.reload();
                });
        });
    })();
</script>