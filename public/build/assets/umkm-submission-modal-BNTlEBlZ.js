(function(){function a(e){e?.dataset?.showOnErrors==="1"&&new bootstrap.Modal(e).show()}function i(e){e&&(e.addEventListener("show.bs.modal",function(){typeof closeDetailPanel=="function"&&closeDetailPanel(),e.style.zIndex="1115",requestAnimationFrame(()=>{const t=document.querySelector(".modal-backdrop.show:not([data-umkm-submission-backdrop])");t&&(t.dataset.umkmSubmissionBackdrop="true",t.style.zIndex="1110")})}),e.addEventListener("shown.bs.modal",function(){e.style.zIndex="1115";const t=document.querySelector('.modal-backdrop.show[data-umkm-submission-backdrop="true"]');t&&(t.style.zIndex="1110")}),e.addEventListener("hidden.bs.modal",function(){e.style.zIndex="";const t=document.querySelector('.modal-backdrop.show[data-umkm-submission-backdrop="true"]');t&&(t.style.zIndex="",delete t.dataset.umkmSubmissionBackdrop)}))}function d(){const e=document.getElementById("submissionMenuList"),t=document.getElementById("addSubmissionMenuItem");if(!e||!t)return;function o(n){n.querySelectorAll("[data-remove-menu-item]").forEach(m=>{m.addEventListener("click",function(){if(e.querySelectorAll("[data-menu-item]").length<=1){this.closest("[data-menu-item]").querySelectorAll("input").forEach(l=>{l.value=""});return}this.closest("[data-menu-item]")?.remove()})})}t.addEventListener("click",function(){const n=document.createElement("div");n.className="border rounded-3 p-2 submission-menu-item",n.setAttribute("data-menu-item","1"),n.innerHTML=`
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
            `,e.appendChild(n),o(n)}),o(e)}function s(){document.addEventListener("DOMContentLoaded",function(){const e=document.getElementById("umkmSubmissionModal");e&&(i(e),a(e)),d()})}window.UMKMSubmissionModal={init:s,showOnErrors:function(){const e=document.getElementById("umkmSubmissionModal");if(!e)return;new bootstrap.Modal(e).show()}},s()})();
