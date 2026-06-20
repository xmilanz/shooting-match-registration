<div class="modal fade" id="truncateModal" tabindex="-1" aria-labelledby="truncateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php
            include './components/modal-warning-form.php';
            WarningModalForm(
                "danger",
                'Vyprázdnění tabulky závodníků',
                "index.php",
                [
                    'csrf_token' => $_SESSION['csrf_token'] ?? ''
                ],
                'Opravdu chcete nevratně vyprázdnit tabulku závodníků? Tato akce odstraní všechny záznamy.',
                'Doporučeno pouze v testovacím prostředí. Akce je nevratná.',
                "./save.php",
                "shooters_table_truncate",
                "Vyprázdnit tabulku",
                "Zrušit"
            );
            ?>
        </div>
    </div>
</div>
