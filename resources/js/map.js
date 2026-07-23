let map;
let ratingModal = null;
let menuSubmissionModal = null;
let imageLightboxModal = null;
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
let activeBottomSheetCleanup = null;
let imageLightboxZoom = 1;
let imageLightboxMode = "desktop";
let imageLightboxGestureCleanup = null;
let imageLightboxTranslateX = 0;
let imageLightboxTranslateY = 0;
let markerClusterLayer = null;
let mapDataAbortController = null;
let loadVisibleUmkmsDebounced = null;
let pendingSelectedUmkmId = null;
let mapInteractionResetTimer = null;
const locationConsentKey = "map_live_tracking_location_consent";
const filterState = {
    searchQuery: "",
    category: "all",
    group: "all",
    minRating: 0,
    openNow: false,
};

function isMobileViewport() {
    return window.matchMedia("(max-width: 768px)").matches;
}

function setTrackingControlsVisible(isVisible) {
    const wrapper = document.getElementById("globalStopTrackingWrapper");
    if (!wrapper) return;

    wrapper.classList.toggle("d-none", !isVisible);

    const floatingStatus = document.getElementById("liveTrackFloatingStatus");
    if (floatingStatus) {
        floatingStatus.classList.toggle("d-none", !isVisible);
    }
}

function createUmkmMarkerIcon(isHighlighted = false) {
    const iconSize = [36, 36];
    const iconAnchor = [18, 34];
    return L.divIcon({
        html: `<div class="umkm-marker ${isHighlighted ? "is-highlighted" : ""}"><i class="fas fa-utensils"></i></div>`,
        className: "custom-marker umkm-marker-wrapper",
        iconSize,
        iconAnchor,
        popupAnchor: [0, -28],
    });
}

function createUserMarkerIcon() {
    return L.divIcon({
        html: '<div class="user-live-marker"><span class="pulse-ring"></span><span class="pulse-dot"><i class="fas fa-location-arrow"></i></span></div>',
        className: "custom-marker user-marker-wrapper",
        iconSize: [30, 30],
        iconAnchor: [15, 15],
    });
}

function debounce(fn, delay = 300) {
    let timerId = null;
    return (...args) => {
        window.clearTimeout(timerId);
        timerId = window.setTimeout(() => fn(...args), delay);
    };
}

function clearLoadedUmkmState() {
    Object.keys(umkmData).forEach((key) => {
        delete umkmData[key];
    });

    Object.keys(markerLookup).forEach((key) => {
        delete markerLookup[key];
    });

    markerClusterLayer?.clearLayers();
}

function setMapControlsCompact(isCompact) {
    const mapControlsEl = document.getElementById("mapControls");
    if (!mapControlsEl) return;

    mapControlsEl.classList.toggle("is-compact", Boolean(isCompact));
    if (isCompact) {
        desktopFilterPanel?.classList.add("d-none");
        searchFilterDropdown?.classList.add("d-none");
        syncSearchFilterToggleState(false);
    }
}

function collapseMapInteractions() {
    setMapControlsCompact(true);
    closeDetailPanel();

    window.clearTimeout(mapInteractionResetTimer);
    mapInteractionResetTimer = window.setTimeout(() => {
        mapInteractionResetTimer = null;
    }, 150);
}

function openPopupForMarker(marker, item) {
    if (!marker || !item || !map) return;

    const popup = L.popup({ maxWidth: 250, minWidth: 200, closeButton: true })
        .setLatLng(marker.getLatLng())
        .setContent(createPopupElement(item));

    popup.openOn(map);
}

function buildMapBoundsQuery() {
    if (!map) return null;

    const bounds = map.getBounds();
    if (!bounds || !bounds.isValid()) return null;

    const params = new URLSearchParams({
        north: bounds.getNorth().toString(),
        south: bounds.getSouth().toString(),
        east: bounds.getEast().toString(),
        west: bounds.getWest().toString(),
    });

    return params;
}

function renderMapUmkms(items) {
    if (!Array.isArray(items)) return;

    clearLoadedUmkmState();

    items.forEach((item) => {
        const data = {
            ...item,
            kategori_key: toCategoryKey(item.kategori),
            kelompok_key: toCategoryKey(item.kelompok),
        };

        umkmData[item.id] = data;

        const marker = L.marker([item.latitude, item.longitude], {
            icon: createUmkmMarkerIcon(Boolean(item.is_recommended)),
            zIndexOffset: 600,
            bubblingMouseEvents: false,
        });

        marker.on("click", () => openPopupForMarker(marker, data));
        markerLookup[item.id] = marker;
        markerClusterLayer?.addLayer(marker);
    });

    renderCategoryChips();
    renderGroupFilters();
    syncFilterControls();
    applyMapFilters(false);

    if (pendingSelectedUmkmId && markerLookup[pendingSelectedUmkmId]) {
        const selectedMarker = markerLookup[pendingSelectedUmkmId];
        const selectedData = umkmData[pendingSelectedUmkmId];
        openPopupForMarker(selectedMarker, selectedData);
        selectedMarker.setIcon(createUmkmMarkerIcon(true));

        setTimeout(() => {
            const isRecommended = Boolean(
                umkmData[pendingSelectedUmkmId]?.is_recommended,
            );
            selectedMarker.setIcon(createUmkmMarkerIcon(isRecommended));
        }, 1800);
    }
}

async function loadVisibleUmkms() {
    if (!window.mapPageConfig?.mapDataUrl || !map) return;

    const params = buildMapBoundsQuery();
    if (!params) return;

    if (mapDataAbortController) {
        mapDataAbortController.abort();
    }

    const controller = new AbortController();
    mapDataAbortController = controller;

    try {
        const response = await fetch(
            `${window.mapPageConfig.mapDataUrl}?${params.toString()}`,
            {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
                signal: controller.signal,
            },
        );

        if (!response.ok) {
            throw new Error(`Failed to load UMKM data (${response.status})`);
        }

        const payload = await response.json();
        renderMapUmkms(payload.data || []);
    } catch (error) {
        if (error.name !== "AbortError") {
            console.error("Failed to load visible UMKM data:", error);
        }
    } finally {
        if (mapDataAbortController === controller) {
            mapDataAbortController = null;
        }
    }
}

function hasStoredLocationConsent() {
    try {
        return window.localStorage.getItem(locationConsentKey) === "granted";
    } catch (error) {
        return false;
    }
}

function storeLocationConsent() {
    try {
        window.localStorage.setItem(locationConsentKey, "granted");
    } catch (error) {
        // no-op if storage unavailable
    }
}

function readMapConfig() {
    const configEl = document.getElementById("mapPageConfig");
    if (!configEl) {
        return null;
    }

    try {
        return JSON.parse(configEl.textContent || "{}");
    } catch (error) {
        console.error("Invalid map page config:", error);
        return null;
    }
}

function getUmkmDetailUrl(umkmId) {
    if (!umkmId) return null;
    const template = window.mapPageConfig?.umkmDetailUrlTemplate;
    if (template) {
        return template.replace("__UMKM__", encodeURIComponent(String(umkmId)));
    }

    return `/umkm/${encodeURIComponent(String(umkmId))}/detail`;
}

function getUmkmTrackUrl(umkmId) {
    if (!umkmId) return null;
    const template = window.mapPageConfig?.umkmTrackUrlTemplate;
    if (template) {
        return template.replace("__UMKM__", encodeURIComponent(String(umkmId)));
    }

    return `/umkm/${encodeURIComponent(String(umkmId))}/track-activity`;
}

function trackUmkmActivity(umkmId) {
    const url = getUmkmTrackUrl(umkmId);
    if (!url) return;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");
    fetch(url, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": csrfToken || "",
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        },
    }).catch(() => {
        // no-op: activity tracking should not block UI
    });
}

function trackUmkmClick(umkmId) {
    const url = getUmkmDetailUrl(umkmId);
    if (!url) return;

    fetch(url, {
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        },
    }).catch(() => {
        // no-op: click tracking should not block UI
    });

    trackUmkmActivity(umkmId);
}

function normalizeText(text) {
    return String(text || "")
        .toLowerCase()
        .trim();
}

function toCategoryKey(categoryName) {
    return normalizeText(categoryName).replace(/\s+/g, "-");
}

function parseJamToMinutes(value) {
    const cleaned = String(value || "").replace(".", ":");
    const parts = cleaned.split(":");
    const hour = parseInt(parts[0], 10);
    const minute = parts[1] ? parseInt(parts[1], 10) : 0;

    if (Number.isNaN(hour) || Number.isNaN(minute)) {
        return null;
    }

    return hour * 60 + minute;
}

function isUmkmOpenNow(jamBukaText) {
    const jamText = normalizeText(jamBukaText);
    if (!jamText) return false;
    if (jamText.includes("24 jam")) return true;

    const match = jamText.match(
        /(\d{1,2}[.:]?\d{0,2})\s*[-–]\s*(\d{1,2}[.:]?\d{0,2})/,
    );
    if (!match) return false;

    const startMinutes = parseJamToMinutes(match[1]);
    const endMinutes = parseJamToMinutes(match[2]);
    if (startMinutes === null || endMinutes === null) return false;

    const now = new Date();
    const nowMinutes = now.getHours() * 60 + now.getMinutes();

    if (startMinutes <= endMinutes) {
        return nowMinutes >= startMinutes && nowMinutes <= endMinutes;
    }

    return nowMinutes >= startMinutes || nowMinutes <= endMinutes;
}

