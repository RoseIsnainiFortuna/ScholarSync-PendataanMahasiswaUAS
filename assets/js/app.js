/* ============================================================
   assets/js/app.js — ScholarSync Academic
   ============================================================ */

document.addEventListener("DOMContentLoaded", function () {
  /* ---- Sidebar Toggle (mobile) ---- */
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("sidebarOverlay");
  const toggleBtn = document.getElementById("sidebarToggle");

  if (toggleBtn && sidebar && overlay) {
    toggleBtn.addEventListener("click", function () {
      sidebar.classList.toggle("open");
      overlay.classList.toggle("active");
    });

    overlay.addEventListener("click", function () {
      sidebar.classList.remove("open");
      overlay.classList.remove("active");
    });
  }

  /* ---- Auto-dismiss Bootstrap alerts ---- */
  document.querySelectorAll(".alert-auto-dismiss").forEach(function (el) {
    setTimeout(function () {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
      bsAlert.close();
    }, 4000);
  });

  /* ---- Theme toggle ---- */
  const themeToggleBtn = document.getElementById("themeToggleBtn");
  const themeToggleIcon = document.getElementById("themeToggleIcon");

  function setTheme(theme) {
    document.documentElement.setAttribute("data-theme", theme);
    localStorage.setItem("scholarsyncTheme", theme);
    if (themeToggleIcon) {
      themeToggleIcon.className =
        theme === "dark" ? "bi bi-sun" : "bi bi-moon-stars";
    }
  }

  function initTheme() {
    const savedTheme = localStorage.getItem("scholarsyncTheme");
    const preferredTheme =
      savedTheme ||
      (window.matchMedia("(prefers-color-scheme: dark)").matches
        ? "dark"
        : "light");
    setTheme(preferredTheme);
  }

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener("click", function () {
      const current =
        document.documentElement.getAttribute("data-theme") === "dark"
          ? "dark"
          : "light";
      setTheme(current === "dark" ? "light" : "dark");
    });
  }

  initTheme();

  /* ---- Generic form validation ---- */
  initFormValidation();
  initPhotoCrop();
  initDependentProdi();

  /* ---- Delete confirmation ---- */
  document.querySelectorAll(".btn-delete").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      const name = btn.dataset.name || "data ini";
      if (
        !confirm("Hapus " + name + "?\nTindakan ini tidak dapat dibatalkan.")
      ) {
        e.preventDefault();
      }
    });
  });

  /* ---- Show/hide password toggle ---- */
  document.querySelectorAll(".toggle-password").forEach(function (btn) {
    btn.addEventListener("click", function () {
      const target = document.querySelector(btn.dataset.target);
      if (!target) return;
      const isText = target.type === "text";
      target.type = isText ? "password" : "text";
      btn.querySelector("i").className = isText
        ? "bi bi-eye"
        : "bi bi-eye-slash";
    });
  });
});

/* ============================================================
   Form Validation Helper
   Gunakan: data-required, data-min-length, data-max, data-min
   pada <input> / <select>, dan data-validate="true" pada <form>
   ============================================================ */
function initFormValidation() {
  document
    .querySelectorAll('form[data-validate="true"]')
    .forEach(function (form) {
      form.addEventListener("submit", function (e) {
        let valid = true;

        // Clear previous errors
        form.querySelectorAll(".field-error").forEach(function (el) {
          el.textContent = "";
          el.classList.remove("show");
        });

        form.querySelectorAll("[data-required]").forEach(function (input) {
          const val = input.value.trim();
          const errEl = document.getElementById("err_" + input.name);

          if (val === "") {
            showError(
              errEl,
              input.dataset.required || "Field ini wajib diisi.",
            );
            valid = false;
            return;
          }

          const minLen = parseInt(input.dataset.minLength, 10);
          if (minLen && val.length < minLen) {
            showError(errEl, "Minimal " + minLen + " karakter.");
            valid = false;
            return;
          }

          const minVal = parseFloat(input.dataset.min);
          const maxVal = parseFloat(input.dataset.max);
          const numVal = parseFloat(val);

          if (!isNaN(minVal) && numVal < minVal) {
            showError(errEl, "Nilai minimal " + minVal);
            valid = false;
          } else if (!isNaN(maxVal) && numVal > maxVal) {
            showError(errEl, "Nilai maksimal " + maxVal);
            valid = false;
          }
        });

        if (!valid) e.preventDefault();
      });

      // Inline live feedback
      form.querySelectorAll("[data-required]").forEach(function (input) {
        input.addEventListener("input", function () {
          const errEl = document.getElementById("err_" + input.name);
          if (input.value.trim() !== "") {
            if (errEl) {
              errEl.textContent = "";
              errEl.classList.remove("show");
            }
            input.classList.remove("is-invalid");
            input.classList.add("is-valid");
          }
        });
      });
    });
}

