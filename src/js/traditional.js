const traditionalList = document.querySelector(".p-traditional__list");
const featurePointGroups = document.querySelectorAll(".p-feature__item-points");
const featurePoints = document.querySelectorAll(".p-feature__item-point");
const slideInLeftTargets = document.querySelectorAll(".js-slide-in-left");
const dropInTargets = document.querySelectorAll(".js-drop-in");
const priceCheckGroups = document.querySelectorAll(".js-price-checks");

const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
const isMobileViewport = window.matchMedia("(max-width: 767px)").matches;

const observeOnce = (element, options = {}) => {
  if (!element) return;

  if (prefersReducedMotion) {
    element.classList.add("is-visible");
    return;
  }

  const observer = new IntersectionObserver(
    (entries, obs) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        entry.target.classList.add("is-visible");
        obs.unobserve(entry.target);
      });
    },
    {
      threshold: options.threshold ?? 0.25,
      rootMargin: options.rootMargin ?? "0px 0px -10% 0px",
    }
  );

  observer.observe(element);
};

const revealDropInTargets = () => {
  dropInTargets.forEach((target) => {
    if (target.classList.contains("is-visible")) return;

    const rect = target.getBoundingClientRect();
    const hasScrolledEnough = window.scrollY > 80;
    const isReadyToReveal = rect.top <= window.innerHeight * 0.82;

    if (!hasScrolledEnough || !isReadyToReveal) return;

    target.classList.add("is-visible");
  });
};

observeOnce(traditionalList, isMobileViewport ? {
  threshold: 0,
  rootMargin: "0px 0px -8% 0px",
} : {});
if (isMobileViewport) {
  featurePointGroups.forEach((group) => {
    if (prefersReducedMotion) {
      group.querySelectorAll(".p-feature__item-point").forEach((p) => p.classList.add("is-visible"));
      return;
    }

    const observer = new IntersectionObserver(
      (entries, obs) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;

          entry.target.querySelectorAll(".p-feature__item-point").forEach((point, i) => {
            setTimeout(() => {
              point.classList.add("is-visible");
            }, i * 200);
          });
          obs.unobserve(entry.target);
        });
      },
      { threshold: 0, rootMargin: "0px 0px -8% 0px" }
    );

    observer.observe(group);
  });
} else {
  featurePointGroups.forEach((group) => {
    observeOnce(group);
  });
}
slideInLeftTargets.forEach((target) => {
  observeOnce(target);
});
priceCheckGroups.forEach((group) => {
  observeOnce(group, {
    threshold: 0.35,
    rootMargin: "0px 0px -16% 0px",
  });
});

if (prefersReducedMotion) {
  dropInTargets.forEach((target) => {
    target.classList.add("is-visible");
  });
} else if (dropInTargets.length > 0) {
  window.addEventListener("scroll", revealDropInTargets, { passive: true });
  window.addEventListener("load", revealDropInTargets);
  revealDropInTargets();
}
