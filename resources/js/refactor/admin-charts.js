(function () {
    function initCharts(cfg) {
        const ratingLabels = cfg.ratingLabels || [];
        const ratingValues = cfg.ratingValues || [];
        const kategoriLabels = cfg.kategoriLabels || [];
        const kategoriValues = cfg.kategoriValues || [];
        const wilayahLabels = cfg.wilayahLabels || [];
        const wilayahValues = cfg.wilayahValues || [];
        const jamBukaLabels = cfg.jamBukaLabels || [];
        const jamBukaValues = cfg.jamBukaValues || [];

        // Rating chart
        const ratingCanvas = document.getElementById('ratingCategoryChart');
        if (ratingCanvas && ratingLabels.length) {
            const ratingWrap = ratingCanvas.closest('.chart-wrap');
            const ratingHeight = Math.max(340, ratingLabels.length * 44);
            if (ratingWrap) {
                ratingWrap.style.height = ratingHeight + 'px';
            }

            new Chart(ratingCanvas, {
                type: 'bar',
                data: {
                    labels: ratingLabels,
                    datasets: [{
                        label: 'Mean Rating',
                        data: ratingValues,
                        backgroundColor: 'rgba(25, 135, 84, 0.65)',
                        borderColor: 'rgba(25, 135, 84, 1)',
                        borderWidth: 1,
                        borderRadius: 6,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 8,
                            bottom: 8,
                        },
                    },
                    scales: {
                        x: { beginAtZero: true, max: 5, ticks: { stepSize: 1 } },
                        y: {
                            ticks: {
                                autoSkip: false,
                            },
                        },
                    },
                },
            });
        }

        // Kategori pie
        const kategoriCanvas = document.getElementById('kategoriDistributionChart');
        if (kategoriCanvas && kategoriLabels.length) {
            const pieColors = kategoriLabels.map((_, index) => `hsl(${(index * 48) % 360}, 70%, 60%)`);
            const totalKategori = kategoriValues.reduce((sum, value) => sum + Number(value || 0), 0);
            const kategoriDisplayLabels = kategoriLabels.map((label, index) => {
                const value = Number(kategoriValues[index] || 0);
                const percent = totalKategori ? ((value / totalKategori) * 100).toFixed(1) : '0.0';
                return `${label} (${percent}%)`;
            });
            new Chart(kategoriCanvas, {
                type: 'pie',
                data: { labels: kategoriDisplayLabels, datasets: [{ label: 'Jumlah UMKM', data: kategoriValues, backgroundColor: pieColors, borderColor: '#ffffff', borderWidth: 2 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
            });
        }

        // Wilayah doughnut
        const wilayahCanvas = document.getElementById('wilayahDistributionChart');
        if (wilayahCanvas && wilayahLabels.length) {
            const wilayahColors = wilayahLabels.map((_, index) => `hsl(${(index * 46 + 210) % 360}, 75%, 60%)`);
            const totalWilayah = wilayahValues.reduce((sum, value) => sum + Number(value || 0), 0);
            const wilayahDisplayLabels = wilayahLabels.map((label, index) => {
                const value = Number(wilayahValues[index] || 0);
                const percent = totalWilayah ? ((value / totalWilayah) * 100).toFixed(1) : '0.0';
                return `${label} (${percent}%)`;
            });
            new Chart(wilayahCanvas, {
                type: 'doughnut',
                data: { labels: wilayahDisplayLabels, datasets: [{ label: 'Jumlah UMKM', data: wilayahValues, backgroundColor: wilayahColors, borderColor: '#ffffff', borderWidth: 2 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '56%', plugins: { legend: { position: 'bottom' } } },
            });
        }

        // Jam buka bar
        const jamBukaCanvas = document.getElementById('jamBukaChart');
        if (jamBukaCanvas && jamBukaLabels.length) {
            const jamBukaColors = ['rgba(59, 130, 246, 0.7)','rgba(14, 165, 233, 0.7)','rgba(249, 115, 22, 0.7)','rgba(16, 185, 129, 0.7)'];
            new Chart(jamBukaCanvas, {
                type: 'bar',
                data: { labels: jamBukaLabels, datasets: [{ label: 'Jumlah UMKM', data: jamBukaValues, backgroundColor: jamBukaColors, borderColor: jamBukaColors.map((c)=>c.replace('0.7','1')), borderWidth: 1, borderRadius: 8 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const cfgEl = document.getElementById('adminDashboardConfig');
        if (!cfgEl) return;
        let cfg = {};
        try { cfg = JSON.parse(cfgEl.textContent || '{}'); } catch (e) { cfg = {}; }
        if (typeof Chart === 'undefined') return;
        initCharts(cfg);
    });
})();
