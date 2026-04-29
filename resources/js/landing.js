let isSearching = false;
let searchTimeout;

document.addEventListener('DOMContentLoaded', function() {
    const desktopInput = document.querySelector('#headerSearchInput');
    const desktopForm = document.querySelector('#headerSearchForm');
    const mobileInput = document.querySelector('#mobileHeaderSearchInput');
    const mobileForm = document.querySelector('#mobileHeaderSearchForm');
    const mobileFilterModalEl = document.getElementById('mobileLandingFilterModal');
    const openMobileFilterBtn = document.getElementById('openMobileFilterBtn');
    const mobileFilterApplyBtn = document.getElementById('mobileFilterApplyBtn');
    const mobileFilterResetBtn = document.getElementById('mobileFilterResetBtn');

    const getDesktopFilterSelect = (name) => desktopForm?.querySelector(`select[name="${name}"]`) || null;
    const getMobileHidden = (name) => mobileForm?.querySelector(`input[type="hidden"][name="${name}"]`) || null;

    const ensureMobileHidden = (name) => {
        if (!mobileForm) return null;
        let hidden = getMobileHidden(name);
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = name;
            mobileForm.appendChild(hidden);
        }
        return hidden;
    };

    const syncFilterValue = (name, value) => {
        const normalized = String(value || '');
        const desktopSelect = getDesktopFilterSelect(name);
        if (desktopSelect) {
            desktopSelect.value = normalized;
        }

        const hidden = ensureMobileHidden(name);
        if (!hidden) return;
        hidden.value = normalized;
        if (!normalized) {
            hidden.remove();
        }
    };

    const readInitialFilterValue = (name) => {
        const desktopValue = getDesktopFilterSelect(name)?.value;
        if (desktopValue) return desktopValue;
        const mobileHiddenValue = getMobileHidden(name)?.value;
        return mobileHiddenValue || '';
    };

    const syncModalFilterInputs = () => {
        const kelompokSelect = document.getElementById('mobileFilterKelompok');
        const kategoriSelect = document.getElementById('mobileFilterKategori');
        const ratingSelect = document.getElementById('mobileFilterRating');

        if (kelompokSelect) kelompokSelect.value = readInitialFilterValue('id_kelompok');
        if (kategoriSelect) kategoriSelect.value = readInitialFilterValue('id_kategori');
        if (ratingSelect) ratingSelect.value = readInitialFilterValue('min_rating');
    };

    let mobileFilterModal = null;
    if (mobileFilterModalEl && typeof bootstrap !== 'undefined') {
        mobileFilterModal = new bootstrap.Modal(mobileFilterModalEl);
    }

    const syncInputValue = (value, source) => {
        if (source !== 'desktop' && desktopInput) desktopInput.value = value;
        if (source !== 'mobile' && mobileInput) mobileInput.value = value;
    };

    const registerInputHandlers = (input, source) => {
        if (!input) return;

        input.addEventListener('input', function() {
            const query = this.value.trim();
            syncInputValue(query, source);
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performLiveSearch(query, true);
            }, 300);
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = this.value.trim();
                syncInputValue(query, source);
                performLiveSearch(query, true);
            }
        });
    };

    registerInputHandlers(desktopInput, 'desktop');
    registerInputHandlers(mobileInput, 'mobile');

    const registerFormHandlers = (form, input) => {
        if (!form || !input) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            performLiveSearch(input.value.trim(), true);
        });
    };

    registerFormHandlers(desktopForm, desktopInput);
    registerFormHandlers(mobileForm, mobileInput);

    if (openMobileFilterBtn && mobileFilterModal) {
        openMobileFilterBtn.addEventListener('click', function() {
            syncModalFilterInputs();
            mobileFilterModal.show();
        });
    }

    mobileFilterApplyBtn?.addEventListener('click', function() {
        syncFilterValue('id_kelompok', document.getElementById('mobileFilterKelompok')?.value || '');
        syncFilterValue('id_kategori', document.getElementById('mobileFilterKategori')?.value || '');
        syncFilterValue('min_rating', document.getElementById('mobileFilterRating')?.value || '');
        mobileFilterModal?.hide();

        const query = (mobileInput?.value || desktopInput?.value || '').trim();
        syncInputValue(query, 'mobile');
        performLiveSearch(query, true);
    });

    mobileFilterResetBtn?.addEventListener('click', function() {
        syncFilterValue('id_kelompok', '');
        syncFilterValue('id_kategori', '');
        syncFilterValue('min_rating', '');

        if (desktopInput) desktopInput.value = '';
        if (mobileInput) mobileInput.value = '';

        const kelompokSelect = document.getElementById('mobileFilterKelompok');
        const kategoriSelect = document.getElementById('mobileFilterKategori');
        const ratingSelect = document.getElementById('mobileFilterRating');
        if (kelompokSelect) kelompokSelect.value = '';
        if (kategoriSelect) kategoriSelect.value = '';
        if (ratingSelect) ratingSelect.value = '';

        mobileFilterModal?.hide();
        performLiveSearch('', true);
    });

    attachPaginationHandlers();
});

