(function (w, $) {
    w.SSASAdmin = w.SSASAdmin || {};

    function getVisibleColumns(paymentBefore, registraceSmeny) {
        var columnConfig = {
            "1_0": [2, 5, 6, 10, 11, 12, 13, 14, 15, 16, 17], // předem
            "0_0": [2, 5, 6, 10, 11, 12, 13, 14, 15, 20],        // na místě
            "1_1": [1, 2, 5, 6, 10, 11, 12, 13, 14, 15, 16, 17], // předem + směny
            "0_1": [1, 2, 5, 6, 10, 11, 12, 13, 14, 15, 20]       // na místě + směny
        };

        return columnConfig[(paymentBefore ? 1 : 0) + "_" + (registraceSmeny ? 1 : 0)];
    }

    w.SSASAdmin.destroyZavodniciDataTableIfExists = function () {
        try {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#zavodnici')) {
                $('#zavodnici').DataTable().destroy();
            }
        } catch (e) {
            // noop
        }
    };

    w.SSASAdmin.initZavodniciDataTable = function (opts) {
        if (!$.fn.DataTable) return;

        opts = opts || {};
        var pb = (typeof opts.paymentBefore !== 'undefined') ? !!opts.paymentBefore : !!w.paymentBefore;
        var rs = (typeof opts.registraceSmeny !== 'undefined') ? !!opts.registraceSmeny : !!w.registraceSmeny;

        var visibleColumns = getVisibleColumns(pb, rs);

        // Fail-safe: pokud konfigurace viditelných sloupců není dostupná,
        // nesmíme schovat všechny sloupce (jinak bude tabulka "prázdná").
        var columnDefs = [
            { targets: -1, render: $.fn.dataTable.render.ellipsis(25) }
        ];
        if (Array.isArray(visibleColumns)) {
            // Důležité: nejdřív schovat vše, pak povolit jen vybrané sloupce.
            columnDefs.unshift({ targets: visibleColumns, visible: true });
            columnDefs.unshift({ targets: '_all', visible: false });
        } else {
            // eslint-disable-next-line no-console
            console.warn('SSASAdmin: visibleColumns config missing for key', (pb ? 1 : 0) + "_" + (rs ? 1 : 0));
        }

        var dt = $('#zavodnici').DataTable({
            responsive: true,
            colReorder: true,
            deferRender: true,
            stateSave: false,
            columnDefs: columnDefs,
            language: {
                url: '../lang/cs.json'
            },
            lengthMenu: [
                [-1, 10, 25, 50],
                ['Všechny', '10 řádků', '25 řádků', '50 řádků']
            ],
            layout: {
                topStart: {
                    searchBuilder: {
                        preDefined: {
                            criteria: [
                                {
                                    condition: '',
                                    data: '',
                                    value: ['']
                                }
                            ],
                        }
                    },
                    buttons: [
                        { extend: 'pageLength' },
                        {
                            extend: 'colvis',
                            collectionLayout: 'fixed columns',
                            collectionTitle: 'Viditelné sloupce'
                        },
                        { extend: 'spacer', text: '     ' },
                        { extend: 'excelHtml5' },
                        { extend: 'csvHtml5' },
                        {
                            extend: 'print',
                            exportOptions: { columns: ':visible' },
                            autoPrint: false,
                            customize: function (win) {
                                $(win.document.body).css('font-size', '10px');
                                $(win.document.body).find('table').addClass('print');
                                $(win.document.body).find('td').addClass('print');
                                $(win.document.body).find('th').addClass('print');
                            }
                        },
                        { extend: 'spacer', text: '     ' }
                    ]
                },
                topEnd: {
                    search: {
                        placeholder: 'Hledat'
                    }
                },
                bottomEnd: {
                    paging: {
                        numbers: 3
                    }
                }
            }
        });

        // Vynucení viditelnosti sloupců přes API (spolehlivé i po refreshi / reinicializaci)
        if (Array.isArray(visibleColumns)) {
            try {
                dt.columns().visible(false, false);
                dt.columns(visibleColumns).visible(true, false);
                dt.columns.adjust().draw(false);
            } catch (e) {
                // noop
            }
        }
    };

    $(document).ready(function () {
        // první načtení stránky
        w.SSASAdmin.initZavodniciDataTable();
    });
})(window, jQuery);
