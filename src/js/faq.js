const faqButtons = document.querySelectorAll('.p-faq__question');

faqButtons.forEach((button) => {
  button.addEventListener('click', () => {
    const item = button.closest('.p-faq__item');
    const answerId = button.getAttribute('aria-controls');

    if (!item || !answerId) {
      return;
    }

    const answer = document.getElementById(answerId);

    if (!answer) {
      return;
    }

    const isOpen = item.classList.contains('is-open');

    item.classList.toggle('is-open', !isOpen);
    button.setAttribute('aria-expanded', String(!isOpen));
    answer.setAttribute('aria-hidden', String(isOpen));
  });
});
