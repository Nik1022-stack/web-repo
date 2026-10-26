document.addEventListener('DOMContentLoaded', () => {
  const menuToggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.nav');

  if (menuToggle && nav) {
    menuToggle.addEventListener('click', () => {
      nav.classList.toggle('open');
    });
  }

  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav a').forEach((link) => {
    const href = link.getAttribute('href');
    if (href === currentPage) {
      link.classList.add('active');
    }
  });

  const filters = document.querySelectorAll('.filter-btn');
  const courseCards = document.querySelectorAll('.course-card');

  filters.forEach((button) => {
    button.addEventListener('click', () => {
      const category = button.dataset.filter;

      filters.forEach((item) => item.classList.toggle('active', item === button));

      courseCards.forEach((card) => {
        const match = category === 'all' || card.dataset.courseCategory === category;
        card.classList.toggle('hidden', !match);
      });
    });
  });

  const form = document.querySelector('[data-contact-form]');
  if (form) {
    form.addEventListener('submit', (event) => {
      event.preventDefault();

      const name = form.querySelector('#name');
      const email = form.querySelector('#email');
      const subject = form.querySelector('#subject');
      const message = form.querySelector('#message');
      const result = form.querySelector('.form-msg');

      if (!name.value.trim() || !email.value.trim() || !subject.value.trim() || !message.value.trim()) {
        result.textContent = 'Please fill in all fields before submitting.';
        result.className = 'form-msg show error';
        return;
      }

      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(email.value.trim())) {
        result.textContent = 'Please enter a valid email address.';
        result.className = 'form-msg show error';
        return;
      }

      result.textContent = 'Thank you! Your message has been sent successfully.';
      result.className = 'form-msg show success';
      form.reset();
    });
  }

  const faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach((item) => {
    const button = item.querySelector('.faq-question');
    if (!button) return;

    button.addEventListener('click', () => {
      const isOpen = item.classList.contains('open');
      faqItems.forEach((faq) => faq.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    });
  });

  const noteSearch = document.querySelector('[data-note-search]');
  const noteItems = document.querySelectorAll('[data-note-item]');

  if (noteSearch) {
    noteSearch.addEventListener('input', () => {
      const term = noteSearch.value.trim().toLowerCase();

      noteItems.forEach((item) => {
        const text = item.textContent.toLowerCase();
        item.classList.toggle('hidden', term && !text.includes(term));
      });
    });
  }

  const galleryItems = document.querySelectorAll('[data-gallery-item]');
  const lightbox = document.querySelector('.lightbox');
  const lightboxImage = lightbox?.querySelector('img');
  const closeButton = document.querySelector('.lightbox-close');

  galleryItems.forEach((item) => {
    item.addEventListener('click', () => {
      if (!lightbox || !lightboxImage) return;
      lightboxImage.src = item.dataset.galleryItem;
      lightbox.classList.add('show');
      lightbox.setAttribute('aria-hidden', 'false');
    });
  });

  closeButton?.addEventListener('click', () => {
    lightbox.classList.remove('show');
    lightbox.setAttribute('aria-hidden', 'true');
  });

  lightbox?.addEventListener('click', (event) => {
    if (event.target === lightbox) {
      lightbox.classList.remove('show');
      lightbox.setAttribute('aria-hidden', 'true');
    }
  });
});
