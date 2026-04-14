let map;
let ratingModal = null;
const umkmData = {};
const markerLookup = {};
let liveTrackingWatchId = null;
let liveTrackingUserMarker = null;
let liveTrackingRouteLine = null;
let liveTrackingDestinationMarker = null;
let liveTrackingFocused = false;
let liveTrackingAbortController = null;
let liveTrackingLastUserPosition = null;
let liveTrackingLastRouteFetchAt = 0;
let locationPermissionModal = null;
let pendingLiveTrackingPayload = null;
const locationConsentKey = 'map_live_tracking_location_consent';
const filterState = {
	searchQuery: '',
	category: 'all',
	group: 'all',
	minRating: 0,
	openNow: false,
};

function isMobileViewport() {
	return window.matchMedia('(max-width: 768px)').matches;
}

function setTrackingControlsVisible(isVisible) {
	const wrapper = document.getElementById('globalStopTrackingWrapper');
	if (!wrapper) return;

	wrapper.classList.toggle('d-none', !isVisible);
}

function createUmkmMarkerIcon(isHighlighted = false) {
	return L.divIcon({
		html: `<div class="umkm-marker ${isHighlighted ? 'is-highlighted' : ''}"><i class="fas fa-utensils"></i></div>`,
		className: 'custom-marker umkm-marker-wrapper',
		iconSize: isHighlighted ? [38, 38] : [32, 32],
		iconAnchor: isHighlighted ? [19, 35] : [16, 30],
		popupAnchor: [0, -28],
	});
}

function createUserMarkerIcon() {
	return L.divIcon({
		html: '<div class="user-live-marker"><span class="pulse-ring"></span><span class="pulse-dot"><i class="fas fa-location-arrow"></i></span></div>',
		className: 'custom-marker user-marker-wrapper',
		iconSize: [30, 30],
		iconAnchor: [15, 15],
	});
}

function hasStoredLocationConsent() {
	try {
		return window.localStorage.getItem(locationConsentKey) === 'granted';
	} catch (error) {
		return false;
	}
}

function storeLocationConsent() {
	try {
		window.localStorage.setItem(locationConsentKey, 'granted');
	} catch (error) {
		// no-op if storage unavailable
	}
}

function readMapConfig() {
	const configEl = document.getElementById('mapPageConfig');
	if (!configEl) {
		return null;
	}

	try {
		return JSON.parse(configEl.textContent || '{}');
	} catch (error) {
		console.error('Invalid map page config:', error);
		return null;
	}
}

function normalizeText(text) {
	return String(text || '').toLowerCase().trim();
}

function toCategoryKey(categoryName) {
	return normalizeText(categoryName).replace(/\s+/g, '-');
}

function parseJamToMinutes(value) {
	const cleaned = String(value || '').replace('.', ':');
	const parts = cleaned.split(':');
	const hour = parseInt(parts[0], 10);
	const minute = parts[1] ? parseInt(parts[1], 10) : 0;

	if (Number.isNaN(hour) || Number.isNaN(minute)) {
		return null;
	}

	return (hour * 60) + minute;
}

function isUmkmOpenNow(jamBukaText) {
	const jamText = normalizeText(jamBukaText);
	if (!jamText) return false;
	if (jamText.includes('24 jam')) return true;

	const match = jamText.match(/(\d{1,2}[.:]?\d{0,2})\s*[-–]\s*(\d{1,2}[.:]?\d{0,2})/);
	if (!match) return false;

	const startMinutes = parseJamToMinutes(match[1]);
	const endMinutes = parseJamToMinutes(match[2]);
	if (startMinutes === null || endMinutes === null) return false;

	const now = new Date();
	const nowMinutes = (now.getHours() * 60) + now.getMinutes();

	if (startMinutes <= endMinutes) {
		return nowMinutes >= startMinutes && nowMinutes <= endMinutes;
	}

	return nowMinutes >= startMinutes || nowMinutes <= endMinutes;
}

function generateStars(rating) {
	let starsHtml = '';
	for (let i = 1; i <= 5; i += 1) {
		if (i <= Math.floor(rating)) {
			starsHtml += '<i class="fas fa-star text-warning"></i>';
		} else if (i - 0.5 <= rating) {
			starsHtml += '<i class="fas fa-star-half-alt text-warning"></i>';
		} else {
			starsHtml += '<i class="far fa-star text-warning"></i>';
		}
	}
	return starsHtml;
}

function formatReviewDate(rawDate) {
	if (!rawDate) return '-';
	const date = new Date(rawDate);
	if (Number.isNaN(date.getTime())) return rawDate;

	return date.toLocaleDateString('id-ID', {
		day: '2-digit',
		month: 'short',
		year: 'numeric',
	});
}

function buildUlasanElement(ulasanList) {
	const wrapper = document.createElement('div');

	if (!Array.isArray(ulasanList) || ulasanList.length === 0) {
		const emptyText = document.createElement('p');
		emptyText.className = 'mb-0 text-muted';
		emptyText.textContent = 'Belum ada ulasan untuk UMKM ini.';
		wrapper.appendChild(emptyText);
		return wrapper;
	}

	ulasanList.forEach((ulasan) => {
		const item = document.createElement('div');
		item.className = 'ulasan-item';

		const header = document.createElement('div');
		header.className = 'd-flex justify-content-between align-items-center mb-1';

		const reviewer = document.createElement('strong');
		reviewer.textContent = ulasan.nama_pengulas || 'Anonymous';

		const date = document.createElement('small');
		date.className = 'text-muted';
		date.textContent = formatReviewDate(ulasan.tanggal);

		header.appendChild(reviewer);
		header.appendChild(date);

		const stars = document.createElement('div');
		stars.className = 'stars mb-1';
		stars.innerHTML = generateStars(parseInt(ulasan.nilai_rating || 0, 10));

		const comment = document.createElement('p');
		comment.className = 'mb-0';
		comment.textContent = ulasan.komentar || 'Pengguna tidak menulis ulasan.';

		item.appendChild(header);
		item.appendChild(stars);
		item.appendChild(comment);
		wrapper.appendChild(item);
	});

	return wrapper;
}

