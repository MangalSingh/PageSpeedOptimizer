define([], function () {
  'use strict';

  function swap(img) {
    var src = img.getAttribute('data-src');
    if (!src) return;
    img.setAttribute('src', src);
    img.removeAttribute('data-src');
  }

  function init() {
    var imgs = Array.prototype.slice.call(document.querySelectorAll('img.gps-lazy[data-src]'));
    var runner = function () {
      var batch = imgs.splice(0, 8);
      batch.forEach(swap);
      if (imgs.length) {
        schedule();
      }
    };

    function schedule() {
      if ('requestIdleCallback' in window) {
        requestIdleCallback(runner, { timeout: 1500 });
      } else {
        setTimeout(runner, 200);
      }
    }

    schedule();
  }

  return { init: init };
});
