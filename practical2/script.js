document.addEventListener('DOMContentLoaded', () => {
  const nav = document.querySelector('.nav');
  const menuButton = document.querySelector('.menu-toggle');

  if (menuButton && nav) {
    menuButton.addEventListener('click', () => {
      nav.classList.toggle('nav-open');
      const expanded = menuButton.getAttribute('aria-expanded') === 'true';
      menuButton.setAttribute('aria-expanded', String(!expanded));
    });
  }

  const faqItems = document.querySelectorAll('.faq-item');

  faqItems.forEach((item) => {
    const button = item.querySelector('.faq-question');
    const answer = item.querySelector('.faq-answer');

    if (!button || !answer) return;

    if (item.classList.contains('active')) {
      answer.style.maxHeight = answer.scrollHeight + 'px';
      button.setAttribute('aria-expanded', 'true');
    } else {
      answer.style.maxHeight = '0px';
      button.setAttribute('aria-expanded', 'false');
    }

    button.addEventListener('click', () => {
      const isOpen = item.classList.contains('active');

      faqItems.forEach((faqItem) => {
        const faqButton = faqItem.querySelector('.faq-question');
        const faqAnswer = faqItem.querySelector('.faq-answer');

        if (!faqButton || !faqAnswer) return;

        faqItem.classList.remove('active');
        faqButton.setAttribute('aria-expanded', 'false');
        faqAnswer.style.maxHeight = '0px';
      });

      if (!isOpen) {
        item.classList.add('active');
        button.setAttribute('aria-expanded', 'true');
        answer.style.maxHeight = answer.scrollHeight + 'px';
      }
    });
  });
});