function closeDetailPanel() {
	const panel = document.getElementById('umkm-detail-panel');
	if (!panel) return;

	panel.style.animation = 'slideOutRight 0.3s ease-in';
	setTimeout(() => panel.remove(), 300);
}

function toggleUlasan(containerId, triggerBtn = null) {
	const ulasanContainer = document.getElementById(containerId);
	if (!ulasanContainer) return;

	ulasanContainer.classList.toggle('d-none');
	const isHidden = ulasanContainer.classList.contains('d-none');
	if (triggerBtn) {
		triggerBtn.innerHTML = isHidden
			? '<i class="fas fa-comments me-1"></i>Lihat ulasan'
			: '<i class="fas fa-comments me-1"></i>Sembunyikan ulasan';
	}
}

function setResultInfo(visibleCount) {
	const info = document.getElementById('mapResultInfo');
	if (!info) return;

	const totalCount = Object.keys(umkmData).length;
	if (visibleCount === totalCount) {
		info.textContent = `Menampilkan semua UMKM (${totalCount})`;
	} else {
		info.textContent = `Menampilkan ${visibleCount} dari ${totalCount} UMKM`;
	}
}

function renderCategoryChips() {
	const chipsContainer = document.getElementById('categoryChips');
	if (!chipsContainer) return;

	const categories = Array.from(
		new Set(Object.values(umkmData).map((item) => item.kategori).filter(Boolean)),
	).sort((a, b) => a.localeCompare(b, 'id-ID'));

	chipsContainer.innerHTML = '';

	const allChip = document.createElement('button');
	allChip.type = 'button';
	allChip.className = 'category-chip active';
	allChip.dataset.category = 'all';
	allChip.textContent = 'Semua';
	chipsContainer.appendChild(allChip);

	categories.forEach((categoryName) => {
		const chip = document.createElement('button');
		chip.type = 'button';
		chip.className = 'category-chip';
		chip.dataset.category = toCategoryKey(categoryName);
		chip.textContent = categoryName;
		chipsContainer.appendChild(chip);
	});

	chipsContainer.querySelectorAll('.category-chip').forEach((chip) => {
		chip.addEventListener('click', function clickChip() {
			chipsContainer.querySelectorAll('.category-chip').forEach((c) => c.classList.remove('active'));
			this.classList.add('active');
			filterState.category = this.dataset.category;
			applyMapFilters(false);
		});
	});
}

function renderGroupFilters() {
    // 1. Ambil elemen Desktop (Asumsinya ini masih berupa <select>)
    const desktopGroup = document.getElementById('desktopGroupFilter');

    // 2. Ambil elemen Mobile (SANGAT PENTING: Gunakan ID dari tag <ul>, BUKAN hidden input)
    // Sesuai panduan sebelumnya, pastikan <ul> Anda memiliki id="list-mobileGroupFilter"
    const listMobileGroup = document.getElementById('list-mobileGroupFilter');

    if (!desktopGroup && !listMobileGroup) return;

    // Mengambil data unik kelompok dari umkmData
    const groups = Array.from(
        new Set(Object.values(umkmData).map((item) => item.kelompok).filter(Boolean)),
    ).sort((a, b) => a.localeCompare(b, 'id-ID'));

    // 3. Fungsi pintar untuk menyusun HTML berdasarkan jenis elemennya
    const buildOptions = (containerEl, targetId) => {
        if (!containerEl) return;

        // JIKA ELEMEN ADALAH <select> (Bawaan HTML)
        if (containerEl.tagName.toLowerCase() === 'select') {
            containerEl.innerHTML = '<option value="all">Semua Kelompok</option>';

            groups.forEach((groupName) => {
                const option = document.createElement('option');
                option.value = toCategoryKey(groupName);
                option.textContent = groupName;
                containerEl.appendChild(option);
            });
        }

        // JIKA ELEMEN ADALAH <ul> (Dropdown Bootstrap)
        else if (containerEl.tagName.toLowerCase() === 'ul') {
            let html = `<li><button class="dropdown-item active" type="button" data-value="all" data-target="${targetId}">Semua Kelompok</button></li>`;

            groups.forEach((groupName) => {
                const value = toCategoryKey(groupName);
                html += `<li><button class="dropdown-item" type="button" data-value="${value}" data-target="${targetId}">${groupName}</button></li>`;
            });

            containerEl.innerHTML = html;
        }
    };

    // 4. Jalankan fungsinya
    buildOptions(desktopGroup, 'desktopGroupFilter');
    buildOptions(listMobileGroup, 'mobileGroupFilter');
}

function syncFilterControls() {
	const desktopMinRating = document.getElementById('desktopMinRating');
	const mobileMinRating = document.getElementById('mobileMinRating');
	const desktopOpenNow = document.getElementById('desktopOpenNow');
	const mobileOpenNow = document.getElementById('mobileOpenNow');
	const desktopGroup = document.getElementById('desktopGroupFilter');
	const mobileGroup = document.getElementById('mobileGroupFilter');

	if (desktopMinRating) desktopMinRating.value = String(filterState.minRating);
	if (mobileMinRating) mobileMinRating.value = String(filterState.minRating);
	if (desktopOpenNow) desktopOpenNow.checked = filterState.openNow;
	if (mobileOpenNow) mobileOpenNow.checked = filterState.openNow;
	if (desktopGroup) desktopGroup.value = filterState.group;
	if (mobileGroup) mobileGroup.value = filterState.group;
}

