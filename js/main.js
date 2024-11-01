function wpplf23_validateForm() {
	//TODO: CHECK IF INPUTS ARE EMPTY HERE
	var submitedTelNo = document.getElementById('phoneNo').value;
	var telError = wpplf23_validateTelNumber (submitedTelNo);
	if (telError) {
		return false;
	}
}

function wpplf23_validateTelNumber (TelNo) {
	// If invalid number, report back error
	if (!wpplf23_checkUKTelephone (TelNo)) {
		document.getElementById('phoneNo').className = "FieldError input-text"; //Classs to highlight error
		alert (telNumberErrors[telNumberErrorNo]);
		//return error
		return true;
	}
	// Otherwise redisplay telephone number on form in corrected format
	else {
		document.getElementById('phoneNo').value =  wpplf23_checkUKTelephone (TelNo);
		document.getElementById('phoneNo').className = "FieldOk input-text"; //Resets field back to default
		//alert ("Telephone number appears to be valid");
	}
}

function wpplf23_disableInputs (cb) {
	document.getElementById('smtp_auth_enable').disabled = !this.checked;
}

