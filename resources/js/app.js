import "./bootstrap"; // ini bawaan laravel
import "bootstrap"; // ini untuk mengaktifkan JS Bootstrap (Modal, Dropdown, dll)

// Import semua JS dari Bootstrap
import * as bootstrap from "bootstrap";

// Masukkan ke dalam object window agar bisa diakses oleh file JS lain (seperti map.js / landing.js)
window.bootstrap = bootstrap;
