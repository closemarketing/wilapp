// Company Search.
var companySearch = document.querySelector( '.js-company-search' );

// Event: change selected option on DNI.
var ccooRegistreTipus = document.querySelector('.ccoo-registre #tipus');

if ( ccooRegistreTipus ) {
  
	ccooRegistreTipus.addEventListener('change', function() {

		const tipusField = this.value;
		const dniField = document.querySelector('.ccoo-registre #dni_nie');
		const passField = document.querySelector('.ccoo-registre #passport');
	
		if ( tipusField === 'tipus-dni' ) {
			dniField.parentElement.parentElement.classList.remove('hidden');
			passField.parentElement.parentElement.classList.add('hidden');
		} else {
			dniField.parentElement.parentElement.classList.add('hidden');
			passField.parentElement.parentElement.classList.remove('hidden');
		}
	});

}

var ccooRegistreSituacio = document.querySelector('.ccoo-registre #situacio');

if ( ccooRegistreSituacio && ! companySearch ) {

	// Event: change selected option on Employment.
	ccooRegistreSituacio.addEventListener('change', function() {

		const situationField = this.value;
		const collectiuField = document.querySelector('.ccoo-registre #collectiu');
		const categoriaField = document.querySelector('.ccoo-registre #categoria');
		const areafuncField  = document.querySelector('.ccoo-registre #area_func');

		if ( situationField === 'E' ) {

			this.parentElement.parentElement.classList.remove( 'form-half' );
			this.parentElement.parentElement.classList.add( 'form-third' );
			
			collectiuField.parentElement.parentElement.classList.remove('form-half');
			collectiuField.parentElement.parentElement.classList.add('form-third' );
			collectiuField.parentElement.parentElement.classList.remove('hidden');
			
			categoriaField.parentElement.parentElement.classList.remove('form-half');
			categoriaField.parentElement.parentElement.classList.add('form-third');
			categoriaField.parentElement.parentElement.classList.remove('hidden');

			areafuncField.parentElement.parentElement.classList.add('form-half');
			areafuncField.parentElement.parentElement.classList.remove('form-third');
			areafuncField.parentElement.parentElement.classList.remove('hidden');
		}
		
		if ( situationField === 'D' || situationField === 'N' ) {
			this.parentElement.parentElement.classList.remove( 'form-third' );
			this.parentElement.parentElement.classList.add( 'form-half' );

			collectiuField.parentElement.parentElement.classList.add('hidden');
			categoriaField.parentElement.parentElement.classList.add('hidden');
			areafuncField.parentElement.parentElement.classList.add('hidden');
		}

		if ( situationField === 'A' ) {

			this.parentElement.parentElement.classList.remove( 'form-third' );
			this.parentElement.parentElement.classList.add( 'form-half' );

			categoriaField.parentElement.parentElement.classList.remove( 'form-third' );
			categoriaField.parentElement.parentElement.classList.add( 'form-half' );
		
			collectiuField.parentElement.parentElement.classList.add('hidden');
			
			categoriaField.parentElement.parentElement.classList.remove('hidden');
			
			areafuncField.parentElement.parentElement.classList.remove( 'form-third' );
			areafuncField.parentElement.parentElement.classList.add( 'form-half' );
			areafuncField.parentElement.parentElement.classList.remove('hidden');
		}

		if ( situationField === '' ) {
			this.parentElement.parentElement.classList.remove( 'form-third' );
			this.parentElement.parentElement.classList.add( 'form-half' );

			collectiuField.parentElement.parentElement.classList.add('hidden');
			categoriaField.parentElement.parentElement.classList.add('hidden');
			areafuncField.parentElement.parentElement.classList.add('hidden');
		}

	});
}

function goToNextPage( element ) {

	let parentFieldset    = jQuery( element ).parents('.wizard-fieldset');
	let currentActiveStep = jQuery( element ).parents('.form-wizard').find('.form-wizard-steps .active');
	let next              = jQuery( element );
	let nextWizardStep    = true;
	let nextPage          = jQuery( element ).data( 'page' ) + 1;
	let formContainer     = jQuery( '.ccoo-registre.wizard-section' );

	// If new page is page 2, then reduce the form width, else, normal width.
	if ( 2 == nextPage ) {
		formContainer.addClass( 'code-view' );

		// Hide login form if exist on page 2.
		jQuery( '.login-form' ).remove();
		jQuery( '.or' ).remove();
	} else if ( 2 != nextPage && formContainer.hasClass( 'code-view' ) ) {
		formContainer.removeClass( 'code-view' );
	}

	if ( nextWizardStep ) {
		next.parents('.wizard-fieldset').removeClass("show","400");
		currentActiveStep.removeClass('active').addClass('activated').next().addClass('active',"400");
		next.parents('.wizard-fieldset').next('.wizard-fieldset').addClass("show","400");

		jQuery( document ).find('.wizard-fieldset').each( () => {
		
			if( jQuery( element ).hasClass( 'show' ) ) {
				let formAtrr = jQuery( element ).attr('data-tab-content');
				
				jQuery(document).find('.form-wizard-steps .form-wizard-step-item').each(function(){
					
					if(jQuery( element ).attr('data-attr') == formAtrr){

						jQuery( element ).addClass('active');
						let innerWidth = jQuery( element ).innerWidth();
						let position = jQuery( element ).position();
						jQuery(document).find('.form-wizard-step-move').css({"left": position.left, "width": innerWidth});

					} else {
						jQuery( element ).removeClass('active');
					}
				});

			}

		});
	}
}

