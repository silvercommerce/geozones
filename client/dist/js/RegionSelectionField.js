(function(document, window) {
	/**
	 * Better cross browser support for XHR, thanks to this post:
	 * https://stackoverflow.com/questions/3470895/small-ajax-javascript-library
	 * for the idea
	 */
	function create_xhr() {
		var xhr;
		if (window.ActiveXObject) {
			try {
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			} catch(e) {
				alert(e.message);
				xhr = null;
			}
		} else {
			xhr = new XMLHttpRequest();
		}

		return xhr;
	}

	/**
	 * Create a xhr request to retrieve field data and update select field
	 * 
	 * @param {select} region_field the current region field
	 * @param {string} country the 2 character country code to send
	 */
	function xhr_request(country_field) {
		var region_field = document.getElementById(country_field.dataset.regionField);
		var country = country_field.value;
		var link = region_field.dataset.link + "/" + country;
		var xhr = create_xhr();

		// Handle json response
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4) {
				var json = JSON.parse(xhr.responseText);
				var empty_string = region_field.dataset.emptyString;
				var html = "";

				if (empty_string !== undefined) {
					html = '<option value="">' + region_field.dataset.emptyString + '</option>';
				}

				for(var key in json) {
					html += "<option value=" + key  + ">" +json[key] + "</option>"
				}

				region_field.innerHTML = html;
			}
		}
		xhr.open('GET', link, true)
		xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		xhr.send();
	}

	/**
	 * Handle getting the region list and updating the field
	 */
	var select_fields = document.getElementsByTagName("SELECT");

	for (var i = 0; i < select_fields.length; i++) {
		var curr = select_fields[i];

		if (curr.dataset.regionField) {
			var country_field = document.getElementById(curr.dataset.countryField);
			country_field.dataset.regionField = curr.id; 
			country_field.onchange = function() {
                xhr_request(this);
            }
		}
	}
}(document, window));