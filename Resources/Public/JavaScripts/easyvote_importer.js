$(function () {
	$('.useDatatables').dataTable({
		"bStateSave": true,
		"iDisplayLength": 25,
		"aaSorting": [
			[ 1, "asc" ]
		],
		"oLanguage": {
			"sProcessing": "Bitte warten...",
			"sLengthMenu": "_MENU_ Einträge anzeigen",
			"sZeroRecords": "Keine Einträge vorhanden.",
			"sInfo": "_START_ bis _END_ von _TOTAL_ Einträgen",
			"sInfoEmpty": "0 bis 0 von 0 Einträgen",
			"sInfoFiltered": "(gefiltert von _MAX_  Einträgen)",
			"sInfoPostFix": "",
			"sSearch": "Suche",
			"sUrl": "",
			"oPaginate": {
				"sFirst": "Erster",
				"sPrevious": "Zurück",
				"sNext": "Nächster",
				"sLast": "Letzter"
			}
		}
	});
});


$(function() {
	$("#source, .target").sortable({
		connectWith: ".connected",
		cancel: ".static",
		stop: function(event, ui) {
			reflectChange(event);
			$('.sortable-placeholder').hide();
		},
		start: function(evento, ui) {
			$('.sortable-placeholder').fadeIn();
		}
	}).disableSelection();
	$('#source, .target').bind('sortstart', function(event, ui) {
		$('.sortable-placeholder').text('Hier ablegen.');
	});

});

function reflectChange(event) {
	$('.target').each(function() {
		// example: target-name
		targetId = $(this).attr('id');
		// example: name
		targetType = targetId.split('-')[1];

		// the container for the preview
		$('div#preview-' + targetType).empty();
		// empty the input field containing the column IDs
		$('input#columns-' + targetType).val('');
		$('li.value', this).each(function() {
			// write example data to the corresponding container
			exampleData = $(this).attr('data-example');
			column = $(this).attr('data-column');
			$('div#preview-' + targetType).append(exampleData + '&nbsp;');
			$('input#columns-' + targetType).val($('input#columns-' + targetType).val() + column + ',');
		});

	});
};

function validateForm() {
	var errorMessages = '';
	$('input.validate', 'form#columnAssignment').each(function() {
		if (!$(this).val()) {
			errorMessages += errorTextBegin + ' "'+ $(this).attr('data-label') + '" ' + errorTextEnd + '<br />';
		}
	});
	if (errorMessages) {
		errorMessages += '<br /><button class="button-cancel btn btn-primary">OK</button>';
		Easyvote.displayModal(errorMessages);
		return false;
	}
};
