document.addEventListener("DOMContentLoaded", function () {
    if (!window.L) return;
    const mapEl = document.getElementById("adminLokasiDetailMap");
    if (!mapEl) return;
    const lat = parseFloat(mapEl.dataset.latitude || "0");
    const lng = parseFloat(mapEl.dataset.longitude || "0");
    if (!lat || !lng) return;

    const map = L.map(mapEl).setView([lat, lng], 17);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    L.marker([lat, lng])
        .addTo(map)
        .bindPopup("Lokasi ID " + (mapEl.dataset.lokasiId || ""))
        .openPopup();
});
