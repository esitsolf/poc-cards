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
    updateProgressBars();
    updateListCards();

    if (isDone) {
      var card = block.closest('.flip-card, .poc-list-card');
      var bothDone = card &&
        card.querySelector('.poc-action-block.silver.poc-done') &&
        card.querySelector('.poc-action-block.gold.poc-done');
      if (bothDone) {
        showPopup('both');
      } else {
        showPopup(block.classList.contains('silver') ? 'silver' : 'gold');
      }
    }
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

  // ── Progress bars ──────────────────────────────────────────────
  function updateProgressBars() {
    document.querySelectorAll('.poc-progress-wrap').forEach(function (wrap) {
      var total = parseInt(wrap.dataset.total, 10) || 0;
      if (total === 0) return;
      var ids;
      try { ids = JSON.parse(wrap.dataset.postIds); } catch (e) { return; }

      var silverDone = 0, goldDone = 0;
      ids.forEach(function (id) {
        try {
          if (localStorage.getItem('poc_card_' + id + '_silver') === '1') silverDone++;
          if (localStorage.getItem('poc_card_' + id + '_gold')   === '1') goldDone++;
        } catch (e) {}
      });

      var silverPct = Math.round(silverDone / total * 100);
      var goldPct   = Math.round(goldDone   / total * 100);

      var fillS = wrap.querySelector('.poc-fill-silver');
      var fillG = wrap.querySelector('.poc-fill-gold');
      var cntS  = wrap.querySelector('.poc-pr-count-silver');
      var cntG  = wrap.querySelector('.poc-pr-count-gold');

      if (fillS) fillS.style.width = silverPct + '%';
      if (fillG) fillG.style.width = goldPct   + '%';
      if (cntS)  cntS.textContent  = silverDone + ' von ' + total + ' erledigt';
      if (cntG)  cntG.textContent  = goldDone   + ' von ' + total + ' erledigt';
    });
  }

  updateProgressBars();

  // ── List card states ───────────────────────────────────────────
  function updateListCards() {
    document.querySelectorAll('.poc-list-card').forEach(function (card) {
      var sDone = !!card.querySelector('.poc-action-block.silver.poc-done');
      var gDone = !!card.querySelector('.poc-action-block.gold.poc-done');
      card.classList.remove('state-silver', 'state-gold', 'state-both');
      if (sDone && gDone) card.classList.add('state-both');
      else if (gDone)     card.classList.add('state-gold');
      else if (sDone)     card.classList.add('state-silver');
    });
  }

  updateListCards();

  // ── Completion popup ───────────────────────────────────────────
  (function injectPopup() {
    if (document.getElementById('poc-popup-overlay')) return;
    var el = document.createElement('div');
    el.id = 'poc-popup-overlay';
    el.className = 'poc-popup-overlay';
    el.innerHTML =
      '<div class="poc-popup-box">' +
        '<div class="poc-popup-icon" id="poc-popup-icon"></div>' +
        '<h2 class="poc-popup-title" id="poc-popup-title"></h2>' +
        '<p class="poc-popup-body" id="poc-popup-body"></p>' +
        '<button class="poc-popup-btn" id="poc-popup-btn"></button>' +
      '</div>';
    document.body.appendChild(el);
    // Close when clicking the overlay backdrop
    el.addEventListener('click', function (e) {
      if (e.target === el) closePopup();
    });
    el.style.display = 'none';
  }());

  function showPopup(type) {
    var overlay = document.getElementById('poc-popup-overlay');
    var icon    = document.getElementById('poc-popup-icon');
    var title   = document.getElementById('poc-popup-title');
    var body    = document.getElementById('poc-popup-body');
    var btn     = document.getElementById('poc-popup-btn');
    if (!overlay) return;

    if (type === 'silver') {
      icon.textContent = '👣';
      title.textContent = 'Fußabdruck gesetzt – gut gemacht!';
      body.innerHTML = 'Wandel beginnt im Gehen. Jetzt bist du einen Schritt weiter!<br><br><strong>Schaffst du auch den Handabdruck?</strong>';
      btn.textContent = 'Weiter geht\'s!';
      btn.onclick = function () { closePopup(); };
    } else if (type === 'gold') {
      icon.textContent = "🖐️";
      title.textContent = 'Handabdruck hinterlassen – stark!';
      body.innerHTML = 'Du hast andere mitgenommen. Das ist der Moment, wo aus einer persönlichen Entscheidung etwas Gemeinsames wird. Was du gerade getan hast, wirkt weit über dich hinaus.';
      btn.textContent = 'Weiter geht\'s!';
      btn.onclick = function () {
        closePopup();
        if (current < visible.length - 1) goTo(current + 1);
      };
    } else {
      icon.textContent = '⭐⭐⭐⭐⭐';
      title.textContent = 'Toll, du gehörst zu den 1% der wirksamsten Spieler:innen.';
      body.innerHTML = 'Du hast sowohl den Fuß- als auch den Handabdruck gesetzt. Das ist außergewöhnlich!';
      btn.textContent = 'Weiter geht\'s!';
      btn.onclick = function () {
        closePopup();
        if (current < visible.length - 1) goTo(current + 1);
      };
    }
    overlay.classList.add('poc-popup-open');
    overlay.style.display = 'flex';
  }

  function closePopup() {
    var overlay = document.getElementById('poc-popup-overlay');
    if (overlay) overlay.style.display = 'none';
  }

  // ── Card navigation ────────────────────────────────────────────
  var track   = document.querySelector('.poc-cards-track');
  var counter = document.querySelector('.poc-nav-counter');
  var btnPrev = document.querySelector('.poc-nav-prev');
  var btnNext = document.querySelector('.poc-nav-next');

  // visible / current are only meaningful when [poc_cards] is on the page
  var visible = [];
  var current = 0;

  function goTo(index) {
    if (!track || index < 0 || index >= visible.length) return;
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

  if (track) {
    var cards = Array.from(track.children).filter(function (el) {
      return el.classList.contains('flip-card');
    });
    visible = cards.slice();
    goTo(0);
    if (btnPrev) btnPrev.addEventListener('click', function () { goTo(current - 1); });
    if (btnNext) btnNext.addEventListener('click', function () { goTo(current + 1); });
  }

  // ── List category filter ────────────────────────────────────────
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.poc-list-filter-bar .poc-filter-btn');
    if (!btn) return;

    var wrap   = btn.closest('.poc-list-wrap');
    var filter = btn.dataset.filter;

    btn.closest('.poc-list-filter-bar').querySelectorAll('.poc-filter-btn').forEach(function (b) {
      b.classList.toggle('poc-filter-active', b === btn);
    });

    if (wrap) {
      wrap.querySelectorAll('.poc-list-card').forEach(function (card) {
        var cat  = card.dataset.cat || '';
        var show = filter === 'all' || cat === filter;
        card.style.display = show ? '' : 'none';
      });
    }
  });

  // ── Simplified list row click → navigate to full card ─────────────────────
  document.addEventListener('click', function (e) {
    var row = e.target.closest('.poc-list-card');
    if (!row) return;
    // Don't navigate when the user is interacting with a toggle
    if (e.target.closest('.poc-toggle-btn')) return;

    var postId     = row.dataset.postId;
    var list       = row.closest('.poc-list');
    var fullTabSel = list ? list.dataset.fullTab : '';
    var fullUrl    = list ? list.dataset.fullUrl  : '';

    // Cross-page navigation: redirect to the cards page with ?poc_card=POST_ID
    if (fullUrl && postId) {
      var sep = fullUrl.indexOf('?') === -1 ? '?' : '&';
      window.location.href = fullUrl + sep + 'poc_card=' + encodeURIComponent(postId);
      return;
    }

    // Same-page: activate the tab that contains [poc_cards] if a selector was provided
    if (fullTabSel) {
      var tabEl = document.querySelector(fullTabSel);
      if (tabEl) tabEl.click();
    }

    if (postId) {
      document.dispatchEvent(new CustomEvent('poc:gotocard', { detail: { postId: postId } }));
    }
  });

  // ── Respond to poc:gotocard event ─────────────────────────────────────────
  document.addEventListener('poc:gotocard', function (e) {
    var postId = e.detail && e.detail.postId;
    if (!postId || !track) return;

    var targetCard = document.getElementById('card-' + postId);
    if (!targetCard) return;

    var visIdx = visible.indexOf(targetCard);
    if (visIdx >= 0) goTo(visIdx);

    // Scroll to the top of the cards section
    var navWrapper = document.querySelector('.poc-nav-wrapper');
    var scrollTarget = navWrapper || track;
    if (scrollTarget) {
      scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });

  // ── Auto-navigate on page load if ?poc_card=POST_ID is in the URL ──────────
  (function () {
    var params  = new URLSearchParams(window.location.search);
    var postId  = params.get('poc_card');
    if (!postId) return;
    // Use a small delay to let the layout settle
    setTimeout(function () {
      document.dispatchEvent(new CustomEvent('poc:gotocard', { detail: { postId: postId } }));
    }, 100);
  }());

});