// Google Address
document.addEventListener( 'DOMContentLoaded', function() {

	let autocomplete;

	// Init autocomplete.
	function initAutocomplete() {

		let address_field = document.querySelector( '.ccoo-registre input#address' );
		const options     = {
			fields: [ 'address_components' ],
			componentRestrictions: { country: 'es' }
		} 
		
		autocomplete = new google.maps.places.Autocomplete( address_field, options );
		autocomplete.addListener( 'place_changed', onPlaceChanged );
	}

	// On change place fire this callback.
	function onPlaceChanged() {

		const place = autocomplete.getPlace();

		let addressValue         = "";
		let postCodeValue        = "";
		let addressValueHidden   = "";
		let cityValueHidden      = "";
		
		const addressField       = document.querySelector( '.ccoo-registre #address' );
		
		const postcodeField      = document.querySelector( '.ccoo-registre #zipcode' );
		const addressFieldHidden = document.querySelector( '.ccoo-registre #address_1' );
		const cityFieldHidden    = document.querySelector( '.ccoo-registre #city' );
		const stateProvince      = document.querySelector( '.ccoo-registre #state' );

		for ( const component of place.address_components ) {

			const componentType = component.types[0];
			switch (componentType) {

				case "postal_code": {
					postCodeValue = `${component.long_name}${postCodeValue}`;
					break;
				}

				case "route": {
					addressValueHidden = `${component.long_name}${addressValueHidden}`;
					break;
				}

				case "street_number": {
					addressValueHidden += ` , ${component.long_name}${addressValueHidden}`;
					break;
				}

				case "administrative_area_level_2": {
					cityValueHidden = `${component.long_name}${cityValueHidden}`;
					break;
				}

			}
		}

		// Check if postcode value is empty.
		if ( '' === postCodeValue ) {
			// Cant get Zip code, set to empty if have value.
			postcodeField.value  = '';
			alert( 'La direcció ha de ser més precisa per obtenir un codi postal' );
		} else {
			postcodeField.value       = postCodeValue;
			addressFieldHidden.value  = addressValueHidden;
			cityFieldHidden.value     = cityValueHidden;
			stateProvince.value       = cityValueHidden;

			let zipcodeInput  = document.querySelector( '#zipcode' );
			zipcodeInput.disabled = true;
		}

	}

	// Enable/disable zipcode field.
	let zipcodeInput  = document.querySelector( '#zipcode' );
	let addressInput  = document.querySelector( '#address' );	
	
	addressInput.onkeypress = () => {
		zipcodeInput.disabled = false;
	}

	// If Source exist, set FPP as default option.
	if ( document.body.contains( document.querySelector( 'select#procedencia' ) ) ) {
		document.querySelector( 'select#procedencia' ).value = 'a';;
	}

	// Init.
	initAutocomplete();
});

if ( companySearch ) {

	let companyProvince      = document.querySelector( '.js-company-province' );
	let companyType          = document.querySelector( '.js-company-search-type' );
	let companySearch        = document.querySelector( '.js-company-search-value' );
	let companySearchAction  = document.querySelector( '.js-company-search-action' );
	let companySearchResults = document.querySelector( '.js-company-search-results' );

	const situacioFiled      = document.querySelector( '#situacio' );
	
	// Event: change selected option on Employment.
	companySearchAction.addEventListener( 'click', ( e ) => {
		e.preventDefault();

		if ( ! companyProvince.value ) {
			alert( 'És obligatori introduir una província per cercar empreses.' )
			return false;
		}
		
		// Remove warning before add it.

		// Check lengh.
		if ( 3 > companySearch.value.length ) {
			alert( 'És obligatori introduir un valor per cercar per nom o nif.' );
			return false;

		}
	
		jQuery.ajax({
			type: 'POST',
			url: AjaxVarSubmit.url,
			data: {
				action: 'registre_covenant_search',
				keyword: companySearch.value,
				state: companyProvince.value,
				type: companyType.value,
				nonce: AjaxVarSubmit.nonce
			},
			beforeSend: function() { 
				companySearchAction.disabled = true;
				companySearchAction.parentNode.innerHTML += '<p style="font-size:13px;color:red;" class="js-info-message>Carregant...</p>'
			},
			complete: function() { 
				companySearchAction.disabled = false;
				document.querySelector( '.js-info-message' ).remove;
			},
			success: function( response ) {
				companySearchResults.innerHTML = response.data;
			},
			error: function(xhr, textStatus, error){
				console.log(xhr.statusText);
				console.log(textStatus);
				console.log(error);
			}
		});

	});

	
	situacioFiled.addEventListener( 'change', function() {
		let value = this.value.toUpperCase();
		
		if ( 'E' == value || 'A' == value ) {
			document.querySelector( '.js-company-search' ).style.display = 'block';
		} else {
			document.querySelector( '.js-company-search' ).style.display = 'none';
		}

	});

}
