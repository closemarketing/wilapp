// Validation messages as global var.
var validation = AjaxVarStep.validationMsg;

// Validate first page.
jQuery( '.js-validate-page-1' ).on( 'click', ( e ) => {

	e.preventDefault();

	if ( jQuery( '.js-user-profile-loader' ).prop( 'disabled' ) ) {
		// Process is running, prevent send data again.
		return false;
	}

	// Show loader next to button and disable button.
	toggleLoaderIcon( e.target );

	// Fields.
	const email           = jQuery( '.ccoo-registre #email' );
	const password        = jQuery( '.ccoo-registre #pwd' );
	const repeatPassword  = jQuery( '.ccoo-registre #cpwd' );
	const dni             = jQuery( '.ccoo-registre #dni_nie' );
	const passport        = jQuery( '.ccoo-registre #passport' );
	const hash            = jQuery( '.ccoo-registre #hash' );
	const idvalidated     = jQuery( '.ccoo-registre #idvalidated' );
	const mobile          = jQuery( '.ccoo-registre #mobile' );
	const page            = 1;
	const nonce           = jQuery( '.ccoo-registre #validate_step_1_nonce' );
	const formType        = jQuery( '.ccoo-registre' ).data( 'type' );
	const isRecupera			= jQuery( '.ccoo-registre' ).data( 'recupera' )

	// Reset validation error messages.
	jQuery( '.js-validation-error-message' ).remove();
	jQuery( '.validation-error-input' ).removeClass( 'validation-error-input' );

	let validationPassed = true;

	// Validate email.
	if ( ! validateEmail( email ) ) {
		validationPassed = false;
	}

	// Validate password.
	if ( ! validatePassword( password, repeatPassword ) ) {
		validationPassed = false;
	}

	// Validate DNI / NIE.
	if ( ! validateDniNie( dni ) ) {
		validationPassed = false;
	}

	// Validate Phone Number.
	if ( ! validatePhoneNumber( mobile ) ) {
		validationPassed = false;
	}

	// Check if validation is passed or not.
	if ( ! validationPassed ) {
		// Validation fails, return false and remove loader. Dont send any data to API yet.
		toggleLoaderIcon( e.target );
		return false;
	}

	// Validation passed, send data to API.
	jQuery.ajax({
		type: 'POST',
		url: AjaxVarStep.url,
		data: {
			action: 'validate_step',
			dni_nie: dni.val(),
			mobile: mobile.val(),
			email: email.val(),
			password: password.val(),
			passport: passport.val(),
			hash: hash.val(),
			idvalidated: idvalidated.val(),
			page: page,
			form_type: formType,
			is_recupera: isRecupera,
			validate_step_1_nonce: nonce.val(),
		},

		success: function( response ) {
			toggleLoaderIcon( e.target );

			// Check for errors.
			if ( false === response.success ) {
				inlineErrorMessage( jQuery( '.js-validate-page-1' ), response.data );
				return false;
			}

			// This fields are with display none on second page, we must use attr() method to set a value.
			idvalidated.attr( 'value', response.data.idvalidated );
			hash.attr( 'value', response.data.hash );
			// Go to next page.
			goToNextPage( e.target );
		},
		error: function( xhr, status, error ) {
			toggleLoaderIcon( e.target );
			inlineErrorMessage( $( '.js-validate-page-1' ), xhr.responseJSON.data.message );
			console.log( `Error: ${JSON.stringify( xhr.responseJSON.data.message )}` );
		},
	});
});

