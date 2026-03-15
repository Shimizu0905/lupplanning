const modal = document.querySelector('#privacy-modal');

if (modal) {
  const openButton = document.querySelector('.js-privacy-open');
  const closeButtons = modal.querySelectorAll('.js-privacy-close');
  const agreeButton = modal.querySelector('.js-privacy-agree');
  const scrollArea = modal.querySelector('.js-privacy-scroll');
  const checkbox = document.querySelector('.js-privacy-checkbox');
  const focusTarget = modal.querySelector('.p-privacy-modal__dialog');
  let lastFocusedElement = null;

  if (checkbox) {
    checkbox.checked = false;
    checkbox.disabled = true;
  }

  const unlockAgreement = () => {
    if (!agreeButton || !scrollArea) {
      return;
    }

    const reachedBottom =
      scrollArea.scrollTop + scrollArea.clientHeight >= scrollArea.scrollHeight - 4;

    if (reachedBottom) {
      agreeButton.disabled = false;
    }
  };

  const openModal = () => {
    lastFocusedElement = document.activeElement;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    agreeButton.disabled = true;
    scrollArea.scrollTop = 0;
    focusTarget.focus();
    unlockAgreement();
  };

  const closeModal = () => {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';

    if (lastFocusedElement instanceof HTMLElement) {
      lastFocusedElement.focus();
    }
  };

  openButton?.addEventListener('click', openModal);

  closeButtons.forEach((button) => {
    button.addEventListener('click', closeModal);
  });

  scrollArea?.addEventListener('scroll', unlockAgreement);

  agreeButton?.addEventListener('click', () => {
    if (!checkbox) {
      return;
    }

    checkbox.disabled = false;
    checkbox.checked = true;
    checkbox.dispatchEvent(new Event('change', { bubbles: true }));
    closeModal();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
      closeModal();
    }
  });
}
