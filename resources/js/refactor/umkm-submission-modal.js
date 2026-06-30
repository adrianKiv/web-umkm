(function () {
    function showModalIfNeeded(modalEl) {
        const showOnErrors = modalEl?.dataset?.showOnErrors === "1";
        if (showOnErrors) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

    function bindModalZIndex(modalEl) {
        if (!modalEl) return;
        modalEl.addEventListener("show.bs.modal", function () {
            if (typeof closeDetailPanel === "function") {
                closeDetailPanel();
            }

            modalEl.style.zIndex = "1115";
            requestAnimationFrame(() => {
                const backdrop = document.querySelector(
                    ".modal-backdrop.show:not([data-umkm-submission-backdrop])",
                );
                if (backdrop) {
                    backdrop.dataset.umkmSubmissionBackdrop = "true";
                    backdrop.style.zIndex = "1110";
                }
            });
        });

        modalEl.addEventListener("shown.bs.modal", function () {
            modalEl.style.zIndex = "1115";
            const backdrop = document.querySelector(
                '.modal-backdrop.show[data-umkm-submission-backdrop="true"]',
            );
            if (backdrop) {
                backdrop.style.zIndex = "1110";
            }
        });

        modalEl.addEventListener("hidden.bs.modal", function () {
            modalEl.style.zIndex = "";
            const backdrop = document.querySelector(
                '.modal-backdrop.show[data-umkm-submission-backdrop="true"]',
            );
            if (backdrop) {
                backdrop.style.zIndex = "";
                delete backdrop.dataset.umkmSubmissionBackdrop;
            }
        });
    }

    function bindMenuListActions() {
        const menuList = document.getElementById("submissionMenuList");
        const addBtn = document.getElementById("addSubmissionMenuItem");
        if (!menuList || !addBtn) return;

        function bindRemoveAction(root) {
            root.querySelectorAll("[data-remove-menu-item]").forEach((btn) => {
                btn.addEventListener("click", function () {
                    const items = menuList.querySelectorAll("[data-menu-item]");
                    if (items.length <= 1) {
                        const row = this.closest("[data-menu-item]");
                        row.querySelectorAll("input").forEach((input) => {
                            input.value = "";
                        });
                        return;
                    }

                    this.closest("[data-menu-item]")?.remove();
                });
            });
        }

        addBtn.addEventListener("click", function () {
            const wrapper = document.createElement("div");
            wrapper.className = "border rounded-3 p-2 submission-menu-item";
            wrapper.setAttribute("data-menu-item", "1");
            wrapper.innerHTML = `
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small">Nama Menu</label>
                        <input type="text" name="menu_nama[]" class="form-control form-control-sm" placeholder="Contoh: Ayam Bakar">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Harga</label>
                        <input type="number" step="0.01" min="0" name="menu_harga[]" class="form-control form-control-sm" placeholder="Contoh: 25000">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Foto Menu</label>
                        <input type="file" name="menu_foto[]" class="form-control form-control-sm" accept="image/*">
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-menu-item title="Hapus menu">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            menuList.appendChild(wrapper);
            bindRemoveAction(wrapper);
        });

        bindRemoveAction(menuList);
    }

    function init() {
        document.addEventListener("DOMContentLoaded", function () {
            const modalEl = document.getElementById("umkmSubmissionModal");
            if (modalEl) {
                bindModalZIndex(modalEl);
                showModalIfNeeded(modalEl);
            }

            bindMenuListActions();
        });
    }

    // Expose for potential direct calls
    window.UMKMSubmissionModal = {
        init: init,
        showOnErrors: function () {
            const modalEl = document.getElementById("umkmSubmissionModal");
            if (!modalEl) return;
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        },
    };

    init();
})();