// Validate second page.
jQuery( '.js-validate-page-2' ).on( 'click', ( e ) => {

	e.preventDefault();

	if ( jQuery( '.js-user-profile-loader' ).prop( 'disabled' ) ) {
		// Process is running, prevent send data again.
		return false;
	}

	// Show loader next to button.
	toggleLoaderIcon( e.target );

	// Reset validation error messages.
	jQuery( '.js-validation-error-message' ).remove();
	jQuery( '.validation-error-input' ).removeClass( 'validation-error-input' );

	let validationPassed = true;

	// Fields.
	const email           = jQuery( '.ccoo-registre #email' );
	const password        = jQuery( '.ccoo-registre #pwd' );
	const repeatPassword  = jQuery( '.ccoo-registre #cpwd' );
	const dni             = jQuery( '.ccoo-registre #dni_nie' );
	const passport        = jQuery( '.ccoo-registre #passport' );
	const hash            = jQuery( '.ccoo-registre #hash' );
	const idvalidated     = jQuery( '.ccoo-registre #idvalidated' );
	const mobile          = jQuery( '.ccoo-registre #mobile' );
	const page            = 2;
	const nonce           = jQuery( '.ccoo-registre #validate_step_1_nonce' );
	const formType        = jQuery( '.ccoo-registre' ).data( 'type' );
	const isRecupera			= jQuery( '.ccoo-registre' ).data( 'recupera' )

	// Validate hash.
	if ( ! validateStringLength( hash, validation.registre_app_hash_label, 4 ) ) {
		validationPassed = false;
	}

	// Check if validation is passed or not.
	if ( ! validationPassed ) {
		// Validation fails, return false and remove loader. Dont send any data to API yet.
		toggleLoaderIcon( e.target );
		return false;
	}

	// Validation passed, send data to API.
	jQuery.ajax({
		type: 'POST',
		url: AjaxVarStep.url,
		data: {
			action: 'validate_step',
			dni_nie: dni.val(),
			mobile: mobile.val(),
			email: email.val(),
			password: password.val(),
			passport: passport.val(),
			hash: hash.val(),
			idvalidated: idvalidated.val(),
			page: page,
			form_type: formType,
			is_recupera: isRecupera,
			validate_step_1_nonce: nonce.val(),
		},

		success: function( response ) {
			toggleLoaderIcon( e.target );
			console.log( response );
			if ( response.data.hidden_form ) {
				toggleFormAndShowErrors( response.data.message );
			} else {
				// Go to next page.
				goToNextPage( e.target );
			}

		},
		error: function( xhr, status, error ) {
			toggleLoaderIcon( e.target );

			if ( xhr.responseJSON.data.hidden_form ) {
				toggleFormAndShowErrors( xhr.responseJSON.data.message );
			} else {
				inlineErrorMessage( hash, xhr.responseJSON.data.message );
			}
		}
	});

});