function applyMapFilters(focusMap = false) {
	const visibleLatLngs = [];
	let visibleCount = 0;

	Object.values(umkmData).forEach((item) => {
		const marker = markerLookup[item.id];
		if (!marker) return;

		const searchTarget = normalizeText(`${item.nama_umkm} ${item.alamat_lengkap} ${item.kategori}`);
		const matchesSearch = !filterState.searchQuery || searchTarget.includes(filterState.searchQuery);
		const matchesCategory = filterState.category === 'all' || item.kategori_key === filterState.category;
		const matchesGroup = filterState.group === 'all' || item.kelompok_key === filterState.group;
		const matchesRating = Number(item.rating_avg || 0) >= Number(filterState.minRating || 0);
		const matchesOpenNow = !filterState.openNow || isUmkmOpenNow(item.jam_buka);
		const isVisible = matchesSearch && matchesCategory && matchesGroup && matchesRating && matchesOpenNow;

		if (isVisible) {
			if (!map.hasLayer(marker)) marker.addTo(map);
			visibleLatLngs.push(marker.getLatLng());
			visibleCount += 1;
		} else if (map.hasLayer(marker)) {
			marker.closePopup();
			map.removeLayer(marker);
		}
	});

	setResultInfo(visibleCount);

	if (!focusMap || visibleLatLngs.length === 0) return;
	if (visibleLatLngs.length === 1) {
		map.setView(visibleLatLngs[0], 18);
	} else {
		map.fitBounds(visibleLatLngs, { padding: [50, 50], maxZoom: 17 });
	}
}

function createPopupElement(item) {
	const wrapper = document.createElement('div');
	wrapper.className = 'marker-popup';

	const title = document.createElement('h6');
	title.className = 'mb-1';
	title.textContent = item.nama_umkm;

	const address = document.createElement('p');
	address.className = 'mb-2 text-muted small';
	address.textContent = item.alamat_lengkap || '-';

	const button = document.createElement('button');
	button.type = 'button';
	button.className = 'btn btn-primary btn-sm';
	button.innerHTML = '<i class="fas fa-info-circle me-1"></i>Lihat Detail';
	button.addEventListener('click', () => {
		showUmkmDetail(item.id);
	});

	wrapper.appendChild(title);
	wrapper.appendChild(address);
	wrapper.appendChild(button);
	return wrapper;
}

