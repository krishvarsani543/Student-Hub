/**
 * Practical 6: Rendering External JSON Data using Fetch API, Search & Filter
 * Course: Web Development Framework (WDF)
 */

document.addEventListener("DOMContentLoaded", function () {
  
  // State variables
  let currentDataset = "events";
  let rawData = [];
  let filteredData = [];
  let currentPage = 1;
  let pageSize = 8;

  // DOM Elements
  const tabs = document.querySelectorAll(".tab-btn");
  const searchInput = document.getElementById("searchInput");
  const clearSearchBtn = document.getElementById("clearSearchBtn");
  const categoryFilter = document.getElementById("categoryFilter");
  const sortFilter = document.getElementById("sortFilter");
  const pageSizeSelect = document.getElementById("pageSizeSelect");
  const dataContainer = document.getElementById("dataContainer");
  const loadingSpinner = document.getElementById("loadingSpinner");
  const errorContainer = document.getElementById("errorContainer");
  const errorMessage = document.getElementById("errorMessage");
  const emptyContainer = document.getElementById("emptyContainer");
  const retryBtn = document.getElementById("retryBtn");
  const showingCount = document.getElementById("showingCount");
  const totalCount = document.getElementById("totalCount");
  const cacheStatus = document.getElementById("cacheStatus");
  const prevPageBtn = document.getElementById("prevPageBtn");
  const nextPageBtn = document.getElementById("nextPageBtn");
  const pageNumbers = document.getElementById("pageNumbers");
  const dependentSection = document.getElementById("dependentSection");

  // Dependent Dropdown Elements
  const deptSelect = document.getElementById("deptSelect");
  const semSelect = document.getElementById("semSelect");
  const specSelect = document.getElementById("specSelect");
  const resetDependentBtn = document.getElementById("resetDependentBtn");

  const departmentData = {
    "Computer Engineering": {
      semesters: ["Semester 3", "Semester 5", "Semester 7"],
      specializations: ["Artificial Intelligence", "Cybersecurity", "Full-Stack Development", "Blockchain", "Competitive Programming"]
    },
    "Information Technology": {
      semesters: ["Semester 3", "Semester 5", "Semester 7"],
      specializations: ["Cloud & DevOps", "Data Analytics", "UI/UX & Frontend", "DevOps & SRE"]
    },
    "Artificial Intelligence & Data Science": {
      semesters: ["Semester 3", "Semester 5"],
      specializations: ["Deep Learning", "NLP & Computer Vision", "Machine Learning Ops"]
    },
    "Electronics & Communication": {
      semesters: ["Semester 5", "Semester 7"],
      specializations: ["Embedded Systems", "VLSI Design", "IoT & Robotics"]
    }
  };

  // ==========================================
  // 1. FETCH API DATA LOADING & CACHING
  // ==========================================
  async function loadData(datasetName) {
    currentDataset = datasetName;
    currentPage = 1;
    dataContainer.innerHTML = "";
    emptyContainer.style.display = "none";
    errorContainer.style.display = "none";
    loadingSpinner.style.display = "block";

    // Show or hide dependent filter (most relevant on students tab)
    if (dependentSection) {
      dependentSection.style.display = datasetName === "students" ? "block" : "none";
    }

    const cacheKey = `studenthub_cache_${datasetName}`;
    const url = `data/${datasetName}.json`;

    try {
      const response = await fetch(url);
      if (!response.ok) throw new Error(`HTTP Error ${response.status}: Failed to fetch ${url}`);
      
      const data = await response.json();
      rawData = data;

      // Update cache
      localStorage.setItem(cacheKey, JSON.stringify(data));
      cacheStatus.innerHTML = '<i class="fa-solid fa-cloud-arrow-down"></i> Live Fetch API';
      cacheStatus.style.background = '#ecfdf5';
      cacheStatus.style.color = '#065f46';

    } catch (err) {
      console.warn("Fetch failed, attempting to read from localStorage cache:", err);
      const cached = localStorage.getItem(cacheKey);

      if (cached) {
        rawData = JSON.parse(cached);
        cacheStatus.innerHTML = '<i class="fa-solid fa-hard-drive"></i> Offline LocalStorage Cache';
        cacheStatus.style.background = '#fef3c7';
        cacheStatus.style.color = '#92400e';
      } else {
        loadingSpinner.style.display = "none";
        errorContainer.style.display = "block";
        errorMessage.textContent = err.message;
        return;
      }
    }

    loadingSpinner.style.display = "none";
    populateCategoryFilter();
    applyFilterAndSort();
  }

  // ==========================================
  // 2. DYNAMIC CATEGORY FILTER POPULATION
  // ==========================================
  function populateCategoryFilter() {
    categoryFilter.innerHTML = '<option value="ALL">All Categories / Depts</option>';
    const categories = new Set();

    rawData.forEach((item) => {
      if (item.category) categories.add(item.category);
      if (item.department) categories.add(item.department);
    });

    categories.forEach((cat) => {
      const opt = document.createElement("option");
      opt.value = cat;
      opt.textContent = cat;
      categoryFilter.appendChild(opt);
    });
  }

  // ==========================================
  // 3. SEARCH, FILTER & SORT ENGINE
  // ==========================================
  function applyFilterAndSort() {
    const searchVal = searchInput.value.trim().toLowerCase();
    const categoryVal = categoryFilter.value;
    const sortVal = sortFilter.value;

    filteredData = rawData.filter((item) => {
      // 1. Search Query Match
      let matchesSearch = true;
      if (searchVal) {
        const searchableText = [
          item.title || "",
          item.name || "",
          item.question || "",
          item.answer || "",
          item.description || "",
          item.venue || "",
          item.department || "",
          item.id || ""
        ].join(" ").toLowerCase();
        matchesSearch = searchableText.includes(searchVal);
      }

      // 2. Category Match
      let matchesCategory = true;
      if (categoryVal !== "ALL") {
        matchesCategory = item.category === categoryVal || item.department === categoryVal;
      }

      // 3. Dependent Filter Match (if on students tab)
      if (currentDataset === "students" && deptSelect.value) {
        if (item.department !== deptSelect.value) return false;
        if (semSelect.value && `Semester ${item.semester}` !== semSelect.value) return false;
        if (specSelect.value && item.specialization !== specSelect.value) return false;
      }

      return matchesSearch && matchesCategory;
    });

    // Sort Logic
    if (sortVal === "name_asc") {
      filteredData.sort((a, b) => (a.title || a.name || a.question || "").localeCompare(b.title || b.name || b.question || ""));
    } else if (sortVal === "name_desc") {
      filteredData.sort((a, b) => (b.title || b.name || b.question || "").localeCompare(a.title || a.name || a.question || ""));
    }

    currentPage = 1;
    renderView();
  }

  // ==========================================
  // 4. VIEW RENDERING & PAGINATION
  // ==========================================
  function renderView() {
    totalCount.textContent = filteredData.length;
    
    if (filteredData.length === 0) {
      dataContainer.innerHTML = "";
      emptyContainer.style.display = "block";
      showingCount.textContent = "0";
      renderPaginationControls(0);
      return;
    }

    emptyContainer.style.display = "none";

    const totalPages = Math.ceil(filteredData.length / pageSize);
    if (currentPage > totalPages) currentPage = totalPages;

    const startIndex = (currentPage - 1) * pageSize;
    const endIndex = Math.min(startIndex + pageSize, filteredData.length);
    const paginatedItems = filteredData.slice(startIndex, endIndex);

    showingCount.textContent = `${startIndex + 1} - ${endIndex}`;

    // Render items based on dataset
    if (currentDataset === "events") {
      renderEvents(paginatedItems);
    } else if (currentDataset === "students") {
      renderStudents(paginatedItems);
    } else if (currentDataset === "faqs") {
      renderFaqs(paginatedItems);
    }

    renderPaginationControls(totalPages);
  }

  function renderEvents(items) {
    dataContainer.innerHTML = items.map((ev) => `
      <article class="item-card">
        <div class="card-img-wrapper">
          <img src="${ev.image}" alt="${ev.title}" onerror="this.src='../IMAGES/college campus.jpg'">
          <span class="card-badge">${ev.category}</span>
        </div>
        <div class="card-content">
          <h3>${ev.title}</h3>
          <div class="card-meta">
            <span><i class="fa-solid fa-calendar"></i> ${ev.date} at ${ev.time}</span>
            <span><i class="fa-solid fa-location-dot"></i> ${ev.venue}</span>
            <span><i class="fa-solid fa-chair"></i> ${ev.seatsAvailable} Seats Available</span>
          </div>
          <p class="card-desc">${ev.description}</p>
          <div class="card-footer">
            <span style="color:#059669; font-weight:700;"><i class="fa-solid fa-circle-check"></i> ${ev.status}</span>
            <button class="btn-card" onclick="alert('Proceeding to registration for: ${ev.title}')">
              Register Now
            </button>
          </div>
        </div>
      </article>
    `).join("");
  }

  function renderStudents(items) {
    dataContainer.innerHTML = items.map((s) => `
      <article class="item-card">
        <div class="card-content">
          <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div class="student-avatar">${s.name.charAt(0)}</div>
            <span class="gpa-pill">GPA: ${s.gpa}</span>
          </div>
          <h3>${s.name}</h3>
          <div class="card-meta">
            <span><i class="fa-solid fa-id-badge"></i> Roll No: <strong>${s.id}</strong></span>
            <span><i class="fa-solid fa-building-columns"></i> ${s.department}</span>
            <span><i class="fa-solid fa-graduation-cap"></i> Semester ${s.semester}</span>
            <span><i class="fa-solid fa-award"></i> Specialization: ${s.specialization}</span>
            <span><i class="fa-solid fa-envelope"></i> ${s.email}</span>
          </div>
          <div class="card-footer">
            <span style="color:#0284c7; font-weight:600;"><i class="fa-solid fa-user-check"></i> ${s.status}</span>
            <button class="btn-card" onclick="alert('Viewing academic transcript for: ${s.name} (${s.id})')">
              View Profile
            </button>
          </div>
        </div>
      </article>
    `).join("");
  }

  function renderFaqs(items) {
    dataContainer.innerHTML = items.map((f) => `
      <div class="faq-box" style="grid-column: 1 / -1;">
        <h4><i class="fa-solid fa-circle-question" style="color:var(--secondary);"></i> ${f.question}</h4>
        <p style="margin-top:8px; line-height:1.6;">${f.answer}</p>
        <div style="margin-top:10px; font-size:12px; color:var(--text-muted);">
          <span class="pill-tag" style="font-size:11px;">Category: ${f.category}</span>
        </div>
      </div>
    `).join("");
  }

  function renderPaginationControls(totalPages) {
    prevPageBtn.disabled = currentPage <= 1;
    nextPageBtn.disabled = currentPage >= totalPages || totalPages === 0;

    pageNumbers.innerHTML = "";
    for (let i = 1; i <= totalPages; i++) {
      const btn = document.createElement("button");
      btn.className = `page-num ${i === currentPage ? "active" : ""}`;
      btn.textContent = i;
      btn.addEventListener("click", () => {
        currentPage = i;
        renderView();
        window.scrollTo({ top: 350, behavior: "smooth" });
      });
      pageNumbers.appendChild(btn);
    }
  }

  // ==========================================
  // 5. EVENT LISTENERS
  // ==========================================
  tabs.forEach((tab) => {
    tab.addEventListener("click", function () {
      tabs.forEach((t) => t.classList.remove("active"));
      tab.classList.add("active");
      searchInput.value = "";
      clearSearchBtn.style.display = "none";
      loadData(tab.getAttribute("data-dataset"));
    });
  });

  searchInput.addEventListener("input", function () {
    clearSearchBtn.style.display = searchInput.value ? "block" : "none";
    applyFilterAndSort();
  });

  clearSearchBtn.addEventListener("click", function () {
    searchInput.value = "";
    clearSearchBtn.style.display = "none";
    applyFilterAndSort();
  });

  categoryFilter.addEventListener("change", applyFilterAndSort);
  sortFilter.addEventListener("change", applyFilterAndSort);

  pageSizeSelect.addEventListener("change", function () {
    pageSize = parseInt(pageSizeSelect.value, 10);
    currentPage = 1;
    renderView();
  });

  prevPageBtn.addEventListener("click", function () {
    if (currentPage > 1) {
      currentPage--;
      renderView();
    }
  });

  nextPageBtn.addEventListener("click", function () {
    const totalPages = Math.ceil(filteredData.length / pageSize);
    if (currentPage < totalPages) {
      currentPage++;
      renderView();
    }
  });

  if (retryBtn) retryBtn.addEventListener("click", () => loadData(currentDataset));

  // ==========================================
  // 6. DEPENDENT DROPDOWNS LOGIC
  // ==========================================
  deptSelect.addEventListener("change", function () {
    const dept = deptSelect.value;
    semSelect.innerHTML = '<option value="">-- All Semesters --</option>';
    specSelect.innerHTML = '<option value="">-- All Specializations --</option>';

    if (dept && departmentData[dept]) {
      semSelect.disabled = false;
      departmentData[dept].semesters.forEach((sem) => {
        const opt = document.createElement("option");
        opt.value = sem;
        opt.textContent = sem;
        semSelect.appendChild(opt);
      });

      specSelect.disabled = false;
      departmentData[dept].specializations.forEach((spec) => {
        const opt = document.createElement("option");
        opt.value = spec;
        opt.textContent = spec;
        specSelect.appendChild(opt);
      });
    } else {
      semSelect.disabled = true;
      specSelect.disabled = true;
    }
    applyFilterAndSort();
  });

  semSelect.addEventListener("change", applyFilterAndSort);
  specSelect.addEventListener("change", applyFilterAndSort);

  resetDependentBtn.addEventListener("click", function () {
    deptSelect.value = "";
    semSelect.innerHTML = '<option value="">-- Select Semester --</option>';
    semSelect.disabled = true;
    specSelect.innerHTML = '<option value="">-- Select Specialization --</option>';
    specSelect.disabled = true;
    applyFilterAndSort();
  });

  // Initial Load
  loadData("events");

});
