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

dateInput.value = snrDashboard.today;
loadDashboard(snrDashboard.today);