function showError(el, msg) {
  if (!el) return;
  el.textContent = msg;
  el.classList.add("show");
  const input = el.previousElementSibling;
  if (input) input.classList.add("is-invalid");
}

function initPhotoCrop() {
  const fotoInput = document.getElementById("fotoInput");
  const cropPanel = document.getElementById("photoCropPanel");
  const cropCanvas = document.getElementById("photoCropCanvas");
  const zoomInput = document.getElementById("photoZoom");
  const rotateInput = document.getElementById("photoRotate");
  const croppedDataInput = document.getElementById("fotoCroppedData");
  if (
    !fotoInput ||
    !cropPanel ||
    !cropCanvas ||
    !zoomInput ||
    !rotateInput ||
    !croppedDataInput
  ) {
    return;
  }

  const ctx = cropCanvas.getContext("2d");
  let cropImage = null;
  let scale = 1;
  let rotation = 0;
  let offsetX = 0;
  let offsetY = 0;
  let dragging = false;
  let dragStart = { x: 0, y: 0 };
  let startOffset = { x: 0, y: 0 };

  function resetCropState() {
    scale = 1;
    rotation = 0;
    offsetX = 0;
    offsetY = 0;
    zoomInput.value = "1";
    rotateInput.value = "0";
    croppedDataInput.value = "";
  }

  function renderCrop() {
    if (!cropImage) {
      ctx.clearRect(0, 0, cropCanvas.width, cropCanvas.height);
      return;
    }

    const w = cropCanvas.width;
    const h = cropCanvas.height;
    ctx.clearRect(0, 0, w, h);
    ctx.fillStyle = "#f8fafc";
    ctx.fillRect(0, 0, w, h);

    const fitScale = Math.max(w / cropImage.width, h / cropImage.height);
    const drawWidth = cropImage.width * fitScale * scale;
    const drawHeight = cropImage.height * fitScale * scale;
    const rad = (rotation * Math.PI) / 180;

    ctx.save();
    ctx.translate(w / 2, h / 2);
    ctx.rotate(rad);
    ctx.drawImage(
      cropImage,
      -drawWidth / 2 + offsetX,
      -drawHeight / 2 + offsetY,
      drawWidth,
      drawHeight,
    );
    ctx.restore();

    ctx.strokeStyle = "rgba(255,255,255,0.85)";
    ctx.lineWidth = 2;
    ctx.strokeRect(1, 1, w - 2, h - 2);
  }

  function showCropPanel() {
    if (cropPanel.classList.contains("d-none")) {
      cropPanel.classList.remove("d-none");
    }
  }

  fotoInput.addEventListener("change", function () {
    const file = fotoInput.files && fotoInput.files[0];
    if (!file) {
      cropImage = null;
      cropPanel.classList.add("d-none");
      return;
    }

    const allowedTypes = ["image/jpeg", "image/png"];
    if (!allowedTypes.includes(file.type)) {
      alert("Format foto tidak valid. Silakan pilih file JPG/PNG.");
      fotoInput.value = "";
      return;
    }

    const reader = new FileReader();
    reader.onload = function (event) {
      cropImage = new Image();
      cropImage.onload = function () {
        resetCropState();
        renderCrop();
        showCropPanel();
      };
      cropImage.src = event.target.result;
    };
    reader.readAsDataURL(file);
  });

  zoomInput.addEventListener("input", function () {
    scale = parseFloat(zoomInput.value) || 1;
    renderCrop();
  });

  rotateInput.addEventListener("input", function () {
    rotation = parseInt(rotateInput.value, 10) || 0;
    renderCrop();
  });

  cropCanvas.style.cursor = "grab";

  cropCanvas.addEventListener("pointerdown", function (event) {
    if (!cropImage) {
      return;
    }
    dragging = true;
    dragStart = { x: event.clientX, y: event.clientY };
    startOffset = { x: offsetX, y: offsetY };
    cropCanvas.setPointerCapture(event.pointerId);
    cropCanvas.style.cursor = "grabbing";
  });

  cropCanvas.addEventListener("pointermove", function (event) {
    if (!dragging) {
      return;
    }
    offsetX = startOffset.x + (event.clientX - dragStart.x);
    offsetY = startOffset.y + (event.clientY - dragStart.y);
    renderCrop();
  });

  cropCanvas.addEventListener("pointerup", function (event) {
    dragging = false;
    cropCanvas.style.cursor = "grab";
    cropCanvas.releasePointerCapture(event.pointerId);
  });

  cropCanvas.addEventListener("pointerleave", function () {
    if (dragging) {
      dragging = false;
      cropCanvas.style.cursor = "grab";
    }
  });

  const form = fotoInput.closest("form");
  if (form) {
    form.addEventListener("submit", function (event) {
      if (!cropImage || cropPanel.classList.contains("d-none")) {
        return;
      }

      event.preventDefault();
      cropCanvas.toBlob(
        function (blob) {
          if (!blob) {
            form.submit();
            return;
          }

          const reader = new FileReader();
          reader.onloadend = function () {
            croppedDataInput.value = reader.result;
            form.submit();
          };
          reader.readAsDataURL(blob);
        },
        "image/jpeg",
        0.9,
      );
    });
  }
}

