jQuery( document ).ready( function( $ ) {

	/**
	 * Settings tabs navigation.
	 */
	var tstatsHash = window.location.hash;
	if ( '' !== tstatsHash ) {
		$( '.nav-tab-wrapper' ).children().removeClass( 'nav-tab-active' );
		$( '.nav-tab-wrapper a[href="' + tstatsHash + '"]' ).addClass( 'nav-tab-active' );

		$( '.tabs-content form' ).children( '.tab-content' ).addClass( 'hidden' );
		$( '.tabs-content form div' + tstatsHash.replace( '#', '#tab-' ) ).removeClass( 'hidden' );
	}

	$( '.nav-tab-wrapper a' ).click( function() {
		var tstatsTabID = $( this ).attr( 'href' ).replace( '#', '#tab-' );

		// Active tab.
		$( this ).parent().children().removeClass( 'nav-tab-active' );
		$( this ).addClass( 'nav-tab-activ e' );

		// Active tab content.
		$( '.tabs-content form' ).children( '.tab-content' ).addClass( 'hidden' );
		$( '.tabs-content form div' + tstatsTabID ).removeClass( 'hidden' );

	});

});
