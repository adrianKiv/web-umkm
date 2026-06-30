(function () {
    function bindModalZIndexById(id, backdropAttr) {
        const modalEl = document.getElementById(id);
        if (!modalEl) return;
        modalEl.addEventListener("show.bs.modal", function () {
            if (typeof closeDetailPanel === "function") {
                closeDetailPanel();
            }

            modalEl.style.zIndex = "1115";
            requestAnimationFrame(() => {
                const backdrop = document.querySelector(
                    ".modal-backdrop.show:not([" + backdropAttr + "])",
                );
                if (backdrop) {
                    backdrop.dataset[backdropAttr.replace(/[-@]/g, "")] =
                        "true";
                    backdrop.style.zIndex = "1110";
                }
            });
        });

        modalEl.addEventListener("shown.bs.modal", function () {
            modalEl.style.zIndex = "1115";
            const backdrop = document.querySelector(
                ".modal-backdrop.show[" + backdropAttr + '="true"]',
            );
            if (backdrop) backdrop.style.zIndex = "1110";
        });

        modalEl.addEventListener("hidden.bs.modal", function () {
            modalEl.style.zIndex = "";
            const backdrop = document.querySelector(
                ".modal-backdrop.show[" + backdropAttr + '="true"]',
            );
            if (backdrop) {
                backdrop.style.zIndex = "";
                try {
                    delete backdrop.dataset[backdropAttr.replace(/[-@]/g, "")];
                } catch (e) {}
            }
        });
    }

    function bindMenuSubmissionList() {
        const menuList = document.getElementById("menuSubmissionList");
        const addBtn = document.getElementById("addMenuSubmissionItem");
        if (!menuList || !addBtn) return;

        function bindRemoveButtons(root) {
            root.querySelectorAll("[data-remove-menu-item]").forEach(
                (button) => {
                    button.addEventListener("click", function () {
                        const rows =
                            menuList.querySelectorAll("[data-menu-item]");
                        if (rows.length <= 1) {
                            const row = this.closest("[data-menu-item]");
                            row?.querySelectorAll("input").forEach((input) => {
                                input.value = "";
                            });
                            return;
                        }
                        this.closest("[data-menu-item]")?.remove();
                    });
                },
            );
        }

        const createMenuRow = () => {
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
            bindRemoveButtons(wrapper);
        };

        addBtn.addEventListener("click", createMenuRow);
        bindRemoveButtons(menuList);

        // when modal hides, reset extra rows
        const modalEl = document.getElementById("menuSubmissionModal");
        if (modalEl) {
            modalEl.addEventListener("hidden.bs.modal", function () {
                const rows = menuList.querySelectorAll("[data-menu-item]");
                rows.forEach((row, index) => {
                    if (index === 0) {
                        row.querySelectorAll("input").forEach((input) => {
                            input.value = "";
                        });
                        return;
                    }
                    row.remove();
                });
            });
        }
    }

    function showModalIfNeeded(modalId) {
        const modalEl = document.getElementById(modalId);
        if (!modalEl) return;
        const showOnErrors = modalEl.dataset?.showOnErrors === "1";
        if (showOnErrors && typeof bootstrap !== "undefined") {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        bindModalZIndexById("ratingModal", "data-rating-modal-backdrop");
        bindModalZIndexById(
            "menuSubmissionModal",
            "data-menu-submission-backdrop",
        );
        bindMenuSubmissionList();
        showModalIfNeeded("menuSubmissionModal");
    });
})();
