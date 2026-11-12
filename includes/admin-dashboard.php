<?php

defined('ABSPATH') || exit;

function snr_render_dashboard_page()
{
    ?>
    <div class="wrap snr-dashboard">

        <div class="snr-dash__header">
            <h1>Panel del día</h1>
            <label class="snr-dash__date">
                Día
                <input type="date" id="snr-dash-date">
            </label>
        </div>

        <section class="snr-dash__kpis" aria-label="Resumen del día">
            <div class="snr-kpi">
                <span class="snr-kpi__label">Reservas</span>
                <span class="snr-kpi__value" data-kpi="reservations">–</span>
            </div>
            <div class="snr-kpi">
                <span class="snr-kpi__label">Comensales</span>
                <span class="snr-kpi__value" data-kpi="guests">–</span>
            </div>
            <div class="snr-kpi">
                <span class="snr-kpi__label">Ocupación</span>
                <span class="snr-kpi__value" data-kpi="occupancy">–</span>
            </div>
            <div class="snr-kpi">
                <span class="snr-kpi__label">Tronas</span>
                <span class="snr-kpi__value" data-kpi="high_chairs">–</span>
            </div>
        </section>

        <div class="snr-dash__grid">

            <section class="snr-card">
                <h2 class="snr-card__title">Ocupación por turno</h2>

                <div class="snr-service" data-service="lunch">
                    <div class="snr-service__header">
                        <span class="snr-service__name">Comida</span>
                        <span class="snr-service__count">–</span>
                    </div>
                    <div class="snr-bar">
                        <div class="snr-bar__fill"></div>
                    </div>
                </div>

                <div class="snr-service" data-service="dinner">
                    <div class="snr-service__header">
                        <span class="snr-service__name">Cena</span>
                        <span class="snr-service__count">–</span>
                    </div>
                    <div class="snr-bar">
                        <div class="snr-bar__fill"></div>
                    </div>
                </div>

            </section>

            <section class="snr-card">
                <h2 class="snr-card__title">Próximas llegadas</h2>
                <ul class="snr-arrivals" id="snr-arrivals">
                    <li class="snr-arrivals__empty">Cargando…</li>
                </ul>
            </section>

        </div>
    </div>
    <?php
}

function snr_enqueue_dashboard_assets($hook_suffix)
{
    if (!str_ends_with($hook_suffix, 'snr-dashboard')) {
        return;
    }

    wp_enqueue_style(
        'snr-admin-dashboard',
        SNR_PLUGIN_URL . 'assets/css/admin-dashboard.css',
        array(),
        SNR_VERSION
    );
}

add_action('admin_enqueue_scripts', 'snr_enqueue_dashboard_assets');