function showUmkmDetail(umkmId) {
	const data = umkmData[umkmId];
	if (!data) return;

	closeDetailPanel();

	const panel = document.createElement('div');
	panel.id = 'umkm-detail-panel';
	panel.className = 'umkm-detail-panel';

	const header = document.createElement('div');
	header.className = 'detail-header';

	const title = document.createElement('h4');
	title.className = 'mb-0';
	title.textContent = data.nama_umkm;

	const closeBtn = document.createElement('button');
	closeBtn.type = 'button';
	closeBtn.className = 'custom-btn-close';
	closeBtn.innerHTML = '<i class="fas fa-times"></i>';
	closeBtn.addEventListener('click', closeDetailPanel);

	header.appendChild(title);
	header.appendChild(closeBtn);

	const content = document.createElement('div');
	content.className = 'detail-content';

	const photoSection = document.createElement('div');
	photoSection.className = 'detail-section';
	const photoEl = document.createElement('img');
	photoEl.className = 'detail-umkm-photo';
	photoEl.src = data.foto_umkm_url || '/images/default-umkm.svg';
	photoEl.alt = `Foto ${data.nama_umkm}`;
	photoEl.onerror = function onImageError() {
		this.onerror = null;
		this.src = '/images/default-umkm.svg';
	};
	photoSection.appendChild(photoEl);
	content.appendChild(photoSection);

	const makeSection = (icon, label, node) => {
		const section = document.createElement('div');
		section.className = 'detail-section';
		const h6 = document.createElement('h6');
		h6.innerHTML = `<i class="fas ${icon} me-2"></i>${label}`;
		section.appendChild(h6);
		section.appendChild(node);
		return section;
	};

	const kategoriBadge = document.createElement('span');
	kategoriBadge.className = 'badge bg-primary';
	kategoriBadge.textContent = data.kategori;
	content.appendChild(makeSection('fa-tag', 'Kategori', kategoriBadge));

	const jamText = document.createElement('p');
	jamText.className = 'mb-0';
	jamText.textContent = data.jam_buka || '-';
	content.appendChild(makeSection('fa-clock', 'Jam Buka', jamText));

	const alamatText = document.createElement('p');
	alamatText.className = 'mb-0';
	alamatText.textContent = data.alamat_lengkap || '-';
	content.appendChild(makeSection('fa-map-marker-alt', 'Alamat Lengkap', alamatText));

	const ratingWrap = document.createElement('div');
	const ratingContainer = document.createElement('div');
	ratingContainer.className = 'd-flex align-items-center';
	const starsDiv = document.createElement('div');
	starsDiv.className = 'stars me-2';
	starsDiv.innerHTML = generateStars(data.rating_avg || 0);
	const ratingText = document.createElement('small');
	ratingText.className = 'text-muted';
	ratingText.textContent = `(${(Number(data.rating_avg) || 0).toFixed(1)} • ${Number(data.rating_count) || 0} ulasan)`;
	ratingContainer.appendChild(starsDiv);
	ratingContainer.appendChild(ratingText);

	const ulasanBtn = document.createElement('button');
	ulasanBtn.type = 'button';
	ulasanBtn.className = 'btn btn-link btn-sm p-0 mt-2';
	ulasanBtn.innerHTML = '<i class="fas fa-comments me-1"></i>Lihat ulasan';

	const ulasanContainer = document.createElement('div');
	ulasanContainer.id = `ulasan-list-${data.id}`;
	ulasanContainer.className = 'ulasan-list-container d-none mt-2';
	ulasanContainer.appendChild(buildUlasanElement(data.ulasan));
	ulasanBtn.addEventListener('click', function onToggle() {
		toggleUlasan(ulasanContainer.id, this);
	});

	ratingWrap.appendChild(ratingContainer);
	ratingWrap.appendChild(ulasanBtn);
	ratingWrap.appendChild(ulasanContainer);
	content.appendChild(makeSection('fa-star', 'Rating', ratingWrap));

	if (data.deskripsi) {
		const desc = document.createElement('p');
		desc.className = 'mb-0';
		desc.textContent = data.deskripsi;
		content.appendChild(makeSection('fa-info-circle', 'Deskripsi', desc));
	}

	const menuWrap = document.createElement('div');
	if (Array.isArray(data.menu) && data.menu.length > 0) {
		const menuList = document.createElement('div');
		menuList.className = 'menu-list d-grid gap-2';

		data.menu.forEach((menuItem) => {
			const menuRow = document.createElement('div');
			menuRow.className = 'menu-item d-flex align-items-center gap-2';

			const menuImage = document.createElement('img');
			menuImage.className = 'menu-thumb';
			menuImage.src = menuItem.foto_menu_url || '/images/default-menu.svg';
			menuImage.alt = `Foto ${menuItem.nama_menu}`;
			menuImage.onerror = function onMenuImageError() {
				this.onerror = null;
				this.src = '/images/default-menu.svg';
			};

			const menuInfo = document.createElement('div');
			menuInfo.className = 'flex-grow-1';
			const menuName = document.createElement('div');
			menuName.className = 'fw-semibold';
			menuName.textContent = menuItem.nama_menu || '-';
			const menuPrice = document.createElement('small');
			menuPrice.className = 'text-muted';
			menuPrice.textContent = `Rp${new Intl.NumberFormat('id-ID').format(Number(menuItem.harga_menu || 0))}`;

			menuInfo.appendChild(menuName);
			menuInfo.appendChild(menuPrice);
			menuRow.appendChild(menuImage);
			menuRow.appendChild(menuInfo);
			menuList.appendChild(menuRow);
		});

		menuWrap.appendChild(menuList);
	} else {
		const emptyMenuText = document.createElement('p');
		emptyMenuText.className = 'mb-0 text-muted';
		emptyMenuText.textContent = 'Belum ada data menu.';
		menuWrap.appendChild(emptyMenuText);
	}

	content.appendChild(makeSection('fa-utensils', 'Menu UMKM', menuWrap));

	const resolvedCoords = resolveUmkmCoordinates(data);

	const actionsSection = document.createElement('div');
	actionsSection.className = 'detail-actions mt-3';
	const row = document.createElement('div');
	row.className = 'row g-2';

	const ratingCol = document.createElement('div');
	ratingCol.className = 'col-6';
	const ratingBtn = document.createElement('button');
	ratingBtn.type = 'button';
	ratingBtn.className = 'btn btn-success btn-sm w-100';
	ratingBtn.innerHTML = '<i class="fas fa-star me-1"></i>Beri Rating';
	ratingBtn.addEventListener('click', () => openRatingModal(data.id, data.nama_umkm));
	ratingCol.appendChild(ratingBtn);

	const backCol = document.createElement('div');
	backCol.className = 'col-6';
	const backLink = document.createElement('a');
	backLink.href = window.mapPageConfig?.landingUrl || '/';
	backLink.className = 'btn btn-outline-primary btn-sm w-100';
	backLink.innerHTML = '<i class="fas fa-arrow-left me-1"></i>Kembali';
	backCol.appendChild(backLink);

	const liveTrackCol = document.createElement('div');
	liveTrackCol.className = 'col-6';
	const liveTrackBtn = document.createElement('button');
	liveTrackBtn.type = 'button';
	liveTrackBtn.className = 'btn btn-info btn-sm w-100';
	liveTrackBtn.innerHTML = '<i class="fas fa-route me-1"></i>Live Track';
	liveTrackBtn.disabled = !resolvedCoords;
	liveTrackBtn.addEventListener('click', () => {
		if (!resolvedCoords) {
			showAlert('error', 'Koordinat UMKM belum tersedia.');
			return;
		}

		startLiveTrackingTo(resolvedCoords[0], resolvedCoords[1], data.nama_umkm);
	});
	liveTrackCol.appendChild(liveTrackBtn);

	row.appendChild(ratingCol);
	row.appendChild(backCol);
	row.appendChild(liveTrackCol);
	actionsSection.appendChild(row);
	content.appendChild(actionsSection);

	const liveTrackStatus = document.createElement('div');
	liveTrackStatus.className = 'live-track-status text-muted small mt-2 w-100';
	liveTrackStatus.textContent = 'Live tracking belum dimulai.';
	content.appendChild(liveTrackStatus);

	panel.appendChild(header);
	panel.appendChild(content);
	document.body.appendChild(panel);
}

