(function(){function i(e){e?.dataset?.showOnErrors==="1"&&new bootstrap.Modal(e).show()}function s(e){e&&(e.addEventListener("show.bs.modal",function(){typeof closeDetailPanel=="function"&&closeDetailPanel(),e.style.zIndex="1115",requestAnimationFrame(()=>{const t=document.querySelector(".modal-backdrop.show:not([data-umkm-submission-backdrop])");t&&(t.dataset.umkmSubmissionBackdrop="true",t.style.zIndex="1110")})}),e.addEventListener("shown.bs.modal",function(){e.style.zIndex="1115";const t=document.querySelector('.modal-backdrop.show[data-umkm-submission-backdrop="true"]');t&&(t.style.zIndex="1110")}),e.addEventListener("hidden.bs.modal",function(){e.style.zIndex="";const t=document.querySelector('.modal-backdrop.show[data-umkm-submission-backdrop="true"]');t&&(t.style.zIndex="",delete t.dataset.umkmSubmissionBackdrop)}))}function o(){const e=document.getElementById("submissionMenuList"),t=document.getElementById("addSubmissionMenuItem");if(!e||!t)return;function d(n){n.querySelectorAll("[data-remove-menu-item]").forEach(m=>{m.addEventListener("click",function(){if(e.querySelectorAll("[data-menu-item]").length<=1){this.closest("[data-menu-item]").querySelectorAll("input").forEach(c=>{c.value=""});return}this.closest("[data-menu-item]")?.remove()})})}t.addEventListener("click",function(){const n=document.createElement("div");n.className="neo-box submission-menu-item p-3",n.setAttribute("data-menu-item","1"),n.innerHTML=`
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="neo-form-label" style="font-size: 0.75rem;">Nama Menu</label>
                        <input type="text" name="menu_nama[]" class="form-control neo-input" placeholder="Ayam Bakar">
                    </div>
                    <div class="col-md-3">
                        <label class="neo-form-label" style="font-size: 0.75rem;">Harga (Rp)</label>
                        <input type="number" step="0.01" min="0" name="menu_harga[]" class="form-control neo-input" placeholder="25000">
                    </div>
                    <div class="col-md-4">
                        <label class="neo-form-label" style="font-size: 0.75rem;">Foto Menu</label>
                        <input type="file" name="menu_foto[]" class="form-control neo-input" style="padding-top: 0.2rem;" accept="image/*">
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="button" class="btn neo-btn-danger" style="padding: 0.45rem;" data-remove-menu-item title="Hapus menu">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `,e.appendChild(n),d(n)}),d(e)}function a(){document.addEventListener("DOMContentLoaded",function(){const e=document.getElementById("umkmSubmissionModal");e&&(s(e),i(e)),o()})}window.UMKMSubmissionModal={init:a,showOnErrors:function(){const e=document.getElementById("umkmSubmissionModal");if(!e)return;new bootstrap.Modal(e).show()}},a()})();document.addEventListener("DOMContentLoaded",function(){const i=document.querySelectorAll(".neo-submit-form"),s=document.getElementById("neoFormLoader");i.forEach(o=>{o.addEventListener("submit",function(a){if(o.checkValidity()){s&&s.classList.remove("d-none");const e=o.querySelector('button[type="submit"]');e&&(e.disabled=!0,e.innerHTML='<i class="fas fa-spinner fa-spin me-2"></i>MENGIRIM...')}})})});
