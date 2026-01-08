define([], function () {
  'use strict';

  function swap(img) {
    var src = img.getAttribute('data-src');
    if (!src) return;
    var tmp = new Image();
    tmp.onload = function () {
      img.setAttribute('src', src);
      img.removeAttribute('data-src');
      img.classList.add('gps-lazy--loaded');
    };
    tmp.src = src;
  }

  function init() {
    var imgs = Array.prototype.slice.call(document.querySelectorAll('img.gps-lazy.gps-lqip[data-src]'));
    if (!('IntersectionObserver' in window)) {
      imgs.forEach(swap);
      return;
    }
    var io = new IntersectionObserver(function(entries) {
      entries.forEach(function(e) {
        if (e.isIntersecting) {
          swap(e.target);
          io.unobserve(e.target);
        }
      });
    }, { rootMargin: '300px 0px' });

    imgs.forEach(function(img) { io.observe(img); });
  }

  return { init: init };
});