function resetStars() {
	document.querySelectorAll('.rating-stars .star').forEach((star) => {
		star.className = 'far fa-star star';
	});
}

function setStars(rating) {
	document.querySelectorAll('.rating-stars .star').forEach((star, index) => {
		star.className = index < rating ? 'fas fa-star star text-warning' : 'far fa-star star';
	});
}

function showAlert(type, message) {
	const alertDiv = document.createElement('div');
	alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
	alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: calc(100vw - 30px);';
	alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
	document.body.appendChild(alertDiv);
	setTimeout(() => {
		if (alertDiv.parentNode) alertDiv.remove();
	}, 4000);
}

function resolveUmkmCoordinates(umkmDataItem) {
	const lat = Number(umkmDataItem?.latitude ?? umkmDataItem?.lokasi?.latitude);
	const lng = Number(umkmDataItem?.longitude ?? umkmDataItem?.lokasi?.longitude);

	if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
		return null;
	}

	return [lat, lng];
}

function ensureLocationPermissionModal() {
	if (document.getElementById('locationPermissionModal')) {
		if (!locationPermissionModal) {
			locationPermissionModal = new bootstrap.Modal(document.getElementById('locationPermissionModal'));
		}
		return;
	}

	const modal = document.createElement('div');
	modal.className = 'modal fade';
	modal.id = 'locationPermissionModal';
	modal.tabIndex = -1;
	modal.setAttribute('aria-hidden', 'true');
	modal.innerHTML = `
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><i class="fas fa-location-dot me-2"></i>Izin Lokasi</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p class="mb-2">Fitur Live Track membutuhkan akses lokasi Anda untuk menghitung rute jalan ke UMKM.</p>
					<ul class="small text-muted mb-0 ps-3">
						<li>Lokasi dipakai hanya saat tracking aktif.</li>
						<li>Rute dihitung menggunakan OSRM.</li>
					</ul>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="button" class="btn btn-primary" id="confirmLocationPermissionBtn">Izinkan & Mulai</button>
				</div>
			</div>
		</div>
	`;

	document.body.appendChild(modal);
	locationPermissionModal = new bootstrap.Modal(modal);

	document.getElementById('confirmLocationPermissionBtn')?.addEventListener('click', () => {
		if (!pendingLiveTrackingPayload) {
			locationPermissionModal?.hide();
			return;
		}

		const { latitude, longitude, umkmName } = pendingLiveTrackingPayload;
		locationPermissionModal?.hide();
		startLiveTrackingTo(latitude, longitude, umkmName, true);
	});
}

function formatDistance(meters) {
	if (meters < 1000) {
		return `${Math.round(meters)} m`;
	}

	return `${(meters / 1000).toFixed(2)} km`;
}

function formatDuration(seconds) {
	if (!Number.isFinite(seconds) || seconds <= 0) {
		return '-';
	}

	const totalMinutes = Math.round(seconds / 60);
	if (totalMinutes < 60) {
		return `${totalMinutes} menit`;
	}

	const hours = Math.floor(totalMinutes / 60);
	const minutes = totalMinutes % 60;
	if (minutes === 0) {
		return `${hours} jam`;
	}

	return `${hours} jam ${minutes} menit`;
}

function setLiveTrackStatus(message, tone = 'muted') {
	document.querySelectorAll('.live-track-status').forEach((el) => {
		el.classList.remove('text-muted', 'text-success', 'text-danger', 'text-primary');
		el.classList.add(`text-${tone}`);
		el.textContent = message;
	});
}

async function fetchOsrmRoute(userPosition, destination) {
	if (liveTrackingAbortController) {
		liveTrackingAbortController.abort();
	}

	liveTrackingAbortController = new AbortController();

	const [userLat, userLng] = userPosition;
	const [destLat, destLng] = destination;
	const endpoint = `https://router.project-osrm.org/route/v1/driving/${userLng},${userLat};${destLng},${destLat}?overview=full&geometries=geojson`;

	const response = await fetch(endpoint, {
		signal: liveTrackingAbortController.signal,
		headers: { Accept: 'application/json' },
	});

	if (!response.ok) {
		throw new Error('OSRM response not ok');
	}

	const data = await response.json();
	const bestRoute = data?.routes?.[0];
	const coordinates = bestRoute?.geometry?.coordinates;

	if (!bestRoute || !Array.isArray(coordinates) || coordinates.length < 2) {
		throw new Error('OSRM route invalid');
	}

	const latLngs = coordinates.map((point) => [point[1], point[0]]);
	return {
		latLngs,
		distance: Number(bestRoute.distance || 0),
		duration: Number(bestRoute.duration || 0),
	};
}

function stopLiveTracking(showMessage = false) {
	if (liveTrackingWatchId !== null && navigator.geolocation) {
		navigator.geolocation.clearWatch(liveTrackingWatchId);
	}

	liveTrackingWatchId = null;
	liveTrackingFocused = false;
	liveTrackingLastUserPosition = null;
	liveTrackingLastRouteFetchAt = 0;

	if (liveTrackingAbortController) {
		liveTrackingAbortController.abort();
		liveTrackingAbortController = null;
	}

	if (liveTrackingUserMarker) {
		map.removeLayer(liveTrackingUserMarker);
		liveTrackingUserMarker = null;
	}

	if (liveTrackingRouteLine) {
		map.removeLayer(liveTrackingRouteLine);
		liveTrackingRouteLine = null;
	}

	if (liveTrackingDestinationMarker) {
		map.removeLayer(liveTrackingDestinationMarker);
		liveTrackingDestinationMarker = null;
	}

	setLiveTrackStatus('Live tracking belum dimulai.', 'muted');
	setTrackingControlsVisible(false);

	if (showMessage) {
		showAlert('success', 'Live tracking dihentikan.');
	}
}