function generateStars(rating) {
    let starsHtml = "";
    for (let i = 1; i <= 5; i += 1) {
        if (i <= Math.floor(rating)) {
            starsHtml += '<i class="fas fa-star text-warning" style="-webkit-text-stroke: 1px #000;"></i>';
        } else if (i - 0.5 <= rating) {
            starsHtml += '<i class="fas fa-star-half-alt text-warning" style="-webkit-text-stroke: 1px #000;"></i>';
        } else {
            starsHtml += '<i class="far fa-star text-warning" style="-webkit-text-stroke: 1px #000;"></i>';
        }
    }
    return starsHtml;
}

function formatMenuPrice(value) {
    const price = Number(value);
    if (!Number.isFinite(price) || price <= 0) {
        return "- (harga belum dimasukan)";
    }

    return `Rp ${new Intl.NumberFormat("id-ID").format(price)}`;
}

function resetLightboxTransform(imageEl, resetButton = null) {
    imageLightboxZoom = 1;
    imageLightboxTranslateX = 0;
    imageLightboxTranslateY = 0;
    if (imageEl) {
        imageEl.style.transform = "translate3d(0px, 0px, 0) scale(1)";
        imageEl.style.cursor = "zoom-in";
    }
    if (resetButton) {
        resetButton.textContent = "Reset";
    }
}

function updateLightboxTransform(imageEl, resetButton = null) {
    if (!imageEl) return;

    imageEl.style.transform = `translate3d(${imageLightboxTranslateX}px, ${imageLightboxTranslateY}px, 0) scale(${imageLightboxZoom})`;
    imageEl.style.cursor = imageLightboxZoom > 1 ? "grab" : "zoom-in";
    if (resetButton) {
        resetButton.textContent =
            imageLightboxZoom === 1
                ? "Reset"
                : `${Math.round(imageLightboxZoom * 100)}%`;
    }
}

function clampLightboxTranslation(imageEl, nextX, nextY) {
    if (!imageEl) {
        return { x: nextX, y: nextY };
    }

    const previewBox = imageEl.parentElement;
    const stage = previewBox?.parentElement;
    if (!previewBox || !stage) {
        return { x: nextX, y: nextY };
    }

    const previewRect = previewBox.getBoundingClientRect();
    const stageRect = stage.getBoundingClientRect();
    const maxX = Math.max(
        0,
        (previewRect.width * imageLightboxZoom - stageRect.width) / 2,
    );
    const maxY = Math.max(
        0,
        (previewRect.height * imageLightboxZoom - stageRect.height) / 2,
    );

    return {
        x: Math.min(Math.max(nextX, -maxX), maxX),
        y: Math.min(Math.max(nextY, -maxY), maxY),
    };
}

