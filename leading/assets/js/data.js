(function () {
  'use strict';

  let currentSheets = [];
  let activeSheet = null;
  let currentHeaders = [];
  let currentRows = [];      // semua baris dari sheet aktif (belum difilter)
  let filteredRows = [];     // baris setelah difilter pencarian
  let searchTimer = null;

  const tabsEl = document.getElementById('sheetTabs');
  const tableContainer = document.getElementById('tableContainer');
  const searchInput = document.getElementById('searchInput');
  const rowCountPill = document.getElementById('rowCountPill');
  const downloadBtn = document.getElementById('downloadBtn');

  function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function formatCellValue(value) {
    if (value === null || value === undefined || value === '') return '';
    const str = String(value);
    // Deteksi ISO date string dari Apps Script, format jadi tanggal singkat
    const isoMatch = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/.test(str);
    if (isoMatch) {
      const d = new Date(str);
      if (!isNaN(d.getTime())) {
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
      }
    }
    return str;
  }

  function statusBadgeClass(value) {
    const v = String(value).toUpperCase();
    if (v === 'ASSIGNED') return 'assigned';
    if (v === 'OLD') return 'old';
    return 'default';
  }

  function renderTabs() {
    tabsEl.innerHTML = '';
    currentSheets.forEach((sheetName, idx) => {
      const li = document.createElement('li');
      li.className = 'nav-item';
      li.setAttribute('role', 'presentation');

      const btn = document.createElement('button');
      btn.className = 'nav-link' + (idx === 0 ? ' active' : '');
      btn.type = 'button';
      btn.textContent = sheetName;
      btn.addEventListener('click', () => switchSheet(sheetName, btn));

      li.appendChild(btn);
      tabsEl.appendChild(li);
    });
  }

  function setActiveTabButton(clickedBtn) {
    tabsEl.querySelectorAll('.nav-link').forEach(b => b.classList.remove('active'));
    if (clickedBtn) clickedBtn.classList.add('active');
  }

  function renderLoadingTable() {
    tableContainer.innerHTML = '<div class="spinner-wrap"><i class="bi bi-hourglass-split"></i> Memuat data...</div>';
  }

  function renderEmptyState(message) {
    tableContainer.innerHTML = `
      <div class="empty-state">
        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
        <p class="mt-2 mb-0">${escapeHtml(message)}</p>
      </div>`;
  }

  function renderTable() {
    if (!currentHeaders.length) {
      renderEmptyState('Tidak ada kolom data pada sheet ini.');
      rowCountPill.textContent = '0 baris';
      return;
    }

    if (!filteredRows.length) {
      renderEmptyState('Tidak ada data yang cocok dengan pencarian.');
      rowCountPill.textContent = '0 baris';
      return;
    }

    const visibleHeaders = currentHeaders.filter(h => h.trim() !== '');

    let html = '<table class="data-table"><thead><tr>';
    visibleHeaders.forEach(h => {
      html += `<th>${escapeHtml(h)}</th>`;
    });
    html += '</tr></thead><tbody>';

    filteredRows.forEach(row => {
      html += '<tr>';
      visibleHeaders.forEach(h => {
        const raw = row[h];
        if (h.trim().toUpperCase() === 'STATUS' && raw) {
          html += `<td><span class="badge-status ${statusBadgeClass(raw)}">${escapeHtml(raw)}</span></td>`;
        } else {
          html += `<td>${escapeHtml(formatCellValue(raw))}</td>`;
        }
      });
      html += '</tr>';
    });

    html += '</tbody></table>';
    tableContainer.innerHTML = html;
    rowCountPill.textContent = filteredRows.length.toLocaleString('id-ID') + ' baris';
  }

  function applySearchFilter() {
    const q = searchInput.value.trim().toLowerCase();
    if (!q) {
      filteredRows = currentRows;
    } else {
      filteredRows = currentRows.filter(row => {
        return currentHeaders.some(h => {
          const val = row[h];
          if (val === null || val === undefined) return false;
          return String(val).toLowerCase().includes(q);
        });
      });
    }
    renderTable();
    updateDownloadLink();
  }

  function updateDownloadLink() {
    const q = encodeURIComponent(searchInput.value.trim());
    const sheet = encodeURIComponent(activeSheet || '');
    downloadBtn.href = `download.php?cat=${encodeURIComponent(CAT_SLUG)}&sheet=${sheet}&q=${q}`;
  }

  async function loadSheetData(sheetName) {
    renderLoadingTable();
    try {
      const res = await fetch(`api.php?cat=${encodeURIComponent(CAT_SLUG)}&action=data&sheet=${encodeURIComponent(sheetName)}`);
      const json = await res.json();
      if (json.status !== 'ok') {
        renderEmptyState(json.message || 'Gagal memuat data.');
        return;
      }
      currentHeaders = json.headers || [];
      currentRows = json.rows || [];
      searchInput.value = '';
      filteredRows = currentRows;
      renderTable();
      updateDownloadLink();
    } catch (err) {
      renderEmptyState('Terjadi kesalahan saat memuat data: ' + err.message);
    }
  }

  function switchSheet(sheetName, btnEl) {
    activeSheet = sheetName;
    setActiveTabButton(btnEl);
    loadSheetData(sheetName);
  }

  async function init() {
    try {
      const res = await fetch(`api.php?cat=${encodeURIComponent(CAT_SLUG)}&action=list`);
      const json = await res.json();
      if (json.status !== 'ok' || !json.sheets || !json.sheets.length) {
        tabsEl.innerHTML = '<li class="nav-item"><span class="nav-link disabled">Tidak ada sheet ditemukan</span></li>';
        renderEmptyState('Tidak ada data tersedia untuk kategori ini.');
        return;
      }
      currentSheets = json.sheets;
      renderTabs();
      activeSheet = currentSheets[0];
      loadSheetData(activeSheet);
    } catch (err) {
      tabsEl.innerHTML = '<li class="nav-item"><span class="nav-link disabled text-danger">Gagal memuat sheet</span></li>';
      renderEmptyState('Terjadi kesalahan saat memuat daftar sheet: ' + err.message);
    }
  }

  searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applySearchFilter, 200);
  });

  init();
})();
