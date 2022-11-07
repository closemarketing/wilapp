// in JS
clickEvents = document.getElementsByClassName('wilapp-item');

var loadFunction = function( e ) {
	// AJAX request.
	let cat_id     = this.getAttribute('data-cat-id');
	let service_id = this.getAttribute('data-service-id');
	let day        = this.getAttribute('data-appointment-weekday');
	let hour       = this.getAttribute('data-appointment-hour');
	let worker     = this.getAttribute('data-appointment-worker');
	let page       = parseInt( this.closest('.wizard-fieldset').getAttribute('data-page') ) + 1;

	fetch( AjaxVarStep.url, {
		method: 'POST',
		credentials: 'same-origin',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
			'Cache-Control': 'no-cache',
		},
		body: 'action=wizard_step&validate_step_nonce=' + AjaxVarStep.nonce + '&cat_id=' + cat_id + '&service_id=' + service_id + '&day=' + day + '&hour=' + hour + '&worker=' + worker + '&page=' + page,
	})
	.then((resp) => resp.json())
	.then( function(data) {
		console.log(page);
		console.log(data);
		if ( data.success && page < 6 ) {
			goToNextPage( e.target, data.data, true );
		} else if ( page == 6 ) {
			goToNextPage( e.target, '', false );
		}
	})
	.catch(err => console.log(err));
}

for (var i = 0; i < clickEvents.length; i++) {
	clickEvents[i].addEventListener('click', loadFunction, false);
}

function goToNextPage( next, options, page_options ) {
	currentFieldSet = next.closest('.wizard-fieldset');
	currentFieldSet.classList.remove('show');

	nextFieldSet = currentFieldSet.nextSibling;
	if ( page_options ) {
		optionsParent = nextFieldSet.querySelector('.options');
	
		options.forEach(element => {
			var li = document.createElement('li');
			let name = document.createTextNode(element.name);
			li.append(name);
			li.className = 'wilapp-item';
			li.setAttribute( 'data-' + element.type, element.id );
			optionsParent.appendChild(li);
		});
		
		for (var i = 0; i < clickEvents.length; i++) {
			clickEvents[i].addEventListener('click', loadFunction, false);
		}
	}

	nextFieldSet.classList.add('show');
}