function formatReviewDate(rawDate) {
    if (!rawDate) return "-";
    const date = new Date(rawDate);
    if (Number.isNaN(date.getTime())) return rawDate;

    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

function buildUlasanElement(ulasanList) {
    const wrapper = document.createElement("div");

    if (!Array.isArray(ulasanList) || ulasanList.length === 0) {
        const emptyText = document.createElement("p");
        emptyText.className = "mb-0 text-muted";
        emptyText.textContent = "Belum ada ulasan untuk UMKM ini.";
        wrapper.appendChild(emptyText);
        return wrapper;
    }

    ulasanList.forEach((ulasan) => {
        const item = document.createElement("div");
        item.className = "ulasan-item";

        const header = document.createElement("div");
        header.className =
            "d-flex justify-content-between align-items-center mb-1";

        const reviewer = document.createElement("strong");
        reviewer.textContent = ulasan.nama_pengulas || "Anonymous";

        const date = document.createElement("small");
        date.className = "text-muted";
        date.textContent = formatReviewDate(ulasan.tanggal);

        header.appendChild(reviewer);
        header.appendChild(date);

        const stars = document.createElement("div");
        stars.className = "stars mb-1";
        stars.innerHTML = generateStars(parseInt(ulasan.nilai_rating || 0, 10));

        const comment = document.createElement("p");
        comment.className = "mb-0";
        comment.textContent =
            ulasan.komentar || "Pengguna tidak menulis ulasan.";

        item.appendChild(header);
        item.appendChild(stars);
        item.appendChild(comment);
        wrapper.appendChild(item);
    });

    return wrapper;
}

function closeDetailPanel() {
    if (typeof activeBottomSheetCleanup === "function") {
        activeBottomSheetCleanup();
        activeBottomSheetCleanup = null;
    }

    const panel = document.getElementById("umkm-detail-panel");
    if (!panel) return;

    const isMobileSheet =
        panel.classList.contains("umkm-bottom-sheet") && isMobileViewport();
    panel.style.animation = isMobileSheet
        ? "slideOutDown 0.24s ease-in"
        : "slideOutRight 0.3s ease-in";
    setTimeout(() => panel.remove(), isMobileSheet ? 240 : 300);
}

function syncSearchFilterToggleState(isActive) {
    const toggleBtn = document.getElementById("toggleSearchFiltersBtn");
    if (!toggleBtn) return;

    toggleBtn.classList.toggle("is-active", Boolean(isActive));
    toggleBtn.setAttribute("aria-pressed", Boolean(isActive).toString());
    toggleBtn.setAttribute("aria-expanded", Boolean(isActive).toString());
}

function initCategoryChipsInteraction() {
    const chipsContainer = document.getElementById("categoryChips");
    if (!chipsContainer) return;

    chipsContainer.style.overflowX = "auto";
    chipsContainer.style.overflowY = "hidden";
    chipsContainer.style.touchAction = "pan-x";
    chipsContainer.style.pointerEvents = "auto";
    chipsContainer.style.cursor = "grab";
    chipsContainer.style.webkitUserSelect = "none";

    const stopMapGesturePropagation = (event) => {
        event.stopPropagation();
    };

    let dragState = {
        pointerId: null,
        startX: 0,
        startScrollLeft: 0,
        dragging: false,
        suppressClickUntil: 0,
    };

    const wheelToHorizontal = (event) => {
        if (Math.abs(event.deltaY) <= Math.abs(event.deltaX)) return;
        chipsContainer.scrollLeft += event.deltaY;
        event.preventDefault();
    };

    const beginDrag = (event) => {
        if (!chipsContainer.contains(event.target)) return;
        dragState.pointerId = event.pointerId;
        dragState.startX = event.clientX;
        dragState.startScrollLeft = chipsContainer.scrollLeft;
        dragState.dragging = false;
        chipsContainer.classList.remove("is-dragging");
        chipsContainer.style.cursor = "grab";
    };

    const moveDrag = (event) => {
        if (dragState.pointerId !== event.pointerId) return;

        const deltaX = event.clientX - dragState.startX;
        if (!dragState.dragging && Math.abs(deltaX) < 6) return;

        if (!dragState.dragging) {
            dragState.dragging = true;
            chipsContainer.classList.add("is-dragging");
            chipsContainer.setPointerCapture?.(dragState.pointerId);
        }

        chipsContainer.scrollLeft = dragState.startScrollLeft - deltaX;
        event.preventDefault();
    };

    const endDrag = (event) => {
        if (dragState.pointerId !== event.pointerId) return;

        if (dragState.dragging) {
            dragState.suppressClickUntil = Date.now() + 220;
        }

        dragState.pointerId = null;
        dragState.dragging = false;
        chipsContainer.classList.remove("is-dragging");
        chipsContainer.style.cursor = "grab";
    };

    const blockClickAfterDrag = (event) => {
        if (Date.now() <= dragState.suppressClickUntil) {
            event.preventDefault();
            event.stopPropagation();
            dragState.suppressClickUntil = 0;
        }
    };

    if (!chipsContainer.dataset.scrollGuardBound) {
        chipsContainer.addEventListener(
            "pointerdown",
            stopMapGesturePropagation,
            { passive: true },
        );
        chipsContainer.addEventListener(
            "pointermove",
            stopMapGesturePropagation,
            { passive: true },
        );
        chipsContainer.addEventListener(
            "touchstart",
            stopMapGesturePropagation,
            { passive: true },
        );
        chipsContainer.addEventListener(
            "touchmove",
            stopMapGesturePropagation,
            { passive: true },
        );
        chipsContainer.addEventListener("wheel", wheelToHorizontal, {
            passive: false,
        });
        chipsContainer.addEventListener("pointerdown", beginDrag, {
            passive: true,
        });
        chipsContainer.addEventListener("pointermove", moveDrag, {
            passive: false,
        });
        chipsContainer.addEventListener("pointerup", endDrag, {
            passive: true,
        });
        chipsContainer.addEventListener("pointercancel", endDrag, {
            passive: true,
        });
        chipsContainer.addEventListener("click", blockClickAfterDrag, true);
        chipsContainer.dataset.scrollGuardBound = "true";
    }

    requestAnimationFrame(() => {
        chipsContainer.scrollLeft = 0;
    });
}

// function initMobileDetailBottomSheet(panel) {
//     if (!panel || typeof isMobileViewport !== 'function' || !isMobileViewport()) return null;

//     const header = panel.querySelector(".detail-header");
//     const content = panel.querySelector(".detail-content");
//     if (!header || !content) return null;

//     panel.classList.add("umkm-bottom-sheet", "is-expanded");
//     panel.classList.remove("is-collapsed");

//     // Pastikan handle ada
//     let sheetHandle = panel.querySelector(".detail-sheet-handle");
//     if (!sheetHandle) {
//         sheetHandle = document.createElement("div");
//         sheetHandle.className = "detail-sheet-handle";
//         panel.insertBefore(sheetHandle, header);
//     }

//     let startY = 0;
//     let startHeight = 0;
//     let currentHeight = 0;
//     let dragging = false;

//     // Hitung tinggi header + handle sebagai batas bawah (collapsed)
//     const getCollapsedHeight = () => {
//         const handleHeight = sheetHandle.getBoundingClientRect().height || 0;
//         const headerHeight = header.getBoundingClientRect().height || 0;
//         // Tambahkan sedikit padding ekstra (misal 10px) agar rapi
//         return Math.ceil(handleHeight + headerHeight + 10);
//     };

//     const getExpandedHeight = () => {
//         // Maksimal tinggi adalah tinggi layar dikurangi margin atas (misal 12px)
//         return Math.floor(window.innerHeight - 12);
//     };

//     const clampHeight = (value) => {
//         const min = getCollapsedHeight();
//         const max = getExpandedHeight();
//         return Math.min(Math.max(value, min), max);
//     };

//     const applyHeight = (value, withTransition = false) => {
//         currentHeight = clampHeight(value);
//         panel.style.height = `${currentHeight}px`;

//         // Update class berdasarkan threshold (toleransi 5px)
//         const isCollapsed = currentHeight <= getCollapsedHeight() + 5;
//         panel.classList.toggle("is-collapsed", isCollapsed);
//         panel.classList.toggle("is-expanded", !isCollapsed);
//         panel.classList.toggle("is-snapping", withTransition);
//     };

//     const snapSheet = () => {
//         const collapsed = getCollapsedHeight();
//         const expanded = getExpandedHeight();
//         // Threshold: jika ditarik lebih dari 25% ke bawah, maka tutup. Jika tidak, buka penuh.
//         const threshold = collapsed + (expanded - collapsed) * 0.25;
//         const target = currentHeight <= threshold ? collapsed : expanded;
//         applyHeight(target, true);
//     };

//     const onPointerDown = (event) => {
//         if (event.pointerType === "mouse" && event.button !== 0) return;

//         // PERBAIKAN: Jangan drag jika yang diklik adalah tombol close
//         if (event.target.closest(".custom-btn-close")) return;

//         // PERBAIKAN: Izinkan drag dari area Handle ATAU Header agar lebih mudah di sentuh HP
//         if (!event.target.closest(".detail-sheet-handle") && !event.target.closest(".detail-header")) return;

//         dragging = true;
//         startY = event.clientY;
//         startHeight = currentHeight || panel.getBoundingClientRect().height;

//         panel.classList.add("is-dragging");
//         panel.classList.remove("is-snapping");

//         // Set pointer capture ke panel agar drag tidak lepas saat digeser cepat
//         if (typeof panel.setPointerCapture === "function") {
//             panel.setPointerCapture(event.pointerId);
//         }

//         // Mencegah scroll halaman di background
//         event.preventDefault();
//     };

//     const onPointerMove = (event) => {
//         if (!dragging) return;
//         // Mencegah scroll default pada browser
//         event.preventDefault();

//         const deltaY = startY - event.clientY;
//         applyHeight(startHeight + deltaY);
//     };

//     const onPointerUp = (event) => {
//         if (!dragging) return;
//         dragging = false;
//         panel.classList.remove("is-dragging");

//         if (typeof panel.releasePointerCapture === "function") {
//             panel.releasePointerCapture(event.pointerId);
//         }

//         snapSheet();
//     };

//     const onResize = () => {
//         if (typeof isMobileViewport === 'function' && !isMobileViewport()) {
//             panel.classList.remove("umkm-bottom-sheet", "is-collapsed", "is-expanded", "is-dragging", "is-snapping");
//             panel.style.height = "";
//             return;
//         }
//         applyHeight(panel.classList.contains("is-collapsed") ? getCollapsedHeight() : getExpandedHeight());
//     };

//     // Event listener dipasang ke panel agar area tangkapan lebih luas
//     panel.addEventListener("pointerdown", onPointerDown, { passive: false });
//     window.addEventListener("pointermove", onPointerMove, { passive: false });
//     window.addEventListener("pointerup", onPointerUp);
//     window.addEventListener("resize", onResize);

//     // Set tinggi awal
//     setTimeout(() => {
//         applyHeight(getExpandedHeight(), true);
//     }, 50);

//     return () => {
//         panel.removeEventListener("pointerdown", onPointerDown);
//         window.removeEventListener("pointermove", onPointerMove);
//         window.removeEventListener("pointerup", onPointerUp);
//         window.removeEventListener("resize", onResize);
//     };
// }

function prepareDetailPanel(panel) {
    if (!panel) return;

    if (typeof activeBottomSheetCleanup === "function") {
        activeBottomSheetCleanup();
        activeBottomSheetCleanup = null;
    }

    // activeBottomSheetCleanup = initMobileDetailBottomSheet(panel);
}

function toggleUlasan(containerId, triggerBtn = null) {
    const ulasanContainer = document.getElementById(containerId);
    if (!ulasanContainer) return;

    ulasanContainer.classList.toggle("d-none");
    const isHidden = ulasanContainer.classList.contains("d-none");
    if (triggerBtn) {
        triggerBtn.innerHTML = isHidden
            ? '<i class="fas fa-comments me-1"></i>Lihat ulasan'
            : '<i class="fas fa-comments me-1"></i>Sembunyikan ulasan';
    }
}

function setResultInfo(visibleCount) {
    const info = document.getElementById("mapResultInfo");
    if (!info) return;

    const totalCount = Object.keys(umkmData).length;
    if (visibleCount === totalCount) {
        info.textContent = `Menampilkan (${totalCount}) UMKM`;
    } else {
        info.textContent = `Menampilkan ${visibleCount} dari ${totalCount} UMKM`;
    }
}

function renderCategoryChips() {
    const chipsContainer = document.getElementById("categoryChips");
    if (!chipsContainer) return;

    const categories = Array.from(
        new Set(
            Object.values(umkmData)
                .map((item) => item.kategori)
                .filter(Boolean),
        ),
    ).sort((a, b) => a.localeCompare(b, "id-ID"));

    chipsContainer.innerHTML = "";

    const allChip = document.createElement("button");
    allChip.type = "button";
    allChip.className = "category-chip active";
    allChip.dataset.category = "all";
    allChip.textContent = "Semua";
    chipsContainer.appendChild(allChip);

    categories.forEach((categoryName) => {
        const chip = document.createElement("button");
        chip.type = "button";
        chip.className = "category-chip";
        chip.dataset.category = toCategoryKey(categoryName);
        chip.textContent = categoryName;
        chipsContainer.appendChild(chip);
    });

    chipsContainer.querySelectorAll(".category-chip").forEach((chip) => {
        chip.addEventListener("click", function clickChip() {
            chipsContainer
                .querySelectorAll(".category-chip")
                .forEach((c) => c.classList.remove("active"));
            this.classList.add("active");
            filterState.category = this.dataset.category;
            applyMapFilters(false);
        });
    });
}

function renderGroupFilters() {
    const desktopGroup = document.getElementById("desktopGroupFilter");
    const mobileGroup = document.getElementById("mobileGroupFilter");

    if (!desktopGroup && !mobileGroup) return;

    const groups = Array.from(
        new Set(
            Object.values(umkmData)
                .map((item) => item.kelompok)
                .filter(Boolean),
        ),
    ).sort((a, b) => a.localeCompare(b, "id-ID"));

    const buildSelectOptions = (selectEl) => {
        if (!selectEl || selectEl.tagName.toLowerCase() !== "select") return;

        selectEl.innerHTML = '<option value="all">Semua Kelompok</option>';
        groups.forEach((groupName) => {
            const option = document.createElement("option");
            option.value = toCategoryKey(groupName);
            option.textContent = groupName;
            selectEl.appendChild(option);
        });
    };

    buildSelectOptions(desktopGroup);
    buildSelectOptions(mobileGroup);
}

function syncFilterControls() {
    const desktopMinRating = document.getElementById("desktopMinRating");
    const mobileMinRating = document.getElementById("mobileMinRating");
    const desktopOpenNow = document.getElementById("desktopOpenNow");
    const mobileOpenNow = document.getElementById("mobileOpenNow");
    const desktopGroup = document.getElementById("desktopGroupFilter");
    const mobileGroup = document.getElementById("mobileGroupFilter");

    if (desktopMinRating)
        desktopMinRating.value = String(filterState.minRating);
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

        const searchTarget = normalizeText(
            `${item.nama_umkm} ${item.alamat_lengkap} ${item.kategori}`,
        );
        const matchesSearch =
            !filterState.searchQuery ||
            searchTarget.includes(filterState.searchQuery);
        const matchesCategory =
            filterState.category === "all" ||
            item.kategori_key === filterState.category;
        const matchesGroup =
            filterState.group === "all" ||
            item.kelompok_key === filterState.group;
        const matchesRating =
            Number(item.rating_avg || 0) >= Number(filterState.minRating || 0);
        const matchesOpenNow =
            !filterState.openNow || isUmkmOpenNow(item.jam_buka);
        const isVisible =
            matchesSearch &&
            matchesCategory &&
            matchesGroup &&
            matchesRating &&
            matchesOpenNow;

        if (isVisible) {
            if (markerClusterLayer && !markerClusterLayer.hasLayer(marker)) {
                markerClusterLayer.addLayer(marker);
            }
            visibleLatLngs.push(marker.getLatLng());
            visibleCount += 1;
        } else if (markerClusterLayer && markerClusterLayer.hasLayer(marker)) {
            marker.closePopup();
            markerClusterLayer.removeLayer(marker);
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
    const wrapper = document.createElement("div");
    wrapper.className = "marker-popup";

    const title = document.createElement("h6");
    title.className = "mb-1 text-truncate"; // Text-truncate agar tidak terlalu panjang
    title.textContent = item.nama_umkm;

    const address = document.createElement("p");
    address.textContent = item.alamat_lengkap || "-";

    const button = document.createElement("button");
    button.type = "button";
    // Menerapkan gaya tombol Neo-Brutalism secara langsung
    button.className = "btn w-100";
    button.style.backgroundColor = "#5ad641"; // Hijau neo
    button.style.color = "#000";
    button.style.border = "3px solid #000";
    button.style.borderRadius = "0";
    button.style.fontWeight = "900";
    button.style.textTransform = "uppercase";
    button.style.boxShadow = "3px 3px 0 #000";
    button.style.transition = "all 0.1s";

    button.innerHTML = '<i class="fas fa-arrow-right me-1"></i> Lihat Detail';

    // Efek saat tombol ditekan (active) dan disorot (hover)
    button.addEventListener("mousedown", () => {
        button.style.transform = "translate(2px, 2px)";
        button.style.boxShadow = "1px 1px 0 #000";
    });
    button.addEventListener("mouseup", () => {
        button.style.transform = "translate(0, 0)";
        button.style.boxShadow = "3px 3px 0 #000";
    });
    button.addEventListener("mouseleave", () => {
        button.style.transform = "translate(0, 0)";
        button.style.boxShadow = "3px 3px 0 #000";
    });

    button.addEventListener("click", () => {
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

    trackUmkmClick(umkmId);
    closeDetailPanel();

    const panel = document.createElement("div");
    panel.id = "umkm-detail-panel";
    // PERUBAHAN: Menambahkan class neo-detail-panel
    panel.className = "umkm-detail-panel neo-detail-panel";

    const header = document.createElement("div");
    // PERUBAHAN: Menambahkan class neo-detail-header
    header.className = "detail-header neo-detail-header";

    const title = document.createElement("h4");
    // PERUBAHAN: Teks tebal dan uppercase
    title.className = "mb-0 fw-bold text-uppercase";
    title.style.fontWeight = "900";
    title.textContent = data.nama_umkm;

    const closeBtn = document.createElement("button");
    closeBtn.type = "button";
    // PERUBAHAN: Gaya tombol close diubah ke neo
    closeBtn.className = "neo-btn-close-panel"; // Hanya gunakan class CSS buatan Anda
    closeBtn.innerHTML = '<i class="fas fa-times fa-lg"></i>';
    closeBtn.addEventListener("click", closeDetailPanel);

    header.appendChild(title);
    header.appendChild(closeBtn);

    const content = document.createElement("div");
    content.className = "detail-content";

    const scrollWrapper = document.createElement("div");
    scrollWrapper.className = "detail-content-scrollable";
    scrollWrapper.style.flex = "1";
    scrollWrapper.style.overflowY = "auto";

    while (content.firstChild) {
        scrollWrapper.appendChild(content.firstChild);
    }
    content.appendChild(scrollWrapper);

    const photoSection = document.createElement("div");
    // PERUBAHAN: Menambahkan neo-detail-section
    photoSection.className = "detail-section neo-detail-section";
    const photoEl = document.createElement("img");
    // PERUBAHAN: Menambahkan neo-detail-photo
    photoEl.className = "neo-detail-photo lightbox-trigger";
    photoEl.src = data.foto_umkm_url || "/images/default-umkm.svg";
    photoEl.alt = `Foto ${data.nama_umkm}`;
    photoEl.style.cursor = "zoom-in";
    photoEl.addEventListener("click", () => {
        openImageLightbox(photoEl.src, `Foto ${data.nama_umkm}`);
    });
    photoEl.onerror = function onImageError() {
        this.onerror = null;
        this.src = "/images/default-umkm.svg";
    };
    photoSection.appendChild(photoEl);
    content.appendChild(photoSection);

    // PERUBAHAN: Menyesuaikan helper makeSection agar menerapkan gaya neo
    const makeSection = (icon, label, node) => {
        const section = document.createElement("div");
        section.className = "detail-section neo-detail-section";
        const h6 = document.createElement("h6");
        h6.className = "fw-bold text-uppercase";
        h6.innerHTML = `<i class="fas ${icon} me-2"></i>${label}`;
        section.appendChild(h6);
        section.appendChild(node);
        return section;
    };

    const kategoriBadge = document.createElement("span");
    // PERUBAHAN: Menerapkan neo-badge
    kategoriBadge.className = "badge neo-badge";
    kategoriBadge.textContent = data.kategori || "-";
    content.appendChild(makeSection("fa-tag", "Kategori", kategoriBadge));

    const jamText = document.createElement("p");
    jamText.className = "mb-0 fw-semibold";
    jamText.textContent = data.jam_buka || "-";
    content.appendChild(makeSection("fa-clock", "Jam Buka", jamText));

    const alamatText = document.createElement("p");
    alamatText.className = "mb-0 fw-semibold";
    alamatText.textContent = data.alamat_lengkap || "-";
    content.appendChild(
        makeSection("fa-map-marker-alt", "Alamat Lengkap", alamatText),
    );

    const phoneText = document.createElement("p");
    phoneText.className = "mb-0 fw-semibold";
    if (data.no_telfon) {
        const phoneLink = document.createElement("a");
        phoneLink.href = `tel:${String(data.no_telfon).replace(/\s+/g, "")}`;
        phoneLink.className = "text-dark";
        phoneLink.textContent = data.no_telfon;
        phoneText.appendChild(phoneLink);
    } else {
        phoneText.textContent = "-";
    }
    content.appendChild(makeSection("fa-phone", "No telfon", phoneText));

    const ratingWrap = document.createElement("div");
    const ratingContainer = document.createElement("div");
    ratingContainer.className = "d-flex align-items-center";
    const starsDiv = document.createElement("div");
    starsDiv.className = "stars me-2";
    starsDiv.innerHTML = generateStars(data.rating_avg || 0);
    const ratingText = document.createElement("small");
    // PERUBAHAN: Teks rating ditebalkan dan dihitamkan
    ratingText.className = "text-dark fw-bold";
    ratingText.textContent = `(${Number(data.rating_avg || 0).toFixed(1)} • ${Number(data.rating_count || 0)} ulasan)`;
    ratingContainer.appendChild(starsDiv);
    ratingContainer.appendChild(ratingText);

    const ulasanBtn = document.createElement("button");
    ulasanBtn.type = "button";
    ulasanBtn.className = "btn btn-link btn-sm p-0 mt-2 text-dark fw-bold";
    ulasanBtn.innerHTML = '<i class="fas fa-comments me-1"></i>Lihat ulasan';

    const ulasanContainer = document.createElement("div");
    ulasanContainer.id = `ulasan-list-${data.id}`;
    ulasanContainer.className = "ulasan-list-container d-none mt-2";
    ulasanContainer.appendChild(buildUlasanElement(data.ulasan));
    ulasanBtn.addEventListener("click", function onToggle() {
        toggleUlasan(ulasanContainer.id, this);
    });

    ratingWrap.appendChild(ratingContainer);
    ratingWrap.appendChild(ulasanBtn);
    ratingWrap.appendChild(ulasanContainer);
    content.appendChild(makeSection("fa-star", "Rating", ratingWrap));

    if (data.deskripsi) {
        const desc = document.createElement("p");
        desc.className = "mb-0 fw-semibold";
        desc.textContent = data.deskripsi;
        content.appendChild(makeSection("fa-info-circle", "Deskripsi", desc));
    }

    const menuWrap = document.createElement("div");
    const menuItems = Array.isArray(data.menu)
        ? data.menu.filter((menuItem) => !menuItem.is_daftar_foto)
        : [];
    const menuGalleryItems = Array.isArray(data.menu)
        ? data.menu.filter(
              (menuItem) => menuItem.is_daftar_foto && menuItem.foto_menu_url,
          )
        : [];

    if (menuItems.length > 0) {
        const menuList = document.createElement("div");
        menuList.className = "menu-list d-grid gap-2";

        menuItems.forEach((menuItem) => {
            const menuRow = document.createElement("div");
            // PERUBAHAN: Menambahkan border tebal hitam di setiap menu
            menuRow.className = "menu-item d-flex align-items-center gap-2 border-dark border-2";
            menuRow.style.backgroundColor = "#fff";

            const menuImage = document.createElement("img");
            menuImage.className = "menu-thumb border-dark border-2";
            menuImage.src =
                menuItem.foto_menu_url || "/images/default-menu.svg";
            menuImage.alt = `Foto ${menuItem.nama_menu}`;
            menuImage.style.cursor = "zoom-in";
            menuImage.addEventListener("click", () => {
                openImageLightbox(
                    menuImage.src,
                    `Foto ${menuItem.nama_menu || "Menu"}`,
                );
            });
            menuImage.onerror = function onMenuImageError() {
                this.onerror = null;
                this.src = "/images/default-menu.svg";
            };

            const menuInfo = document.createElement("div");
            menuInfo.className = "flex-grow-1";
            const menuName = document.createElement("div");
            menuName.className = "fw-bold text-dark";
            menuName.textContent = menuItem.nama_menu || "-";
            const menuPrice = document.createElement("small");
            menuPrice.className = "text-dark fw-bold";
            menuPrice.textContent = formatMenuPrice(menuItem.harga_menu);

            menuInfo.appendChild(menuName);
            menuInfo.appendChild(menuPrice);
            menuRow.appendChild(menuImage);
            menuRow.appendChild(menuInfo);
            menuList.appendChild(menuRow);
        });

        menuWrap.appendChild(menuList);
    } else {
        const emptyMenuText = document.createElement("p");
        emptyMenuText.className = "mb-1 text-muted fw-bold";
        emptyMenuText.textContent =
            "Belum ada data menu dengan nama dan harga.";
        menuWrap.appendChild(emptyMenuText);
    }

    if (menuGalleryItems.length > 0) {
        const galleryTitle = document.createElement("small");
        galleryTitle.className = "text-dark fw-bold d-block mb-2 mt-3";
        galleryTitle.textContent =
            "Foto daftar menu *Harga sewaktu-waktu dapat berubah";

        const galleryWrap = document.createElement("div");
        galleryWrap.className = "menu-gallery d-flex flex-wrap gap-2";

        menuGalleryItems.forEach((galleryItem) => {
            const galleryImg = document.createElement("img");
            galleryImg.className = "menu-gallery-thumb border-dark border-2";
            galleryImg.src =
                galleryItem.foto_menu_url || "/images/default-menu.svg";
            galleryImg.alt = `Foto daftar menu ${data.nama_umkm}`;
            galleryImg.style.cursor = "zoom-in";
            galleryImg.addEventListener("click", () => {
                openImageLightbox(
                    galleryImg.src,
                    `Foto daftar menu ${data.nama_umkm}`,
                );
            });
            galleryImg.onerror = function onGalleryError() {
                this.onerror = null;
                this.src = "/images/default-menu.svg";
            };

            galleryWrap.appendChild(galleryImg);
        });

        menuWrap.appendChild(galleryTitle);
        menuWrap.appendChild(galleryWrap);
    }

    content.appendChild(makeSection("fa-utensils", "Menu UMKM", menuWrap));

    const submitMenuBtn = document.createElement("button");
    submitMenuBtn.type = "button";
    submitMenuBtn.className = "btn btn-outline-dark btn-sm mt-2 neo-btn";
    submitMenuBtn.innerHTML =
        '<i class="fas fa-plus-circle me-1"></i>Ajukan Menu Baru';
    submitMenuBtn.addEventListener("click", () =>
        openMenuSubmissionModal(data.id, data.nama_umkm),
    );
    menuWrap.appendChild(submitMenuBtn);

    const resolvedCoords = resolveUmkmCoordinates(data);

    const actionsSection = document.createElement("div");
    actionsSection.className = "detail-actions p-2";
    const row = document.createElement("div");
    row.className = "row g-2";

    const ratingCol = document.createElement("div");
    ratingCol.className = "col-6";
    const ratingBtn = document.createElement("button");
    ratingBtn.type = "button";
    // PERUBAHAN: Menambahkan neo-btn
    ratingBtn.className = "btn btn-success btn-sm w-100 neo-btn";
    ratingBtn.innerHTML = '<i class="fas fa-star me-1"></i>Beri Rating';
    ratingBtn.addEventListener("click", () =>
        openRatingModal(data.id, data.nama_umkm),
    );
    ratingCol.appendChild(ratingBtn);

    const backCol = document.createElement("div");
    backCol.className = "col-6";
    const backLink = document.createElement("a");
    backLink.href = window.mapPageConfig?.landingUrl || "/";
    // PERUBAHAN: Menambahkan neo-btn
    backLink.className = "btn btn-outline-dark btn-sm w-100 neo-btn";
    backLink.innerHTML = '<i class="fas fa-arrow-left me-1"></i>Kembali';
    backCol.appendChild(backLink);

    const liveTrackCol = document.createElement("div");
    liveTrackCol.className = "col-12 mt-2";
    const liveTrackBtn = document.createElement("button");
    liveTrackBtn.type = "button";
    // PERUBAHAN: Menambahkan neo-btn
    liveTrackBtn.className = "btn btn-info btn-sm w-100 neo-btn";
    liveTrackBtn.innerHTML = '<i class="fas fa-route me-1"></i>Live Track';
    liveTrackBtn.disabled = !resolvedCoords;
    liveTrackBtn.addEventListener("click", () => {
        if (!resolvedCoords) {
            showAlert("error", "Koordinat UMKM belum tersedia.");
            return;
        }

        startLiveTrackingTo(
            resolvedCoords[0],
            resolvedCoords[1],
            data.nama_umkm,
        );
    });
    liveTrackCol.appendChild(liveTrackBtn);

    row.appendChild(ratingCol);
    row.appendChild(backCol);
    row.appendChild(liveTrackCol);
    actionsSection.appendChild(row);
    content.appendChild(actionsSection);
    panel.appendChild(header);
    panel.appendChild(content);
    panel.appendChild(actionsSection);
    document.body.appendChild(panel);
    prepareDetailPanel(panel);
}

function resetStars() {
    document.querySelectorAll(".rating-stars .star").forEach((star) => {
        star.className = "far fa-star star";
    });
}

function setStars(rating) {
    document.querySelectorAll(".rating-stars .star").forEach((star, index) => {
        star.className =
            index < rating
                ? "fas fa-star star text-warning"
                : "far fa-star star";
    });
}

function showAlert(type, message) {
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type === "success" ? "success" : "danger"} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText =
        "top: 20px; right: 20px; z-index: 9999; max-width: calc(100vw - 30px);";
    alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(alertDiv);
    setTimeout(() => {
        if (alertDiv.parentNode) alertDiv.remove();
    }, 4000);
}

function openImageLightbox(imageUrl, caption = "Preview Gambar") {
    if (!imageUrl || typeof bootstrap === "undefined") return;

    const modalEl = document.getElementById("imageLightboxModal");
    const imageEl = document.getElementById("imageLightboxPreview");
    const titleEl = document.getElementById("imageLightboxLabel");
    const zoomOutBtn = document.getElementById("lightboxZoomOutBtn");
    const zoomInBtn = document.getElementById("lightboxZoomInBtn");
    const resetZoomBtn = document.getElementById("lightboxResetZoomBtn");
    if (!modalEl || !imageEl || !titleEl) return;

    if (!imageLightboxModal) {
        imageLightboxModal = new bootstrap.Modal(modalEl);
    }

    const applyZoom = (nextZoom) => {
        imageLightboxZoom = Math.min(Math.max(nextZoom, 1), 3);
        if (imageLightboxZoom === 1) {
            imageLightboxTranslateX = 0;
            imageLightboxTranslateY = 0;
        } else {
            const clamped = clampLightboxTranslation(
                imageEl,
                imageLightboxTranslateX,
                imageLightboxTranslateY,
            );
            imageLightboxTranslateX = clamped.x;
            imageLightboxTranslateY = clamped.y;
        }
        updateLightboxTransform(imageEl, resetZoomBtn);
    };

    const removeGestureBindings = () => {
        if (typeof imageLightboxGestureCleanup === "function") {
            imageLightboxGestureCleanup();
            imageLightboxGestureCleanup = null;
        }
    };

    const bindGestureBindings = () => {
        removeGestureBindings();

        const onWheel = (event) => {
            if (imageLightboxMode === "mobile") return;
            event.preventDefault();
            applyZoom(imageLightboxZoom + (event.deltaY < 0 ? 0.15 : -0.15));
        };

        let pinchStartDistance = 0;
        let pinchStartZoom = 1;
        let pinchStartTranslateX = 0;
        let pinchStartTranslateY = 0;
        let pinchStartCenter = null;
        let dragStartX = 0;
        let dragStartY = 0;
        let dragOriginX = 0;
        let dragOriginY = 0;
        let panning = false;
        let mouseDragging = false;

        const getTouchDistance = (touches) =>
            Math.hypot(
                touches[0].clientX - touches[1].clientX,
                touches[0].clientY - touches[1].clientY,
            );

        const getTouchCenter = (touches) => ({
            x: (touches[0].clientX + touches[1].clientX) / 2,
            y: (touches[0].clientY + touches[1].clientY) / 2,
        });

        const onTouchStart = (event) => {
            if (imageLightboxMode !== "mobile") return;
            if (event.touches.length === 2) {
                pinchStartDistance = getTouchDistance(event.touches);
                pinchStartZoom = imageLightboxZoom;
                pinchStartTranslateX = imageLightboxTranslateX;
                pinchStartTranslateY = imageLightboxTranslateY;
                pinchStartCenter = getTouchCenter(event.touches);
                return;
            }

            if (event.touches.length === 1 && imageLightboxZoom > 1) {
                const touch = event.touches[0];
                dragStartX = touch.clientX;
                dragStartY = touch.clientY;
                dragOriginX = imageLightboxTranslateX;
                dragOriginY = imageLightboxTranslateY;
                panning = true;
            }
        };

        const onTouchMove = (event) => {
            if (imageLightboxMode !== "mobile") return;
            if (event.touches.length === 2 && pinchStartDistance > 0) {
                event.preventDefault();
                const nextZoom = Math.min(
                    Math.max(
                        pinchStartZoom *
                            (getTouchDistance(event.touches) /
                                pinchStartDistance),
                        1,
                    ),
                    3,
                );
                const center = getTouchCenter(event.touches);
                const referenceCenter = pinchStartCenter || center;
                imageLightboxZoom = nextZoom;
                imageLightboxTranslateX =
                    pinchStartTranslateX + (center.x - referenceCenter.x);
                imageLightboxTranslateY =
                    pinchStartTranslateY + (center.y - referenceCenter.y);
                const clamped = clampLightboxTranslation(
                    imageEl,
                    imageLightboxTranslateX,
                    imageLightboxTranslateY,
                );
                imageLightboxTranslateX = clamped.x;
                imageLightboxTranslateY = clamped.y;
                updateLightboxTransform(imageEl, resetZoomBtn);
                return;
            }

            if (
                event.touches.length === 1 &&
                panning &&
                imageLightboxZoom > 1
            ) {
                event.preventDefault();
                const touch = event.touches[0];
                const next = clampLightboxTranslation(
                    imageEl,
                    dragOriginX + (touch.clientX - dragStartX),
                    dragOriginY + (touch.clientY - dragStartY),
                );
                imageLightboxTranslateX = next.x;
                imageLightboxTranslateY = next.y;
                updateLightboxTransform(imageEl, resetZoomBtn);
            }
        };

        const onTouchEnd = () => {
            pinchStartDistance = 0;
            pinchStartCenter = null;
            panning = false;
        };

        const onMouseDown = (event) => {
            if (imageLightboxMode === "mobile" || imageLightboxZoom <= 1)
                return;
            event.preventDefault();
            mouseDragging = true;
            dragStartX = event.clientX;
            dragStartY = event.clientY;
            dragOriginX = imageLightboxTranslateX;
            dragOriginY = imageLightboxTranslateY;
            imageEl.style.cursor = "grabbing";
        };

        const onMouseMove = (event) => {
            if (!mouseDragging || imageLightboxZoom <= 1) return;
            event.preventDefault();
            const next = clampLightboxTranslation(
                imageEl,
                dragOriginX + (event.clientX - dragStartX),
                dragOriginY + (event.clientY - dragStartY),
            );
            imageLightboxTranslateX = next.x;
            imageLightboxTranslateY = next.y;
            updateLightboxTransform(imageEl, resetZoomBtn);
        };

        const onMouseUp = () => {
            mouseDragging = false;
            updateLightboxTransform(imageEl, resetZoomBtn);
        };

        const onDoubleClick = () => {
            if (imageLightboxMode !== "mobile") return;
            applyZoom(imageLightboxZoom > 1 ? 1 : 2);
        };

        imageEl.addEventListener("wheel", onWheel, { passive: false });
        imageEl.addEventListener("touchstart", onTouchStart, { passive: true });
        imageEl.addEventListener("touchmove", onTouchMove, { passive: false });
        imageEl.addEventListener("touchend", onTouchEnd);
        imageEl.addEventListener("touchcancel", onTouchEnd);
        imageEl.addEventListener("mousedown", onMouseDown);
        window.addEventListener("mousemove", onMouseMove);
        window.addEventListener("mouseup", onMouseUp);
        imageEl.addEventListener("dblclick", onDoubleClick);

        imageLightboxGestureCleanup = () => {
            imageEl.removeEventListener("wheel", onWheel);
            imageEl.removeEventListener("touchstart", onTouchStart);
            imageEl.removeEventListener("touchmove", onTouchMove);
            imageEl.removeEventListener("touchend", onTouchEnd);
            imageEl.removeEventListener("touchcancel", onTouchEnd);
            imageEl.removeEventListener("mousedown", onMouseDown);
            window.removeEventListener("mousemove", onMouseMove);
            window.removeEventListener("mouseup", onMouseUp);
            imageEl.removeEventListener("dblclick", onDoubleClick);
        };
    };

    if (zoomOutBtn && !zoomOutBtn.dataset.bound) {
        zoomOutBtn.addEventListener("click", () =>
            applyZoom(imageLightboxZoom - 0.15),
        );
        zoomOutBtn.dataset.bound = "true";
    }

    if (zoomInBtn && !zoomInBtn.dataset.bound) {
        zoomInBtn.addEventListener("click", () =>
            applyZoom(imageLightboxZoom + 0.15),
        );
        zoomInBtn.dataset.bound = "true";
    }

    if (resetZoomBtn && !resetZoomBtn.dataset.bound) {
        resetZoomBtn.addEventListener("click", () =>
            resetLightboxTransform(imageEl, resetZoomBtn),
        );
        resetZoomBtn.dataset.bound = "true";
    }

    modalEl.style.zIndex = "9999";

    imageLightboxMode = isMobileViewport() ? "mobile" : "desktop";
    modalEl.classList.toggle(
        "is-mobile-lightbox",
        imageLightboxMode === "mobile",
    );
    titleEl.textContent = caption || "Preview Gambar";
    imageEl.src = imageUrl;
    imageEl.alt = caption || "Preview Gambar";
    imageEl.onerror = function onImageError() {
        this.onerror = null;
        this.src = "/images/default-menu.svg";
    };
    resetLightboxTransform(imageEl, resetZoomBtn);
    bindGestureBindings();

    imageLightboxModal.show();
    setTimeout(() => {
        const backdrops = document.querySelectorAll(".modal-backdrop");
        const backdrop = backdrops[backdrops.length - 1];
        if (backdrop) {
            backdrop.style.zIndex = "9998";
        }
    }, 0);

    modalEl.addEventListener(
        "hidden.bs.modal",
        () => {
            removeGestureBindings();
            resetLightboxTransform(imageEl, resetZoomBtn);
        },
        { once: true },
    );
}

function resolveUmkmCoordinates(umkmDataItem) {
    const lat = Number(
        umkmDataItem?.latitude ?? umkmDataItem?.lokasi?.latitude,
    );
    const lng = Number(
        umkmDataItem?.longitude ?? umkmDataItem?.lokasi?.longitude,
    );

    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
        return null;
    }

    return [lat, lng];
}

function ensureLocationPermissionModal() {
    let modalElement = document.getElementById("locationPermissionModal");

    if (modalElement) {
        if (!locationPermissionModal) {
            locationPermissionModal = new bootstrap.Modal(modalElement, {
                backdrop: false,
                keyboard: true,
            });
        }
        return;
    }

    // Buat elemen modal baru
    const modal = document.createElement("div");
    modal.className = "modal fade";
    modal.id = "locationPermissionModal";

    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered location-permission-modal-dialog">
            <div class="modal-content location-permission-modal-content">
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

    // Inisialisasi modal Bootstrap
    locationPermissionModal = new bootstrap.Modal(modal, {
        backdrop: false,
        keyboard: true,
    });

    modal.addEventListener("shown.bs.modal", function () {
        document.body.classList.remove("sheet-open");
        document.body.style.overflow = "";
        document.body.style.paddingRight = "";
    });

    // Bersihkan backdrop secara paksa jika modal ditutup (Solusi Ampuh untuk Mobile Freeze)
    modal.addEventListener("hidden.bs.modal", function () {
        const backdrop = document.querySelector(".modal-backdrop");
        if (backdrop) {
            backdrop.remove();
        }
        document.body.style.overflow = "";
        document.body.style.paddingRight = "";
    });

    document.getElementById("confirmLocationPermissionBtn")
        ?.addEventListener("click", () => {
            if (!pendingLiveTrackingPayload) {
                locationPermissionModal?.hide();
                return;
            }

            const { latitude, longitude, umkmName } =
                pendingLiveTrackingPayload;
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
        return "-";
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

function setLiveTrackFloatingStatus(message, tone = "muted") {
    const floatingStatus = document.getElementById("liveTrackFloatingStatus");
    if (!floatingStatus) return;

    floatingStatus.classList.remove(
        "text-muted",
        "text-success",
        "text-danger",
        "text-primary",
    );
    floatingStatus.classList.add(`text-${tone}`);
    floatingStatus.textContent = message || "";
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
        headers: { Accept: "application/json" },
    });

    if (!response.ok) {
        throw new Error("OSRM response not ok");
    }

    const data = await response.json();
    const bestRoute = data?.routes?.[0];
    const coordinates = bestRoute?.geometry?.coordinates;

    if (!bestRoute || !Array.isArray(coordinates) || coordinates.length < 2) {
        throw new Error("OSRM route invalid");
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

    setLiveTrackFloatingStatus("");
    setTrackingControlsVisible(false);

    if (showMessage) {
        showAlert("success", "Live tracking dihentikan.");
    }
}

function startLiveTrackingTo(
    latitude,
    longitude,
    umkmName = "UMKM",
    skipPermissionDialog = false,
) {
    const destination = [Number(latitude), Number(longitude)];
    if (!Number.isFinite(destination[0]) || !Number.isFinite(destination[1])) {
        showAlert("error", "Koordinat tujuan tidak valid.");
        return;
    }

    if (!skipPermissionDialog) {
        if (isMobileViewport()) {
            closeDetailPanel();
            document.body.classList.remove("sheet-open");
            document.body.style.overflow = "";
            document.body.style.paddingRight = "";
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
        showAlert("error", "Browser Anda tidak mendukung geolocation.");
        return;
    }

    stopLiveTracking(false);
    setTrackingControlsVisible(true);
    setLiveTrackFloatingStatus("Mencari lokasi Anda...", "primary");

    liveTrackingDestinationMarker = L.circleMarker(destination, {
        radius: 7,
        color: "#2563eb",
        fillColor: "#2563eb",
        fillOpacity: 0.9,
        weight: 2,
    })
        .addTo(map)
        .bindTooltip(`Tujuan: ${umkmName}`, { direction: "top" });

    navigator.geolocation.getCurrentPosition(
        () => {
            storeLocationConsent();

            liveTrackingWatchId = navigator.geolocation.watchPosition(
                async (position) => {
                    const userPosition = [
                        position.coords.latitude,
                        position.coords.longitude,
                    ];

                    if (!liveTrackingUserMarker) {
                        liveTrackingUserMarker = L.marker(userPosition, {
                            icon: createUserMarkerIcon(),
                            zIndexOffset: 1100,
                        })
                            .addTo(map)
                            .bindTooltip("Posisi Anda");
                    } else {
                        liveTrackingUserMarker.setLatLng(userPosition);
                    }

                    const movedDistance = liveTrackingLastUserPosition
                        ? map.distance(
                              userPosition,
                              liveTrackingLastUserPosition,
                          )
                        : Number.POSITIVE_INFINITY;
                    const now = Date.now();
                    const shouldFetchRoute =
                        movedDistance >= 20 ||
                        now - liveTrackingLastRouteFetchAt >= 5000;

                    if (!shouldFetchRoute) {
                        return;
                    }

                    liveTrackingLastUserPosition = userPosition;
                    liveTrackingLastRouteFetchAt = now;

                    setLiveTrackFloatingStatus(
                        "Memuat rute jalan OSRM...",
                        "primary",
                    );

                    try {
                        const route = await fetchOsrmRoute(
                            userPosition,
                            destination,
                        );

                        if (!liveTrackingRouteLine) {
                            liveTrackingRouteLine = L.polyline(route.latLngs, {
                                color: "#0ea5e9",
                                weight: 4,
                                opacity: 0.9,
                            }).addTo(map);
                        } else {
                            liveTrackingRouteLine.setLatLngs(route.latLngs);
                        }

                        setLiveTrackFloatingStatus(
                            `Rute ke ${umkmName}: ${formatDistance(route.distance)} (${formatDuration(route.duration)})`,
                            "success",
                        );

                        if (!liveTrackingFocused) {
                            map.fitBounds(liveTrackingRouteLine.getBounds(), {
                                padding: [50, 50],
                                maxZoom: 17,
                            });
                            liveTrackingFocused = true;
                        }
                    } catch (error) {
                        if (error.name === "AbortError") {
                            return;
                        }

                        console.error("OSRM error:", error);
                        setLiveTrackFloatingStatus(
                            "Gagal memuat rute jalan OSRM. Pastikan internet stabil.",
                            "danger",
                        );
                    }
                },
                (error) => {
                    let message = "Gagal mendapatkan lokasi Anda.";
                    if (error.code === 1) {
                        message =
                            "Izin lokasi ditolak. Aktifkan GPS/lokasi untuk live tracking.";
                    } else if (error.code === 2) {
                        message =
                            "Lokasi tidak tersedia. Coba lagi di area dengan sinyal lebih baik.";
                    } else if (error.code === 3) {
                        message = "Permintaan lokasi timeout. Coba lagi.";
                    }

                    setLiveTrackFloatingStatus(message, "danger");
                    showAlert("error", message);
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
            let message = "Gagal mendapatkan izin lokasi.";
            if (error.code === 1) {
                message =
                    "Izin lokasi ditolak. Aktifkan akses lokasi lalu coba lagi.";
            } else if (error.code === 2) {
                message =
                    "Lokasi tidak tersedia. Coba lagi di area dengan sinyal lebih baik.";
            } else if (error.code === 3) {
                message = "Permintaan lokasi timeout. Coba lagi.";
            }

            setLiveTrackFloatingStatus(message, "danger");
            showAlert("error", message);
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
    const modalEl = document.getElementById("ratingModal");
    if (!modalEl) return;

    if (!ratingModal) {
        ratingModal = new bootstrap.Modal(modalEl);
    }

    if (typeof closeDetailPanel === "function") {
        closeDetailPanel();
    }

    modalEl.style.zIndex = "1115";

    document.getElementById("ratingUmkmId").value = umkmId;
    document.getElementById("ratingForm").reset();
    document.getElementById("nilaiRating").value = "0";
    document.getElementById("ratingText").textContent = "Belum dipilih";
    resetStars();

    requestAnimationFrame(() => {
        const backdrop = document.querySelector(
            ".modal-backdrop.show:not([data-rating-modal-backdrop])",
        );
        if (backdrop) {
            backdrop.dataset.ratingModalBackdrop = "false";
            backdrop.style.zIndex = "1110";
        }
    });

    ratingModal.show();
}

function openMenuSubmissionModal(umkmId, umkmName) {
    const modalEl = document.getElementById("menuSubmissionModal");
    if (!modalEl || typeof bootstrap === "undefined") return;

    if (!menuSubmissionModal) {
        menuSubmissionModal = new bootstrap.Modal(modalEl);
    }

    if (typeof closeDetailPanel === "function") {
        closeDetailPanel();
    }

    modalEl.style.zIndex = "1115";

    const inputUmkmId = document.getElementById("menuSubmissionUmkmId");
    const targetName = document.getElementById("menuSubmissionTargetName");
    if (inputUmkmId) inputUmkmId.value = String(umkmId || "");
    if (targetName) targetName.textContent = umkmName || "-";

    requestAnimationFrame(() => {
        const backdrop = document.querySelector(
            ".modal-backdrop.show:not([data-menu-submission-backdrop])",
        );
        if (backdrop) {
            backdrop.dataset.menuSubmissionBackdrop = "false";
            backdrop.style.zIndex = "1110";
        }
    });

    menuSubmissionModal.show();
}

function initRatingFeature() {
    const starsWrapper = document.querySelector(".rating-stars");
    const ratingForm = document.getElementById("ratingForm");
    if (!starsWrapper || !ratingForm) return;

    starsWrapper.querySelectorAll(".star").forEach((star) => {
        star.addEventListener("click", function clickStar() {
            const rating = parseInt(this.getAttribute("data-rating"), 10);
            document.getElementById("nilaiRating").value = String(rating);
            setStars(rating);
            const ratingTexts = [
                "Sangat Buruk",
                "Buruk",
                "Cukup",
                "Baik",
                "Sangat Baik",
            ];
            document.getElementById("ratingText").textContent =
                `${ratingTexts[rating - 1]} (${rating}/5)`;
        });

        star.addEventListener("mouseenter", function hoverStar() {
            setStars(parseInt(this.getAttribute("data-rating"), 10));
        });
    });

    starsWrapper.addEventListener("mouseleave", () => {
        setStars(parseInt(document.getElementById("nilaiRating").value, 10));
    });

    // ratingForm.addEventListener("submit", async (event) => {
    //     event.preventDefault();

    //     try {
    //         const response = await fetch(window.mapPageConfig.ratingStoreUrl, {
    //             method: "POST",
    //             body: new FormData(ratingForm),
    //             headers: {
    //                 "X-CSRF-TOKEN": document
    //                     .querySelector('meta[name="csrf-token"]')
    //                     .getAttribute("content"),
    //                 Accept: "application/json",
    //             },
    //         });

    //         const data = await response.json();
    //         if (!response.ok || !data.success) {
    //             throw new Error(data.message || "Gagal menyimpan rating.");
    //         }

    //         ratingModal?.hide();
    //         showAlert("success", data.message || "Rating berhasil dikirim.");
    //         setTimeout(() => window.location.reload(), 1200);
    //     } catch (error) {
    //         console.error(error);
    //         showAlert("error", "Terjadi kesalahan saat mengirim rating.");
    //     }
    // });
}

function initMapFeature(config) {
    if (!window.L) return;
    pendingSelectedUmkmId = config.selectedUmkm?.id || null;

    const mapContainer = document.createElement("div");
    mapContainer.id = "map";
    document.body.insertBefore(mapContainer, document.body.firstChild);

    const upi = config.upiCenter || {};
    const initialCenter =
        pendingSelectedUmkmId && config.selectedUmkm
            ? [config.selectedUmkm.latitude, config.selectedUmkm.longitude]
            : [
                  upi.latitude || -6.861082410263256,
                  upi.longitude || 107.59205888361987,
              ];
    const initialZoom = pendingSelectedUmkmId && config.selectedUmkm ? 18 : 16;

    map = L.map("map", { zoomControl: false }).setView(
        initialCenter,
        initialZoom,
    );
    L.control.zoom({ position: "bottomright" }).addTo(map);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    if (typeof upi.latitude === "number" && typeof upi.longitude === "number") {
        const center = [upi.latitude, upi.longitude];
        L.circle(center, {
            color: "blue",
            fillColor: "#3388ff",
            fillOpacity: 0.2,
            weight: 2,
            radius: Number(upi.radius || 1000),
        })
            .addTo(map)
            .bindPopup("Radius 1km dari UPI........");

        L.marker(center)
            .addTo(map)
            .bindPopup("Pusat UPI (Universitas Pendidikan Indonesia)");
    }

    markerClusterLayer = L.markerClusterGroup({
        showCoverageOnHover: false,
        chunkedLoading: true,
        removeOutsideVisibleBounds: true,
        disableClusteringAtZoom: 18,
        spiderfyOnMaxZoom: true,
    });
    map.addLayer(markerClusterLayer);
    loadVisibleUmkmsDebounced = debounce(() => loadVisibleUmkms(), 300);

    const mapSearchInput = document.getElementById("mapSearchInput");
    const mapSearchBtn = document.getElementById("mapSearchBtn");
    const toggleSearchFiltersBtn = document.getElementById(
        "toggleSearchFiltersBtn",
    );
    const toggleMoreFiltersBtn = document.getElementById(
        "toggleMoreFiltersBtn",
    );
    const searchFilterDropdown = document.getElementById(
        "searchFilterDropdown",
    );
    const desktopFilterPanel = document.getElementById("desktopFilterPanel");
    const mobileFilterSheet = document.getElementById("mobileFilterSheet");
    const mobileFilterBackdrop = document.getElementById(
        "mobileFilterBackdrop",
    );

    const showSearchFilters = () => {
        setMapControlsCompact(false);
        searchFilterDropdown?.classList.remove("d-none");
        syncSearchFilterToggleState(true);
    };

    const hideSearchFilters = () => {
        if (filterState.searchQuery) return;
        searchFilterDropdown?.classList.add("d-none");
        desktopFilterPanel?.classList.add("d-none");
        syncSearchFilterToggleState(false);
    };

    if (mapSearchInput) {
        mapSearchInput.addEventListener("focus", () => {
            showSearchFilters();
        });

        mapSearchInput.addEventListener("input", function onSearchInput() {
            filterState.searchQuery = normalizeText(this.value);
            if (filterState.searchQuery) {
                showSearchFilters();
            } else {
                hideSearchFilters();
            }
            applyMapFilters(false);
        });

        mapSearchInput.addEventListener("keydown", (event) => {
            if (event.key === "Enter") {
                event.preventDefault();
                filterState.searchQuery = normalizeText(mapSearchInput.value);
                applyMapFilters(true);
            }
        });
    }

    mapSearchBtn?.addEventListener("click", () => {
        setMapControlsCompact(false);
        showSearchFilters();
        filterState.searchQuery = normalizeText(mapSearchInput?.value || "");
        applyMapFilters(true);
    });

    toggleSearchFiltersBtn?.addEventListener("click", () => {
        setMapControlsCompact(false);
        const isHidden = searchFilterDropdown?.classList.toggle("d-none");
        syncSearchFilterToggleState(!isHidden);
    });

    const closeMobileSheet = () => {
        mobileFilterSheet?.classList.add("d-none");
        mobileFilterBackdrop?.classList.add("d-none");
        document.body.classList.remove("sheet-open");
    };

    const syncResponsiveFilterUI = () => {
        initCategoryChipsInteraction();

        const detailPanel = document.getElementById("umkm-detail-panel");
        if (detailPanel) {
            prepareDetailPanel(detailPanel);
        }

        if (window.innerWidth > 768) {
            closeMobileSheet();
        }
    };

    toggleMoreFiltersBtn?.addEventListener("click", () => {
        setMapControlsCompact(false);
        syncFilterControls();
        if (window.innerWidth <= 768) {
            mobileFilterSheet?.classList.remove("d-none");
            mobileFilterBackdrop?.classList.remove("d-none");
            document.body.classList.add("sheet-open");
        } else {
            desktopFilterPanel?.classList.toggle("d-none");
        }
    });

    document
        .getElementById("closeMobileFilterSheet")
        ?.addEventListener("click", () => {
            closeMobileSheet();
        });
    mobileFilterBackdrop?.addEventListener("click", closeMobileSheet);
    window.addEventListener("resize", syncResponsiveFilterUI);
    if (map) {
        map.on("movestart zoomstart dragstart click", collapseMapInteractions);
        map.getContainer()?.addEventListener("touchstart", collapseMapInteractions, {
            passive: true,
        });
    }
    map.on("moveend zoomend", () => {
        loadVisibleUmkmsDebounced?.();
    });

    document
        .getElementById("desktopApplyFilters")
        ?.addEventListener("click", () => {
            filterState.group =
                document.getElementById("desktopGroupFilter").value || "all";
            filterState.minRating = parseFloat(
                document.getElementById("desktopMinRating").value || "0",
            );
            filterState.openNow =
                document.getElementById("desktopOpenNow").checked;
            syncFilterControls();
            applyMapFilters(true);
        });

    document
        .getElementById("desktopResetFilters")
        ?.addEventListener("click", () => {
            filterState.group = "all";
            filterState.minRating = 0;
            filterState.openNow = false;
            syncFilterControls();
            applyMapFilters(true);
        });

    document
        .getElementById("mobileApplyFilters")
        ?.addEventListener("click", () => {
            filterState.group =
                document.getElementById("mobileGroupFilter").value || "all";
            filterState.minRating = parseFloat(
                document.getElementById("mobileMinRating").value || "0",
            );
            filterState.openNow =
                document.getElementById("mobileOpenNow").checked;
            syncFilterControls();
            closeMobileSheet();
            applyMapFilters(true);
        });

    document
        .getElementById("mobileResetFilters")
        ?.addEventListener("click", () => {
            filterState.group = "all";
            filterState.minRating = 0;
            filterState.openNow = false;
            syncFilterControls();
            closeMobileSheet();
            applyMapFilters(true);
        });

    document
        .getElementById("globalStopTrackingBtn")
        ?.addEventListener("click", () => {
            stopLiveTracking(true);
        });

    syncFilterControls();
    initCategoryChipsInteraction();
    syncResponsiveFilterUI();
    setMapControlsCompact(false);
    prepareDetailPanel(document.getElementById("umkm-detail-panel"));
    syncSearchFilterToggleState(
        !searchFilterDropdown?.classList.contains("d-none"),
    );
    if (filterState.searchQuery) {
        showSearchFilters();
    }
    loadVisibleUmkms();

    setTimeout(() => map.invalidateSize(), 300);
}

document.addEventListener("DOMContentLoaded", () => {
    const config = readMapConfig();
    if (!config) return;

    window.mapPageConfig = config;
    window.closeDetailPanel = closeDetailPanel;
    window.toggleUlasan = toggleUlasan;
    window.openRatingModal = openRatingModal;
    window.openMenuSubmissionModal = openMenuSubmissionModal;
    window.openImageLightbox = openImageLightbox;
    window.showUmkmDetail = showUmkmDetail;
    window.startLiveTrackingTo = startLiveTrackingTo;
    window.stopLiveTracking = stopLiveTracking;

    initMapFeature(config);
    initRatingFeature();
});

// Fungsi global untuk menampilkan pesan Neo-Brutalism via JavaScript
window.showAlert = function(type, message) {
    // 1. Cari kontainer flash message, jika belum ada, buat baru
    let container = document.getElementById('neo-flash-container');

    if (!container) {
        const wrapper = document.createElement('div');
        wrapper.className = 'fixed-top px-3 pt-3';
        wrapper.style.cssText = 'z-index: 1116; pointer-events: none;';

        container = document.createElement('div');
        container.id = 'neo-flash-container';
        container.className = 'container d-flex flex-column align-items-end gap-2';
        container.style.cssText = 'pointer-events: auto;';

        wrapper.appendChild(container);
        document.body.appendChild(wrapper);
    }

    // 2. Tentukan warna dan ikon berdasarkan tipe (success / error)
    const isSuccess = type === 'success';
    const alertClass = isSuccess ? 'neo-alert-success' : 'neo-alert-danger';
    const iconClass = isSuccess ? 'fa-check-circle' : 'fa-exclamation-triangle';

    // 3. Buat elemen alert
    const alertEl = document.createElement('div');
    // Tambahkan class yang sama dengan file Blade Anda
    alertEl.className = `neo-alert-flash ${alertClass} fade show d-flex align-items-center justify-content-between p-3`;
    alertEl.setAttribute('role', 'alert');

    // Isi HTML di dalamnya
    alertEl.innerHTML = `
        <div class="fw-black text-uppercase me-4" style="color: #000;">
            <i class="fas ${iconClass} me-2"></i>${message}
        </div>
        <button type="button" class="neo-btn-square-close" onclick="this.parentElement.remove()"
                style="background: transparent; border: 2px solid #000; padding: 2px 8px; font-weight: 900; cursor: pointer;">
            <i class="fas fa-times"></i>
        </button>
    `;

    // 4. Masukkan ke dalam layar
    container.appendChild(alertEl);

    // 5. Hilangkan otomatis setelah 3 detik (jika halaman tidak keburu di-reload)
    setTimeout(() => {
        if (alertEl.parentElement) {
            alertEl.remove();
        }
    }, 5000);
};

document.addEventListener('DOMContentLoaded', function() {
    // Cari semua form yang memiliki class 'neo-submit-form'
    const submissionForms = document.querySelectorAll('.neo-submit-form');
    const loaderOverlay = document.getElementById('neoFormLoader');

    submissionForms.forEach(form => {
        if (form.dataset.neoLoaderBound === 'true') return;
        form.dataset.neoLoaderBound = 'true';

        form.addEventListener('submit', function(e) {
            // Cek apakah form sudah diisi dengan benar (validasi bawaan HTML5)

            e.preventDefault();

            // 1. Tampilkan layar loading
            if (loaderOverlay) {
                loaderOverlay.classList.remove('d-none');
            }

            // 2. Disable tombol submit untuk mencegah double-click
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                // Ubah teks tombol jika diinginkan
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>MENGIRIM...';
            }

            window.requestAnimationFrame(() => form.submit());

        }, true);
    });
});