function startLiveTrackingTo(latitude, longitude, umkmName = 'UMKM', skipPermissionDialog = false) {
	const destination = [Number(latitude), Number(longitude)];
	if (!Number.isFinite(destination[0]) || !Number.isFinite(destination[1])) {
		showAlert('error', 'Koordinat tujuan tidak valid.');
		return;
	}

	if (!skipPermissionDialog) {
		if (isMobileViewport()) {
			closeDetailPanel();
		}

		if (hasStoredLocationConsent()) {
			startLiveTrackingTo(destination[0], destination[1], umkmName, true);
			return;
		}

		ensureLocationPermissionModal();
		pendingLiveTrackingPayload = {
			latitude: destination[0],
			longitude: destination[1],
			umkmName,
		};
		locationPermissionModal?.show();
		return;
	}

	if (!navigator.geolocation) {
		showAlert('error', 'Browser Anda tidak mendukung geolocation.');
		return;
	}

	stopLiveTracking(false);
	setTrackingControlsVisible(true);

	setLiveTrackStatus('Mencari lokasi Anda...', 'primary');

	liveTrackingDestinationMarker = L.circleMarker(destination, {
		radius: 7,
		color: '#2563eb',
		fillColor: '#2563eb',
		fillOpacity: 0.9,
		weight: 2,
	})
		.addTo(map)
		.bindTooltip(`Tujuan: ${umkmName}`, { direction: 'top' });

	navigator.geolocation.getCurrentPosition(
		() => {
			storeLocationConsent();

			liveTrackingWatchId = navigator.geolocation.watchPosition(
		async (position) => {
			const userPosition = [position.coords.latitude, position.coords.longitude];

			if (!liveTrackingUserMarker) {
				liveTrackingUserMarker = L.marker(userPosition, {
					icon: createUserMarkerIcon(),
					zIndexOffset: 1100,
				}).addTo(map).bindTooltip('Posisi Anda');
			} else {
				liveTrackingUserMarker.setLatLng(userPosition);
			}

			const movedDistance = liveTrackingLastUserPosition
				? map.distance(userPosition, liveTrackingLastUserPosition)
				: Number.POSITIVE_INFINITY;
			const now = Date.now();
			const shouldFetchRoute = movedDistance >= 20 || (now - liveTrackingLastRouteFetchAt) >= 5000;

			if (!shouldFetchRoute) {
				return;
			}

			liveTrackingLastUserPosition = userPosition;
			liveTrackingLastRouteFetchAt = now;

			setLiveTrackStatus('Memuat rute jalan OSRM...', 'primary');

			try {
				const route = await fetchOsrmRoute(userPosition, destination);

				if (!liveTrackingRouteLine) {
					liveTrackingRouteLine = L.polyline(route.latLngs, {
						color: '#0ea5e9',
						weight: 4,
						opacity: 0.9,
					}).addTo(map);
				} else {
					liveTrackingRouteLine.setLatLngs(route.latLngs);
				}

				setLiveTrackStatus(
					`Rute ke ${umkmName}: ${formatDistance(route.distance)} (${formatDuration(route.duration)})`,
					'success',
				);

				if (!liveTrackingFocused) {
					map.fitBounds(liveTrackingRouteLine.getBounds(), { padding: [50, 50], maxZoom: 17 });
					liveTrackingFocused = true;
				}
			} catch (error) {
				if (error.name === 'AbortError') {
					return;
				}

				console.error('OSRM error:', error);
				setLiveTrackStatus('Gagal memuat rute jalan OSRM. Pastikan internet stabil.', 'danger');
			}
		},
		(error) => {
			let message = 'Gagal mendapatkan lokasi Anda.';
			if (error.code === 1) {
				message = 'Izin lokasi ditolak. Aktifkan GPS/lokasi untuk live tracking.';
			} else if (error.code === 2) {
				message = 'Lokasi tidak tersedia. Coba lagi di area dengan sinyal lebih baik.';
			} else if (error.code === 3) {
				message = 'Permintaan lokasi timeout. Coba lagi.';
			}

			setLiveTrackStatus(message, 'danger');
			showAlert('error', message);
			stopLiveTracking(false);
		},
		{
			enableHighAccuracy: true,
			maximumAge: 4000,
			timeout: 12000,
		},
	);
		},
		(error) => {
			let message = 'Gagal mendapatkan izin lokasi.';
			if (error.code === 1) {
				message = 'Izin lokasi ditolak. Aktifkan akses lokasi lalu coba lagi.';
			} else if (error.code === 2) {
				message = 'Lokasi tidak tersedia. Coba lagi di area dengan sinyal lebih baik.';
			} else if (error.code === 3) {
				message = 'Permintaan lokasi timeout. Coba lagi.';
			}

			setLiveTrackStatus(message, 'danger');
			showAlert('error', message);
			setTrackingControlsVisible(false);
		},
		{
			enableHighAccuracy: true,
			maximumAge: 0,
			timeout: 10000,
		},
	);
}

function openRatingModal(umkmId, umkmName) {
	const modalEl = document.getElementById('ratingModal');
	if (!modalEl) return;

	if (!ratingModal) {
		ratingModal = new bootstrap.Modal(modalEl);
	}

	document.getElementById('ratingUmkmId').value = umkmId;
	document.getElementById('umkmName').textContent = umkmName;
	document.getElementById('ratingForm').reset();
	document.getElementById('nilaiRating').value = '0';
	document.getElementById('ratingText').textContent = 'Belum dipilih';
	resetStars();
	ratingModal.show();
}