// Validate second page.
jQuery( '.js-validate-submit' ).on( 'click', ( e ) => {

	e.preventDefault();

	if ( jQuery( '.js-user-profile-loader' ).prop( 'disabled' ) ) {
		// Process is running, prevent send data again.
		return false;
	}

	// Show loader next to button.
	toggleLoaderIcon( e.target );

	// Reset validation error messages.
	jQuery( '.js-validation-error-message' ).remove();
	jQuery( '.validation-error-input' ).removeClass( 'validation-error-input' );

	let validationPassed = true;
	const formType    = jQuery( '.ccoo-registre' ).data( 'type' );

	// Global Fields.
	const email       = jQuery( '.ccoo-registre #email' );
	const password    = jQuery( '.ccoo-registre #pwd' );
	const dni         = jQuery( '.ccoo-registre #dni_nie' );
	const passport    = jQuery( '.ccoo-registre #passport' );
	const hash        = jQuery( '.ccoo-registre #hash' );
	const idvalidated = jQuery( '.ccoo-registre #idvalidated' );
	const mobile      = jQuery( '.ccoo-registre #mobile' );
	const nom         = jQuery( '.ccoo-registre #nom' );
	const cognom1     = jQuery( '.ccoo-registre #cognom1' );
	const cognom2     = jQuery( '.ccoo-registre #cognom2' );
	const situacio    = jQuery( '.ccoo-registre #situacio' );
	const errorSubmit = jQuery( '.ccoo-registre #response-error-submit' );
	const gdpr        = jQuery( '.ccoo-registre #gdpr' );
	const nonce       = jQuery( '.ccoo-registre #validate_step_1_nonce' );
	// Full Fields.
	if ( 'full' == formType ) {
		const address     = jQuery( '.ccoo-registre #address' );
		const address_1   = jQuery( '.ccoo-registre #address_1' );
		const address_2   = jQuery( '.ccoo-registre #address_2' );
		const bloc        = jQuery( '.ccoo-registre #bloc' );
		const zipcode     = jQuery( '.ccoo-registre #zipcode' );
		const city        = jQuery( '.ccoo-registre #city' );
		const state       = jQuery( '.ccoo-registre #state' );
		const birth       = jQuery( '.ccoo-registre #birth' );
		const studies     = jQuery( '.ccoo-registre #studies' );
		const sexe        = jQuery( '.ccoo-registre #sexe' );
		const collectiu   = jQuery( '.ccoo-registre #collectiu' );
		const categoria   = jQuery( '.ccoo-registre #categoria' );
		const area_func   = jQuery( '.ccoo-registre #area_func' );
		const procedencia = jQuery( '.ccoo-registre #procedencia' );
	}
	console.log(`Form type: ${formType}`);
	// Simple Fields.
	if ( 'simple' == formType ) {

		let codictreb   = jQuery( '.ccoo-registre #companies-result' );
		
		if ( ! codictreb ) {
			codictreb = '';
		} else {
			codictreb = codictreb.val();
		}

	}
	
	// Validation full.
	if ( 'full' == formType ) {
		// Validate zipcode.
		if ( ! validateStringLength( zipcode, validation.registre_app_zipcode_label, 5, 5 ) ) {
			validationPassed = false;
		}

		// Validate address.
		if ( ! validateStringLength( address, validation.registre_app_address_label ) ) {
			validationPassed = false;
		}

		// Validate birth.
		if ( ! validateBirthday( birth, validation.registre_app_birthdate_label ) ) {
			validationPassed = false;
		}

		// Validate studies.
		if ( ! validateStringLength( studies, 'Estudis' ) ) {
			validationPassed = false;
		}

		// Validate sexe.
		if ( ! validateStringLength( sexe, validation.registre_app_sexe_label ) ) {
			validationPassed = false;
		}

		// Validate collectiu.
		if ( ! validateStringLength( collectiu, 'Collectiu' ) ) {
			validationPassed = false;
		}
		
		// Validate categoria.
		if ( ! validateStringLength( categoria, validation.registre_app_categoria_label ) ) {
			validationPassed = false;
		}

		// Validate area_func.
		if ( ! validateStringLength( area_func, 'Àrea funcional' ) ) {
			validationPassed = false;
		}

		// Validate procedencia.
		if ( ! validateStringLength( procedencia, validation.registre_app_source_label ) ) {
			validationPassed = false;
		}

	}

	// Validation global.

	// Validate nom.
	if ( ! validateStringLength( nom, validation.registre_app_name_label, 3 ) ) {
		validationPassed = false;
	}

	// Validate cognom1.
	if ( ! validateStringLength( cognom1, validation.registre_app_last_name_1_label, 3 ) ) {
		validationPassed = false;
	}
	
	// Validate situacio.
	if ( ! validateStringLength( situacio, validation.registre_app_situacio_label ) ) {
		validationPassed = false;
	}

	// Validate gdpr.
	if ( ! gdpr.is( ':checked' ) ) {
		inlineErrorMessage( gdpr, 'Aquest camp és obligatori.' );
		validationPassed = false;
	}
	
	// Check if validation is passed or not.
	if ( ! validationPassed ) {
		// Validation fails, return false and remove loader. Dont send any data to API yet.
		jQuery( e.target ).children( '.js-user-profile-loader' ).remove();
		return false;
	}

	// Validation passed, send data to API.

	// Form full.
	if ( 'full' == formType ) {
		jQuery.ajax({
			type: 'POST',
			url: AjaxVarSubmit.url,
			data: {
				action: 'validate_submit',
				dni_nie: dni.val(),
				mobile: mobile.val(),
				email: email.val(),
				password: password.val(),
				passport: passport.val(),
				hash: hash.val(),
				idvalidated: idvalidated.val(),
				nom: nom.val(),
				cognom1: cognom1.val(),
				cognom2: cognom2.val(),
				situacio: situacio.val(),
				birth: birth.val(),
				studies: studies.val(),
				sexe: sexe.val(),
				address:address.val(),
				address_1:address_1.val(),
				address_2:address_2.val(),
				bloc:bloc.val(),
				zipcode:zipcode.val(),
				city:city.val(),
				state:state.val(),
				collectiu: collectiu.val(),
				categoria: categoria.val(),
				area_func: area_func.val(),
				procedencia: procedencia.val(),
				validate_step_1_nonce: nonce.val(),
			},
			success: function( response, status, xhr ) {
				toggleLoaderIcon( e.target );
	
				// Check if must redirect or show error.
				if ( false === response.data.redirect ) {
					inlineErrorMessage( errorSubmit, response.data.message );
					return false;
				}
				// Must redirect.
				window.location.href = response.data.redirect_to;
	
			},
			error: function( xhr, status, error ) {
				toggleLoaderIcon( e.target );
				inlineErrorMessage( errorSubmit, xhr.data.message );
			}
		});
	} else {
		jQuery.ajax({
			type: 'POST',
			url: AjaxVarSubmit.url,
			data: {
				action: 'validate_submit',
				dni_nie: dni.val(),
				mobile: mobile.val(),
				email: email.val(),
				password: password.val(),
				idvalidated: idvalidated.val(),
				nom: nom.val(),
				cognom1: cognom1.val(),
				cognom2: cognom2.val(),
				situacio: situacio.val(),
				validate_step_1_nonce: nonce.val(),
				form_type: formType,
				//codictreb: codictreb,
			},
			success: function( response, status, xhr ) {
				toggleLoaderIcon( e.target );
	
				// Check if must redirect or show error.
				if ( false === response.data.redirect ) {
					inlineErrorMessage( errorSubmit, response.data.message );
					return false;
				}
				// Must redirect.
				window.location.href = response.data.redirect_to;
	
			},
			error: function( xhr, status, error ) {
				toggleLoaderIcon( e.target );
				inlineErrorMessage( errorSubmit, xhr.data.message );
			}
		});
	}


});