function performLiveSearch(query, shouldScroll = false) { // Tambah parameter shouldScroll
    if (isSearching) return;
    isSearching = true;

    const headerSearchForm = document.querySelector('#headerSearchForm');
    const url = new URL(window.location.href);

    if (headerSearchForm) {
        const kelompokSelect = headerSearchForm.querySelector('select[name="id_kelompok"]');
        const kategoriSelect = headerSearchForm.querySelector('select[name="id_kategori"]');
        const ratingSelect = headerSearchForm.querySelector('select[name="min_rating"]');

        if (kelompokSelect && kelompokSelect.value) {
            url.searchParams.set('id_kelompok', kelompokSelect.value);
        } else {
            url.searchParams.delete('id_kelompok');
        }

        if (kategoriSelect && kategoriSelect.value) {
            url.searchParams.set('id_kategori', kategoriSelect.value);
        } else {
            url.searchParams.delete('id_kategori');
        }

        if (ratingSelect && ratingSelect.value) {
            url.searchParams.set('min_rating', ratingSelect.value);
        } else {
            url.searchParams.delete('min_rating');
        }
    }

    if (query) {
        url.searchParams.set('search', query);
    } else {
        url.searchParams.delete('search');
    }

    fetch(url.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(html, 'text/html');
        const newUmkmSection = newDoc.querySelector('.umkm-section');
        const newRecommendedSection = newDoc.querySelector('.recommended-section');
        const currentRecommendedSection = document.querySelector('.recommended-section');

        if (currentRecommendedSection) {
            if (newRecommendedSection) {
                currentRecommendedSection.innerHTML = newRecommendedSection.innerHTML;
            } else {
                currentRecommendedSection.remove();
            }
        }

        if (newUmkmSection) {
            const currentSection = document.querySelector('.umkm-section');
            currentSection.innerHTML = newUmkmSection.innerHTML;

            attachPaginationHandlers();

            // HANYA SCROLL JIKA DIMINTA (Misal: Tekan Enter atau Klik Button)
            if (shouldScroll) {
                scrollToResults();
            }
        }

        isSearching = false;
    })
    .catch(error => {
        console.error('Search error:', error);
        isSearching = false;
    });
}

function scrollToResults() {
    const targetSection = document.querySelector('.recommended-section') || document.querySelector('.umkm-section');
    if (targetSection) {
        // Ambil tinggi navbar agar tidak tertutup header yang sticky
        const navHeight = document.querySelector('.navbar-custom').offsetHeight;
        const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset - navHeight - 20;

        window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
        });
    }
}

function attachPaginationHandlers() {
    const paginationLinks = document.querySelectorAll('.pagination a');
    paginationLinks.forEach(link => {
        link.removeEventListener('click', handlePaginationClick);
        link.addEventListener('click', handlePaginationClick);
    });
}

function handlePaginationClick(e) {
    e.preventDefault();
    if (isSearching) return;

    const url = this.href;
    isSearching = true;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(html, 'text/html');
        const newUmkmSection = newDoc.querySelector('.umkm-section');

        if (newUmkmSection) {
            const currentSection = document.querySelector('.umkm-section');
            currentSection.innerHTML = newUmkmSection.innerHTML;
            attachPaginationHandlers();

            // Scroll to results after pagination
            scrollToResults();
        }

        isSearching = false;
    })
    .catch(error => {
        console.error('Pagination error:', error);
        isSearching = false;
    });
}
