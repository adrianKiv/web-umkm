document.addEventListener("DOMContentLoaded", function () {
    const menuList = document.getElementById("adminMenuList");
    const addBtn = document.getElementById("addAdminMenuItem");
    if (!menuList || !addBtn) return;

    const bindRemoveButtons = (root) => {
        root.querySelectorAll("[data-remove-menu-item]").forEach((button) => {
            button.addEventListener("click", function () {
                const rows = menuList.querySelectorAll("[data-menu-item]");
                if (rows.length <= 1) {
                    const row = this.closest("[data-menu-item]");
                    row?.querySelectorAll("input").forEach(
                        (input) => (input.value = ""),
                    );
                    return;
                }

                this.closest("[data-menu-item]")?.remove();
            });
        });
    };

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
});