function initDependentProdi() {
  const fak = document.getElementById("id_fakultas");
  const prodi = document.getElementById("id_prodi");
  if (!fak || !prodi) return;

  function filterProdi() {
    const fid = fak.value;
    Array.from(prodi.options).forEach((opt) => {
      const df = opt.dataset.fakultas || "";
      if (opt.value === "" || opt.value === "0") return; // keep placeholder
      opt.style.display = fid === "" || fid === "0" || fid === df ? "" : "none";
    });
    const selected = prodi.querySelector("option:checked");
    if (selected && selected.style.display === "none") {
      prodi.value = "";
    }
  }

  fak.addEventListener("change", filterProdi);
  // initialize on load
  filterProdi();
}

/* ============================================================
   IPK Chart renderer (dipanggil dari halaman progress)
   ============================================================ */
function renderIPKChart(canvasId, labels, values) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;

  new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "IPK (Kumulatif)",
          data: values,
          borderColor: "#1a3a5c",
          backgroundColor: "rgba(26,58,92,.1)",
          tension: 0.35,
          pointRadius: 5,
          pointBackgroundColor: "#1a3a5c",
          fill: true,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => "IPK: " + ctx.parsed.y.toFixed(2),
          },
        },
      },
      scales: {
        y: {
          min: 0,
          max: 4,
          grid: { color: "#e2e8f0" },
          ticks: { font: { size: 11 } },
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 11 } },
        },
      },
    },
  });
}

/* ============================================================
   Dashboard bar chart
   ============================================================ */
function renderBarChart(canvasId, labels, values) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;

  new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          data: values,
          backgroundColor: labels.map((_, i) =>
            i === values.indexOf(Math.max(...values)) ? "#1a3a5c" : "#b8cce4",
          ),
          borderRadius: 6,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: { grid: { color: "#e2e8f0" }, beginAtZero: true },
        x: { grid: { display: false } },
      },
    },
  });
}