function initRatingFeature() {
	const starsWrapper = document.querySelector('.rating-stars');
	const ratingForm = document.getElementById('ratingForm');
	if (!starsWrapper || !ratingForm) return;

	starsWrapper.querySelectorAll('.star').forEach((star) => {
		star.addEventListener('click', function clickStar() {
			const rating = parseInt(this.getAttribute('data-rating'), 10);
			document.getElementById('nilaiRating').value = String(rating);
			setStars(rating);
			const ratingTexts = ['Sangat Buruk', 'Buruk', 'Cukup', 'Baik', 'Sangat Baik'];
			document.getElementById('ratingText').textContent = `${ratingTexts[rating - 1]} (${rating}/5)`;
		});

		star.addEventListener('mouseenter', function hoverStar() {
			setStars(parseInt(this.getAttribute('data-rating'), 10));
		});
	});

	starsWrapper.addEventListener('mouseleave', () => {
		setStars(parseInt(document.getElementById('nilaiRating').value, 10));
	});

	ratingForm.addEventListener('submit', async (event) => {
		event.preventDefault();

		try {
			const response = await fetch(window.mapPageConfig.ratingStoreUrl, {
				method: 'POST',
				body: new FormData(ratingForm),
				headers: {
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
					Accept: 'application/json',
				},
			});

			const data = await response.json();
			if (!response.ok || !data.success) {
				throw new Error(data.message || 'Gagal menyimpan rating.');
			}

			ratingModal?.hide();
			showAlert('success', data.message || 'Rating berhasil dikirim.');
			setTimeout(() => window.location.reload(), 1200);
		} catch (error) {
			console.error(error);
			showAlert('error', 'Terjadi kesalahan saat mengirim rating.');
		}
	});
}

function initMapFeature(config) {
	if (!window.L) return;

	const mapContainer = document.createElement('div');
	mapContainer.id = 'map';
	document.body.insertBefore(mapContainer, document.body.firstChild);

	map = L.map('map', { zoomControl: false }).setView([0, 0], 2);
	L.control.zoom({ position: 'bottomright' }).addTo(map);
	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
	}).addTo(map);

	const upi = config.upiCenter || {};
	if (typeof upi.latitude === 'number' && typeof upi.longitude === 'number') {
		const center = [upi.latitude, upi.longitude];
		L.circle(center, {
			color: 'blue',
			fillColor: '#3388ff',
			fillOpacity: 0.2,
			weight: 2,
			radius: Number(upi.radius || 1000),
		}).addTo(map).bindPopup('Radius 1km dari UPI');

		L.marker(center).addTo(map).bindPopup('Pusat UPI (Universitas Pendidikan Indonesia)');
	}

	const locations = [];
	(config.umkms || []).forEach((item) => {
		const data = {
			...item,
			kategori_key: toCategoryKey(item.kategori),
			kelompok_key: toCategoryKey(item.kelompok),
		};
		umkmData[item.id] = data;

		const marker = L.marker([item.latitude, item.longitude], {
			icon: createUmkmMarkerIcon(false),
			zIndexOffset: 600,
		}).addTo(map);
		marker.bindPopup(createPopupElement(data), { maxWidth: 250, minWidth: 200 });
		markerLookup[item.id] = marker;
		locations.push([item.latitude, item.longitude]);
	});

	const mapSearchInput = document.getElementById('mapSearchInput');
	const mapSearchBtn = document.getElementById('mapSearchBtn');
	const toggleSearchFiltersBtn = document.getElementById('toggleSearchFiltersBtn');
	const toggleMoreFiltersBtn = document.getElementById('toggleMoreFiltersBtn');
	const searchFilterDropdown = document.getElementById('searchFilterDropdown');
	const mapControls = document.getElementById('mapControls');
	const desktopFilterPanel = document.getElementById('desktopFilterPanel');
	const mobileFilterSheet = document.getElementById('mobileFilterSheet');
	const mobileFilterBackdrop = document.getElementById('mobileFilterBackdrop');

	const showSearchFilters = () => {
		searchFilterDropdown?.classList.remove('d-none');
	};

	const hideSearchFilters = () => {
		if (filterState.searchQuery) return;
		searchFilterDropdown?.classList.add('d-none');
		desktopFilterPanel?.classList.add('d-none');
	};

	if (mapSearchInput) {
		mapSearchInput.addEventListener('focus', () => {
			showSearchFilters();
		});

		mapSearchInput.addEventListener('input', function onSearchInput() {
			filterState.searchQuery = normalizeText(this.value);
			if (filterState.searchQuery) {
				showSearchFilters();
			} else {
				hideSearchFilters();
			}
			applyMapFilters(false);
		});

		mapSearchInput.addEventListener('keydown', (event) => {
			if (event.key === 'Enter') {
				event.preventDefault();
				filterState.searchQuery = normalizeText(mapSearchInput.value);
				applyMapFilters(true);
			}
		});
	}

	mapSearchBtn?.addEventListener('click', () => {
		showSearchFilters();
		filterState.searchQuery = normalizeText(mapSearchInput?.value || '');
		applyMapFilters(true);
	});

	toggleSearchFiltersBtn?.addEventListener('click', () => {
		searchFilterDropdown?.classList.toggle('d-none');
	});

