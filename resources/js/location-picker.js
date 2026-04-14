function toNumber(value, fallback) {
    const parsed = parseFloat(value);
    return Number.isFinite(parsed) ? parsed : fallback;
}

function formatLatLng(latitude, longitude) {
    return `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
}

function initLocationPicker(container) {
    if (!window.L || !container || container.dataset.initialized === '1') {
        return;
    }

    const mapId = container.dataset.mapId;
    const latitudeInputId = container.dataset.latitudeInputId;
    const longitudeInputId = container.dataset.longitudeInputId;
    const readoutId = container.dataset.readoutId;
    const initialLatitude = toNumber(container.dataset.initialLatitude, -6.861082410263256);
    const initialLongitude = toNumber(container.dataset.initialLongitude, 107.59205888361987);
    const initialZoom = parseInt(container.dataset.initialZoom || '15', 10);

    const mapElement = container.querySelector('[data-location-picker-map]');
    const latitudeInput = document.getElementById(latitudeInputId);
    const longitudeInput = document.getElementById(longitudeInputId);
    const readout = document.getElementById(readoutId);

    if (!mapElement || !latitudeInput || !longitudeInput) {
        return;
    }

    const map = L.map(mapElement, { zoomControl: true }).setView([initialLatitude, initialLongitude], initialZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    const marker = L.marker([initialLatitude, initialLongitude], {
        draggable: true,
    }).addTo(map);

    const updateFields = (latitude, longitude) => {
        latitudeInput.value = latitude.toFixed(6);
        longitudeInput.value = longitude.toFixed(6);

        if (readout) {
            readout.textContent = formatLatLng(latitude, longitude);
        }
    };

    const syncFromMarker = () => {
        const position = marker.getLatLng();
        updateFields(position.lat, position.lng);
    };

    map.on('click', (event) => {
        marker.setLatLng(event.latlng);
        syncFromMarker();
    });

    marker.on('dragend', syncFromMarker);

    updateFields(initialLatitude, initialLongitude);

    const modal = container.closest('.modal');
    if (modal) {
        modal.addEventListener('shown.bs.modal', () => {
            map.invalidateSize();
        });
    }

    window.addEventListener('resize', () => {
        map.invalidateSize();
    });

    container.dataset.initialized = '1';
    container.dataset.mapInstanceId = mapId || '';
}

function bootLocationPickers() {
    document.querySelectorAll('[data-location-picker]').forEach((container) => {
        initLocationPicker(container);
    });
}

document.addEventListener('DOMContentLoaded', bootLocationPickers);
