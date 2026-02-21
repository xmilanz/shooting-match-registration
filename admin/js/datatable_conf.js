$(document).ready(function () {

    // Dynamické nastavení viditelných sloupců
    var visibleColumns = paymentBefore
        ? [1, 2, 4, 5, 9, 11, 13, 14, 15, 25]  // placení předem
        : [1, 2, 4, 5, 9, 11, 12, 13, 14, 19];       // placení na místě

    var columnDefs = [
        { targets: visibleColumns, visible: true },
        { targets: '_all', visible: false },
        { targets: -1, render: $.fn.dataTable.render.ellipsis(25) }
    ];

    // Inicializace DataTables
    $('#zavodnici').DataTable({
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
});