// onChange email value, check domain.
jQuery( '.js-check-domain' ).on( 'change', ( e ) => {

	const field  = jQuery( e.target );
	const email  = field.val();

	if ( ! validateEmailDomain( email ) ) {
		inlineErrorMessage( field, validation.email_domain );
		return false;
	} else {
		// Reset validation error messages.
		jQuery( '.js-validation-error-message' ).remove();
		jQuery( '.validation-error-input' ).removeClass( 'validation-error-input' );
	}

});

// Validate email field.
const validateEmail = ( email ) => {

	let errorMsg = "L'adreça de correu electrònic introduïda no és vàlida; comproveu el format (p. ex., correu electrònic@domini.com)."

	if ( validation.registre_app_email_validation ) {
		errorMsg = validation.registre_app_email_validation;
	}

	// Check if they are not empty.
	if ( ! email.val() ) {
		inlineErrorMessage( email, 'Aquest camp és obligatori.' );
		return false;
	}

	if ( ! String( email.val() ).toLowerCase()
		.match( /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/ ) ) {
			inlineErrorMessage( email, errorMsg );
			return false;
	}

	if ( ! validateEmailDomain( email.val() ) ) {
		inlineErrorMessage( email, validation.email_domain );
		return false;
	}

	return true;

};

/**
 * Validate password field.
 * 
 * Must have:
 * Minimum eight characters.
 * At least one uppercase letter.
 * One lowercase letter.
 * One number.
 * 
 * @param {string} password Password.
 * @param {string} repeatPassword Repeat password. 
 * @returns {boolean}
 */
