(function(){function d(o,t){const n=document.getElementById(o);n&&(n.addEventListener("show.bs.modal",function(){typeof closeDetailPanel=="function"&&closeDetailPanel(),n.style.zIndex="1115",requestAnimationFrame(()=>{const e=document.querySelector(".modal-backdrop.show:not(["+t+"])");e&&(e.dataset[t.replace(/[-@]/g,"")]="true",e.style.zIndex="1110")})}),n.addEventListener("shown.bs.modal",function(){n.style.zIndex="1115";const e=document.querySelector(".modal-backdrop.show["+t+'="true"]');e&&(e.style.zIndex="1110")}),n.addEventListener("hidden.bs.modal",function(){n.style.zIndex="";const e=document.querySelector(".modal-backdrop.show["+t+'="true"]');if(e){e.style.zIndex="";try{delete e.dataset[t.replace(/[-@]/g,"")]}catch{}}}))}function r(){const o=document.getElementById("menuSubmissionList"),t=document.getElementById("addMenuSubmissionItem");if(!o||!t)return;function n(a){a.querySelectorAll("[data-remove-menu-item]").forEach(l=>{l.addEventListener("click",function(){if(o.querySelectorAll("[data-menu-item]").length<=1){this.closest("[data-menu-item]")?.querySelectorAll("input").forEach(u=>{u.value=""});return}this.closest("[data-menu-item]")?.remove()})})}const e=()=>{const a=document.createElement("div");a.className="border rounded-3 p-2 submission-menu-item",a.setAttribute("data-menu-item","1"),a.innerHTML=`
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
            `,o.appendChild(a),n(a)};t.addEventListener("click",e),n(o);const s=document.getElementById("menuSubmissionModal");s&&s.addEventListener("hidden.bs.modal",function(){o.querySelectorAll("[data-menu-item]").forEach((l,i)=>{if(i===0){l.querySelectorAll("input").forEach(m=>{m.value=""});return}l.remove()})})}function c(o){const t=document.getElementById(o);if(!t)return;t.dataset?.showOnErrors==="1"&&typeof bootstrap<"u"&&new bootstrap.Modal(t).show()}document.addEventListener("DOMContentLoaded",function(){d("ratingModal","data-rating-modal-backdrop"),d("menuSubmissionModal","data-menu-submission-backdrop"),r(),c("menuSubmissionModal")})})();
