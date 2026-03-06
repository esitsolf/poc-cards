/* POC Cards — Front-end JS */

document.addEventListener('DOMContentLoaded', function () {

  // ── Dynamic height for 3D flip cards ──────────────────────────
  // Both faces are position:absolute so the container needs an explicit height.
  function adjustFlipCardHeight(card) {
    var inner  = card.querySelector('.flip-card-inner');
    var front  = card.querySelector('.flip-card-front');
    var back   = card.querySelector('.flip-card-back');
    if (!inner || !front || !back) return;
    var activePanel = card.classList.contains('flipped') ? back : front;
    var h = activePanel.scrollHeight;
    card.style.height  = h + 'px';
    inner.style.height = h + 'px';
  }

  // Init heights after layout
  requestAnimationFrame(function () {
    document.querySelectorAll('.flip-card').forEach(adjustFlipCardHeight);
  });

  window.addEventListener('resize', function () {
    document.querySelectorAll('.flip-card').forEach(adjustFlipCardHeight);
  });

  // ── Card flip ──────────────────────────────────────────────────
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-flip-trigger]');
    if (!btn) return;
    var card = btn.closest('.flip-card');
    if (!card) return;
    card.classList.toggle('flipped');
    setTimeout(function () { adjustFlipCardHeight(card); }, 50);
  });

  // ── Toggle switch ──────────────────────────────────────────────
  function triggerUlike(ulikeId) {
    if (!ulikeId) return;
    var ulikeBtn = document.querySelector('.wp_ulike_btn[data-ulike-id="' + ulikeId + '"]');
    if (ulikeBtn) ulikeBtn.click();
  }

  document.addEventListener('click', function (e) {
    var toggleBtn = e.target.closest('.poc-toggle-btn');
    if (!toggleBtn) return;

    var block = toggleBtn.closest('.poc-action-block');
    if (!block) return;

    var isDone  = block.classList.toggle('poc-done');
    var pocId   = block.dataset.pocId;
    var ulikeId = block.dataset.ulikeId;

    if (pocId) {
      try {
        localStorage.setItem('poc_' + pocId, isDone ? '1' : '0');
      } catch (err) {}
    }

    triggerUlike(ulikeId);
  });

  // Restore toggle states from localStorage
  document.querySelectorAll('.poc-action-block').forEach(function (block) {
    var pocId = block.dataset.pocId;
    if (!pocId) return;
    try {
      if (localStorage.getItem('poc_' + pocId) === '1') {
        block.classList.add('poc-done');
      }
    } catch (err) {}
  });

  // ── Card navigation ────────────────────────────────────────────
  var track   = document.querySelector('.poc-cards-track');
  var counter = document.querySelector('.poc-nav-counter');
  var btnPrev = document.querySelector('.poc-nav-prev');
  var btnNext = document.querySelector('.poc-nav-next');

  if (!track) return;

  var cards   = Array.from(track.children).filter(function (el) {
    return el.classList.contains('flip-card');
  });

  // visible = subset of cards currently shown (changes with filter)
  var visible  = cards.slice();
  var current  = 0; // index within visible

  function goTo(index) {
    if (index < 0 || index >= visible.length) return;
    current = index;

    // Offset of the target card relative to the first visible card
    var offset = visible[current].offsetLeft - visible[0].offsetLeft;
    track.style.transform = 'translateX(-' + offset + 'px)';

    visible.forEach(function (card, i) {
      card.classList.toggle('poc-active', i === current);
    });

    if (counter) counter.textContent = 'Karte ' + (current + 1) + ' von ' + visible.length;
    if (btnPrev) btnPrev.disabled = current === 0;
    if (btnNext) btnNext.disabled = current === visible.length - 1;
  }

  goTo(0);

  if (btnPrev) btnPrev.addEventListener('click', function () { goTo(current - 1); });
  if (btnNext) btnNext.addEventListener('click', function () { goTo(current + 1); });

  // ── Category filter ────────────────────────────────────────────
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.poc-filter-btn');
    if (!btn) return;

    var filter = btn.dataset.filter;

    document.querySelectorAll('.poc-filter-btn').forEach(function (b) {
      b.classList.toggle('poc-filter-active', b === btn);
    });

    visible = [];
    cards.forEach(function (card) {
      var cat  = card.dataset.cat || '';
      var show = filter === 'all' || cat === filter;
      card.style.display = show ? '' : 'none';
      if (show) visible.push(card);
    });

    current = 0;
    goTo(0);
  });

});
