const dateInput = document.querySelector( '#snr-dash-date' );

async function loadDashboard( date ) {
    const url = new URL( snrDashboard.restUrl );
    url.searchParams.set( 'date', date );

    try {
        const response = await fetch( url, {
            headers: { 'X-WP-Nonce': snrDashboard.nonce },
        } );

        if ( ! response.ok ) {
            throw new Error( `Error ${ response.status }` );
        }

        const data = await response.json();
        console.log( data );
    } catch ( error ) {
        console.error( 'No se pudo cargar el panel:', error );
    }
}

dateInput.value = snrDashboard.today;
loadDashboard( snrDashboard.today );