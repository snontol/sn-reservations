const dateInput = document.querySelector('#snr-dash-date');

async function loadDashboard(date) {
    const url = new URL(snrDashboard.restUrl);
    url.searchParams.set('date', date);

    try {
        const response = await fetch(url, {
            headers: { 'X-WP-Nonce': snrDashboard.nonce },
        });

        if (!response.ok) {
            throw new Error(`Error ${response.status}`);
        }

        const data = await response.json();
        renderKpis(data.summary);
        renderServices( data.services );
        renderArrivals( data.upcoming );
    } catch (error) {
        console.error('No se pudo cargar el panel:', error);
    }
}

function renderKpis(summary) {
    document.querySelectorAll('[data-kpi]').forEach((element) => {
        const key = element.dataset.kpi;
        element.textContent = key === 'occupancy' ? `${summary[key]} %` : summary[key];
    });
}

function renderServices( services ) {
    document.querySelectorAll( '[data-service]' ).forEach( ( element ) => {
        const service = services[ element.dataset.service ];
        const count   = element.querySelector( '.snr-service__count' );
        const fill    = element.querySelector( '.snr-bar__fill' );

        // 1. Porcentaje del turno 
        const percent = service.capacity === 0 ? 0 : Math.round( ( service.guests / service.capacity ) * 100 );

        // 2. Estado bloqueado
        element.classList.toggle( 'is-blocked', service.blocked );

        // 3. Texto de la cifra
        count.textContent = service.blocked ? 'Cerrado' : `${ service.guests } / ${ service.capacity }`;

        // 4. Ancho de la barra
        fill.style.width = `${ percent }%`;
    } );
}

function renderArrivals( upcoming ) {
    const list = document.querySelector( '#snr-arrivals' );
    list.replaceChildren();

    // Sin reservas: un mensaje en lugar de la lista
    if ( upcoming.length === 0 ) {
        const empty = document.createElement( 'li' );
        empty.className   = 'snr-arrivals__empty';
        empty.textContent = 'No hay reservas para este día.';
        list.append( empty );
        return;
    }

    upcoming.forEach( ( arrival ) => {
        const item = document.createElement( 'li' );

        // 1. La hora
        const time = document.createElement( 'span' );
        time.className   = 'snr-arrivals__time';
        time.textContent = arrival.time;
        item.append( time );

    // 2. El cliente
        const guest = document.createElement( 'span' );
        guest.className = 'snr-arrivals__guest';

        const name = document.createElement( 'strong' );
        name.textContent = arrival.name;

        const personText = arrival.party_size === 1 ? 'persona' : 'personas';

        // Construimos el texto con los datos de la reserva
        let details = ` · ${ arrival.party_size } ${ personText } · ${ arrival.table }`;
        
        // Si hay tronas (high_chairs > 0), las añadimos al texto
        if ( arrival.high_chairs > 0 ) {
            details += ` · ${ arrival.high_chairs } trona${ arrival.high_chairs > 1 ? 's' : '' }`;
        }

        guest.append( name, details );
        item.append( guest );

        // 3. Las notas, solo si las hay 

        if ( arrival.notes ) {
            const notes = document.createElement( 'span' );
            notes.className = 'snr-arrivals__notes';
            notes.textContent = arrival.notes;
            item.append ( notes );
        }

        list.append( item );
    } );
}

dateInput.value = snrDashboard.today;
loadDashboard(snrDashboard.today);

dateInput.addEventListener( 'change', () => {
    loadDashboard( dateInput.value );
} );