document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('dropdown-item')) {

        const targetId = e.target.getAttribute('data-target');

        if(targetId) {
            const selectedValue = e.target.getAttribute('data-value');
            const selectedText = e.target.innerHTML;

            // 1. Update teks pada tombol dropdown
            document.getElementById('text-' + targetId).innerHTML = selectedText;

            // 2. Update nilai pada hidden input
            const hiddenInput = document.getElementById(targetId);
            hiddenInput.value = selectedValue;

            // 3. Pindahkan warna abu-abu (active)
            const parentMenu = e.target.closest('.dropdown-menu');
            parentMenu.querySelectorAll('.dropdown-item').forEach(el => el.classList.remove('active'));
            e.target.classList.add('active');

            // KITA HAPUS AUTO-APPLY DI SINI.
            // Aplikasi sekarang akan diam dan menunggu pengguna menekan "Terapkan"
        }
    }
});

	const closeMobileSheet = () => {
		mobileFilterSheet?.classList.add('d-none');
		mobileFilterBackdrop?.classList.add('d-none');
		document.body.classList.remove('sheet-open');
	};

	toggleMoreFiltersBtn?.addEventListener('click', () => {
		syncFilterControls();
		if (window.innerWidth <= 768) {
			mobileFilterSheet?.classList.remove('d-none');
			mobileFilterBackdrop?.classList.remove('d-none');
			document.body.classList.add('sheet-open');
		} else {
			desktopFilterPanel?.classList.toggle('d-none');
		}
	});

	document.getElementById('closeMobileFilterSheet')?.addEventListener('click', closeMobileSheet);
	mobileFilterBackdrop?.addEventListener('click', closeMobileSheet);

	document.getElementById('desktopApplyFilters')?.addEventListener('click', () => {
		filterState.group = document.getElementById('desktopGroupFilter').value || 'all';
		filterState.minRating = parseFloat(document.getElementById('desktopMinRating').value || '0');
		filterState.openNow = document.getElementById('desktopOpenNow').checked;
		syncFilterControls();
		applyMapFilters(true);
	});

	document.getElementById('desktopResetFilters')?.addEventListener('click', () => {
		filterState.group = 'all';
		filterState.minRating = 0;
		filterState.openNow = false;
		syncFilterControls();
		applyMapFilters(true);
	});

	document.getElementById('mobileApplyFilters')?.addEventListener('click', () => {
		filterState.group = document.getElementById('mobileGroupFilter').value || 'all';
		filterState.minRating = parseFloat(document.getElementById('mobileMinRating').value || '0');
		filterState.openNow = document.getElementById('mobileOpenNow').checked;
		syncFilterControls();
		closeMobileSheet();
		applyMapFilters(true);
	});

	document.getElementById('mobileResetFilters')?.addEventListener('click', () => {
		filterState.group = 'all';
		filterState.minRating = 0;
		filterState.openNow = false;
		syncFilterControls();
		closeMobileSheet();
		applyMapFilters(true);
	});

	document.getElementById('globalStopTrackingBtn')?.addEventListener('click', () => {
		stopLiveTracking(true);
	});

	renderCategoryChips();
	renderGroupFilters();
	syncFilterControls();
	if (filterState.searchQuery) {
		showSearchFilters();
	}
	applyMapFilters(false);

	const selected = config.selectedUmkm;
	if (selected && markerLookup[selected.id]) {
		map.setView([selected.latitude, selected.longitude], 18);
		const selectedMarker = markerLookup[selected.id];
		selectedMarker.openPopup();

		selectedMarker.setIcon(createUmkmMarkerIcon(true));

		setTimeout(() => selectedMarker.setIcon(createUmkmMarkerIcon(false)), 1800);
	} else if (locations.length > 0) {
		map.fitBounds(locations, { padding: [30, 30] });
	}

	setTimeout(() => map.invalidateSize(), 300);
}

document.addEventListener('DOMContentLoaded', () => {
	const config = readMapConfig();
	if (!config) return;

// Ambil semua item di dalam dropdown kita
    const dropdownItems = document.querySelectorAll('.dropdown-item');

    dropdownItems.forEach(item => {
        item.addEventListener('click', function() {
            // 1. Ambil data target (ID asli Anda) dan nilainya
            const targetId = this.getAttribute('data-target'); // cth: mobileMinRating
            const selectedValue = this.getAttribute('data-value');
            const selectedText = this.innerHTML;

            // 2. Update teks pada tombol dropdown agar pengguna tahu apa yang dipilih
            document.getElementById('text-' + targetId).innerHTML = selectedText;

            // 3. Masukkan nilai tersebut ke hidden input ID asli Anda
            const hiddenInput = document.getElementById(targetId);
            hiddenInput.value = selectedValue;

            // 4. SANGAT PENTING: Picu event 'change' secara manual.
            // Ini membuat script JS filter peta Anda (yang mendengarkan 'change' event) otomatis berjalan!
            hiddenInput.dispatchEvent(new Event('change'));

            // 5. Rapikan tampilan (pindahkan highlight abu-abu 'active' ke item yang baru diklik)
            const parentMenu = this.closest('.dropdown-menu');
            parentMenu.querySelectorAll('.dropdown-item').forEach(el => el.classList.remove('active'));
            this.classList.add('active');
        });
    });

	window.mapPageConfig = config;
	window.closeDetailPanel = closeDetailPanel;
	window.toggleUlasan = toggleUlasan;
	window.openRatingModal = openRatingModal;
	window.showUmkmDetail = showUmkmDetail;
	window.startLiveTrackingTo = startLiveTrackingTo;
	window.stopLiveTracking = stopLiveTracking;

	initMapFeature(config);
	initRatingFeature();
});
