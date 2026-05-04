$(document).ready(function() {

    $('#zavodnici').DataTable( {
		responsive: true,
//		colReorder: true,

	language: {
		url: './lang/cs.json'
        },

layout: {
        topStart: {
	       buttons: [
			{
				extend: 'pageLength'
			},
	           {
	                extend: 'spacer',
	                text: '     '
	            },
	            {
	                extend: 'print',
	                exportOptions: {
	                    columns: ':visible'
	                },
				autoPrint: false,
				messageBottom: 'Vytisknuto z registrace závodu SSAŠ střelnice Prachatice',
				customize: function ( win ) {
					$(win.document.body)
						.css( 'font-size', '10pt' )
					$(win.document.body).find( 'table' )
						.addClass( 'compact' )
						.css( 'background-image', 'none' );
						}
	            }
	        ],
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
    } );
} )
