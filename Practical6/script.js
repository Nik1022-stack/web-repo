const state = {
  events: [],
  filteredEvents: [],
  currentPage: 1,
  pageSize: 6,
  searchTerm: '',
  category: 'all',
  sortBy: 'date-asc',
  cachedData: null,
};

const elements = {
  statusMessage: document.getElementById('statusMessage'),
  eventsList: document.getElementById('eventsList'),
  searchInput: document.getElementById('searchInput'),
  categoryFilter: document.getElementById('categoryFilter'),
  sortSelect: document.getElementById('sortSelect'),
  pagination: document.getElementById('pagination'),
};

const setStatus = (message, isError = false) => {
  elements.statusMessage.textContent = message;
  elements.statusMessage.classList.toggle('error', isError);
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-IN', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(date);
};

const getSavedData = () => {
  try {
    const cached = localStorage.getItem('campus-events-cache');
    return cached ? JSON.parse(cached) : null;
  } catch (error) {
    return null;
  }
};

const saveDataToCache = (data) => {
  try {
    localStorage.setItem('campus-events-cache', JSON.stringify(data));
  } catch (error) {
    console.warn('Unable to cache data:', error);
  }
};

const populateCategoryFilter = () => {
  const categories = [...new Set(state.events.map((event) => event.category))].sort();

  elements.categoryFilter.innerHTML = '<option value="all">All categories</option>' +
    categories
      .map((category) => `<option value="${category}">${category}</option>`)
      .join('');
};

const applyFiltersAndSort = () => {
  let result = [...state.events];

  if (state.searchTerm) {
    const term = state.searchTerm.toLowerCase();
    result = result.filter((event) => {
      const searchableText = `${event.title} ${event.category} ${event.venue}`.toLowerCase();
      return searchableText.includes(term);
    });
  }

  if (state.category !== 'all') {
    result = result.filter((event) => event.category === state.category);
  }

  switch (state.sortBy) {
    case 'title-asc':
      result.sort((a, b) => a.title.localeCompare(b.title));
      break;
    case 'title-desc':
      result.sort((a, b) => b.title.localeCompare(a.title));
      break;
    case 'date-desc':
      result.sort((a, b) => new Date(b.date) - new Date(a.date));
      break;
    case 'date-asc':
    default:
      result.sort((a, b) => new Date(a.date) - new Date(b.date));
      break;
  }

  state.filteredEvents = result;
  state.currentPage = 1;
  render();
};

const renderEvents = () => {
  const startIndex = (state.currentPage - 1) * state.pageSize;
  const paginatedData = state.filteredEvents.slice(startIndex, startIndex + state.pageSize);

  if (paginatedData.length === 0) {
    elements.eventsList.innerHTML = `
      <div class="empty-state">
        <h3>No events found</h3>
        <p>Try a different search term or category filter.</p>
      </div>
    `;
    return;
  }

  elements.eventsList.innerHTML = paginatedData
    .map(
      (event) => `
        <article class="event-card">
          <img class="event-image" src="${event.image}" alt="${event.title}" />
          <div class="event-body">
            <div class="event-badges">
              <span class="category-pill">${event.category}</span>
              <span class="event-date">${formatDate(event.date)}</span>
            </div>
            <h3>${event.title}</h3>
            <p>${event.description}</p>
            <div class="meta">
              <span>📍 ${event.venue}</span>
              <span>🎯 ${event.category}</span>
            </div>
          </div>
        </article>
      `
    )
    .join('');
};

const renderPagination = () => {
  const totalPages = Math.max(1, Math.ceil(state.filteredEvents.length / state.pageSize));
  const buttons = [];

  buttons.push(`
    <button class="page-btn" data-page="prev" ${state.currentPage === 1 ? 'disabled' : ''}>Prev</button>
  `);

  for (let page = 1; page <= totalPages; page += 1) {
    buttons.push(`
      <button class="page-btn ${page === state.currentPage ? 'active' : ''}" data-page="${page}">${page}</button>
    `);
  }

  buttons.push(`
    <button class="page-btn" data-page="next" ${state.currentPage === totalPages ? 'disabled' : ''}>Next</button>
  `);

  elements.pagination.innerHTML = buttons.join('');

  elements.pagination.querySelectorAll('.page-btn').forEach((button) => {
    button.addEventListener('click', () => {
      const target = button.dataset.page;
      if (target === 'prev') {
        state.currentPage = Math.max(1, state.currentPage - 1);
      } else if (target === 'next') {
        state.currentPage = Math.min(totalPages, state.currentPage + 1);
      } else {
        state.currentPage = Number(target);
      }
      render();
    });
  });
};

const render = () => {
  renderEvents();
  renderPagination();
};

const fetchEvents = async () => {
  const cachedData = getSavedData();

  if (cachedData && Array.isArray(cachedData) && cachedData.length) {
    state.events = cachedData;
    populateCategoryFilter();
    applyFiltersAndSort();
    setStatus('Showing cached event data.');
  }

  try {
    setStatus('Loading events...');
    const response = await fetch('./data/events.json');

    if (!response.ok) {
      throw new Error(`Network error: ${response.status}`);
    }

    const data = await response.json();
    state.events = data;
    saveDataToCache(data);
    populateCategoryFilter();
    applyFiltersAndSort();
    setStatus(`Loaded ${data.length} events successfully.`);
  } catch (error) {
    if (!state.events.length) {
      setStatus('Unable to load event data. Please check the JSON file.', true);
    } else {
      setStatus('Showing cached data because the live data could not be fetched.', true);
    }
    console.error(error);
  }
};

elements.searchInput.addEventListener('input', (event) => {
  state.searchTerm = event.target.value.trim();
  applyFiltersAndSort();
});

elements.categoryFilter.addEventListener('change', (event) => {
  state.category = event.target.value;
  applyFiltersAndSort();
});

elements.sortSelect.addEventListener('change', (event) => {
  state.sortBy = event.target.value;
  applyFiltersAndSort();
});

fetchEvents();
