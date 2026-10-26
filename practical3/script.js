document.addEventListener('DOMContentLoaded', () => {
  const toggleButton = document.querySelector('.nav-toggle');
  const navList = document.querySelector('.nav-list');

  if (toggleButton && navList) {
    toggleButton.addEventListener('click', () => {
      const isOpen = navList.classList.toggle('open');
      toggleButton.setAttribute('aria-expanded', String(isOpen));
    });
  }
});
