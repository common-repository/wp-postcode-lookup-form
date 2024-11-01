function wpplf23_hide_options() {
	var d = document.getElementById('options-div');
	var b = document.getElementById("hide-options-button");
	
	if (wpplf23_isHidden(d)) {
		//Show div
		d.style.display = "block";
		//Set button text
		b.innerHTML = 'Hide';
	} else {
		//Hide div
		d.style.display = "none";
		//Set button text
		b.innerHTML = 'Show';
	}
}

function wpplf23_hide_log_options() {
	var d = document.getElementById('log-options-div');
	var b = document.getElementById("hide-log-options-button");
	
	if (wpplf23_isHidden(d)) {
		//Show div
		d.style.display = "block";
		//Set button text
		b.innerHTML = 'Hide';
		//force window resize event to trigger so that table headers are the correct size when unhiding the div
		window.dispatchEvent(new Event('resize'));
	} else {
		//Hide div
		d.style.display = "none";
		//Set button text
		b.innerHTML = 'Show';
	}
}

function wpplf23_hide_submissions() {
	
	var d = document.getElementById('submissions-table-div');
	var b = document.getElementById("hide-submissions-button");
	
	if (wpplf23_isHidden(d)) {
		//Show div
		d.style.display = "block";
		//Set button text
		b.innerHTML = 'Hide';
	} else {
		//Hide div
		d.style.display = "none";
		//Set button text
		b.innerHTML = 'Show';
	}
}

//Where el is the DOM element you'd like to test for visibility
function wpplf23_isHidden(el) {
    return (el.offsetParent === null)
}

// Change the selector if needed
var $scroll_table = jQuery('table.scroll');
//$table = document.getElementById('log-options-table')
var $bodyCells = $scroll_table.find('tbody tr:first').children();
var colWidth;

//Window resize event
jQuery(window).on('resize', function(){
	//SCROLLABLE TABLE HEADER RESIZE
	// Get the tbody columns width array
    colWidth = $bodyCells.map(function() {
        return jQuery(this).width();
    }).get();
    
    // Set the width of thead columns
    $scroll_table.find('thead tr').children().each(function(i, v) {
        jQuery(v).width(colWidth[i]);
    });
	//END SCROLLABLE TABLE HEADER RESIZE
}).resize();

function wpplf23_on_window_load() {
	wpplf23_highlight_submissions_row();
	wpplf23_hide_debug_log();
}

window.onload = wpplf23_on_window_load;

function wpplf23_highlight_submissions_row() {
    var table = document.getElementById('submissions-table');
    var cells = table.getElementsByTagName('td');

    for (var i = 0; i < cells.length; i++) {
        // Take each cell
        var cell = cells[i];
        // do something on onclick event for cell
        cell.onclick = function () {
            // Get the row id where the cell exists
            var rowId = this.parentNode.rowIndex;

            var rowsNotSelected = table.getElementsByTagName('tr');
            for (var row = 0; row < rowsNotSelected.length; row++) {
                rowsNotSelected[row].style.backgroundColor = "";
                rowsNotSelected[row].classList.remove('selected');
            }
            var rowSelected = table.getElementsByTagName('tr')[rowId];
            rowSelected.style.backgroundColor = "yellow";
            rowSelected.className += " selected";

            //msg = 'The ID of the company is: ' + rowSelected.cells[0].innerHTML;
            //msg += '\nThe cell value is: ' + this.innerHTML;
            //alert(msg);
        }
    }

} //end of function

function wpplf23_hide_debug_log() {
	if (scriptParams.debug_log_enable == 0) {
		wpplf23_hide_log_options();
	}
}

function wpplf23_validateForm() {
	//TODO: CHECK IF INPUTS ARE EMPTY HERE
	//var submitedTelNo = document.getElementById('phoneNo').value;
	//var telError = wpplf23_validateTelNumber (submitedTelNo);
	//if (telError) {
	//	return false;
	//}
}