const validatePassword = ( password, repeatPassword, fromInput = false ) => {
	/*	
	if ( fromInput ) {

		password       = jQuery( password );
		repeatPassword = password.parent().parent().parent().find( 'input#cpwd' );

		console.log( password );
		console.log( repeatPassword );

		if ( ! password.hasClass( 'validation-error-input' ) || ! repeatPassword.hasClass( 'validation-error-input' ) ) {
			return false;
		}

		if ( ! password.val() || ! repeatPassword.val() ) {
			return false;
		}

	}
	*/
	let errorMsg = "L'adreça de correu electrònic introduïda no és vàlida; comproveu el format (p. ex., correu electrònic@domini.com)."

	if ( validation.registre_app_password_validation ) {
		errorMsg = validation.registre_app_password_validation;
	}

	// Check if they are not empty.
	if ( ! password.val() && ! repeatPassword.val() ) {
		inlineErrorMessage( password, 'Aquest camp és obligatori.' );
		inlineErrorMessage( repeatPassword, 'Aquest camp és obligatori.' );
		return false;
	}

	// Check if are the same.
	if ( password.val() != repeatPassword.val() ) {
		inlineErrorMessage( repeatPassword, 'Les contrasenyes no coincideixen' );
		return false;
	}

	if ( 8 > password.val().length ) {
		inlineErrorMessage( password, errorMsg );
		return false;
	}

	return true;

};

/**
 * Validate DNI / NIE field.
 * 
 * @param {DOMObject} password DNI / NIE element.
 * @returns {boolean}
 */
const validateDniNie = ( element ) => {

	let errorMsg = "L'adreça de correu electrònic introduïda no és vàlida; comproveu el format (p. ex., correu electrònic@domini.com)."

	if ( validation.registre_app_document_validation ) {
		errorMsg = validation.registre_app_document_validation;
	}

	const validChars = 'TRWAGMYFPDXBNJZSQVHLCKET';
	const nifRexp    = /^[0-9]{8}[TRWAGMYFPDXBNJZSQVHLCKET]$/i;
	const nieRexp    = /^[XYZ][0-9]{7}[TRWAGMYFPDXBNJZSQVHLCKET]$/i;
	const str        = element.val().toString().toUpperCase();

	if ( ! nifRexp.test( str ) && ! nieRexp.test( str ) ) {
		inlineErrorMessage( element, errorMsg );
		return false;
	} 

	const nie = str
		.replace(/^[X]/, '0')
		.replace(/^[Y]/, '1')
		.replace(/^[Z]/, '2');

	const letter = str.substr( -1 );
	const charIndex = parseInt( nie.substr( 0, 8 ) ) % 23;

	if ( validChars.charAt( charIndex ) === letter ) {
		return true;
	}
	inlineErrorMessage( element, errorMsg );
	return false;
};

/**
 * Validate DNI / NIE field.
 * 
 * Rules:
 * 
 * First number must be 6 or 7.
 * 
 * @param {DOMObject} password DNI / NIE element.
 * @returns {boolean}
 */
const validatePhoneNumber = ( element ) => {

	let errorMsg = "El número de telèfon introduït no és correcte, per favor, torneu-ho a provar";

	if ( validation.registre_app_mobile_validation ) {
		errorMsg = validation.registre_app_mobile_validation;
	}

	const fieldValue  = element.val();
	const firstNumber = fieldValue.slice( 0, 1 );

	// Check if contain any string.
	if ( ! /^\d+$/.test( fieldValue ) ) {
		inlineErrorMessage( element, 'El número de telèfon no és correcte' );
		return false;
	}

	// Check if it has at least/maximum of 9 characters.
	if ( 9 > fieldValue.length ) {
		inlineErrorMessage( element, 'El número de telèfon no és correcte' );
		return false;
	}

	// Check if it starts with 6 or 7.
	if ( '7' != firstNumber && '6' != firstNumber ) {
		inlineErrorMessage( element, errorMsg );
		return false;
	}

	return true;
};

