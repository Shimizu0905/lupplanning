// FV通過時にヘッダーの高さを縮める
document.addEventListener('DOMContentLoaded', () => {
  const header = document.getElementById('header');
  const fv = document.querySelector('.p-fv');
  if (!header || !fv) return;

  const observer = new IntersectionObserver(
    ([entry]) => {
      header.classList.toggle('is-scrolled-over-fv', !entry.isIntersecting);
    },
    { threshold: 0, rootMargin: '0px 0px 0px 0px' }
  );

  observer.observe(fv);
});
