document.addEventListener("DOMContentLoaded",function(){const t=document.getElementById("adminMenuList"),n=document.getElementById("addAdminMenuItem");if(!t||!n)return;const a=e=>{e.querySelectorAll("[data-remove-menu-item]").forEach(o=>{o.addEventListener("click",function(){if(t.querySelectorAll("[data-menu-item]").length<=1){this.closest("[data-menu-item]")?.querySelectorAll("input").forEach(m=>m.value="");return}this.closest("[data-menu-item]")?.remove()})})},l=()=>{const e=document.createElement("div");e.className="border rounded-3 p-2 submission-menu-item",e.setAttribute("data-menu-item","1"),e.innerHTML=`
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
        `,t.appendChild(e),a(e)};n.addEventListener("click",l),a(t)});