/**
 * 
 * Checks the length of a string, with its minimum and maximum.
 * 
 * @param {DOMObject} element String to validate. Require entire DOMObject!
 * @param {integer} max Max. characters allowed.
 * @param {integer} min Min. charaters allowed.
 */
const validateStringLength = ( element, labelText, min = '', max = '' ) => {

	const stringValue  = element.val();
	const stringLength = stringValue.length;

	// Check if is empty. Must check if element display is NOT "none"
	if ( ! stringValue && 'none' != element.parent().parent().css( 'display' ) ) {
		inlineErrorMessage( element, 'Aquest camp és obligatori.' );
		return false;
	}

	// If min is not empty but max yes, validate only min.
	if ( ! max && min && min > stringLength ) {
		inlineErrorMessage( element, `El valor del camp ${labelText} no pot ser inferior a ${min}` );
		return false;
	}

	// If max is not empty but min yes, validate only max.
	if ( ! min && max && max < stringLength ) {
		inlineErrorMessage( element, `El valor del camp ${labelText} no pot ser superior a ${min}` );
		return false;
	}

	// If are not empty, validate.
	if ( min && max ) {
		// Check min and max.
		if ( min > stringLength || max < stringLength ) {
			inlineErrorMessage( element, `El valor del camp ${labelText} no pot ser inferior a ${min} i més gran que ${max}` );
			return false;
		}
	}

	return true;
	
}

// Show a inline error message after input field.
const inlineErrorMessage = ( element, message, scrollDuration = 500 ) => {

	// Mark input as "incorrect" adding red border.
	element.addClass( 'validation-error-input' );
	// Add message after input.
	element.parent().append( `<p class="validation-error-message js-validation-error-message"><small>${message}</small></p>` );

}

const validateBirthday = ( element, labelText ) => {
	let value = element.val();
	
	// Validate if have value
	if ( ! value ) {
		inlineErrorMessage( element, 'Aquest camp és obligatori.' );
		return false;
	}
	
	// Check if year is between 16 and 100 years old.
	let birthdayToDate = new Date( value );
	let age = ~~ ( ( Date.now() - birthdayToDate ) / ( 31557600000 ) );

	if ( 16 >= age || 100 < age ) {
		inlineErrorMessage( element, AjaxVarStep.validationMsg.registre_app_birthdate_validation );
		return false;
	}

	return true;

}

/**
 * 
 * @param {DOMElement} element Current button clicked target.
 * @returns {boolean}
 */
const toggleLoaderIcon = ( element ) => {

	// If loader DONT exist, then create it and disable submit button.
	if ( 0 == jQuery( '.js-user-profile-loader' ).length ) {
		// Append loader icon.
		jQuery( element ).append( `<div class="cmk-loader js-user-profile-loader"><div></div><div></div><div></div><div></div></div>` );
		// Disable button.
		jQuery( '.js-user-profile-loader' ).prop( 'disabled', true );
		return true;
	}

	// Remove loader icon.
	jQuery( element ).children( '.js-user-profile-loader' ).remove();
	// Enable button.
	jQuery( '.js-user-profile-loader' ).prop( 'disabled', false );
	return true;
}

/**
 * Hidden form and show errors on HTML format.
 * @param {string} errors 
 */
const toggleFormAndShowErrors = ( errors ) => {

	const form = jQuery( 'section.ccoo-registre' );

	// Remove all content of form.
	form.empty();
	// Show errors.
	form.append( errors );
}

const validateEmailDomain = ( email ) => {

		const domain     = email.substring( email.lastIndexOf( '@') +1 );
	
		if ( 'ccoo.cat' == domain ) {
			return false;
		}			

		return true;

}