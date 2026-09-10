const REVEAL_SELECTOR = [
  '.section',
  '.program-card',
  '.feature-list article',
  '.steps-grid article',
  '.benefits article',
  '.school-card',
].join(',');

export const initializeScrollReveal = (router) => {
  if (!('IntersectionObserver' in window)
    || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return;
  }

  document.documentElement.classList.add('scroll-reveal-ready');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('is-revealed');
      observer.unobserve(entry.target);
    });
  }, {
    threshold: 0.12,
    rootMargin: '0px 0px -40px',
  });

  const observePage = () => {
    document.querySelectorAll(REVEAL_SELECTOR).forEach((element, index) => {
      if (element.closest('[data-no-scroll-reveal]') || element.classList.contains('scroll-reveal')) return;
      element.classList.add('scroll-reveal');
      element.style.setProperty('--reveal-delay', `${Math.min(index % 4, 3) * 70}ms`);
      observer.observe(element);
    });
  };

  router.isReady().then(() => requestAnimationFrame(observePage));
  router.afterEach(() => requestAnimationFrame(() => requestAnimationFrame(observePage)));
};
