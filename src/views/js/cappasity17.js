/**
{LICENSE_PLACEHOLDER}
*/

// --- requestAnimationFrame polyfill start ---
// requestAnimationFrame polyfill by Erik Möller. fixes from Paul Irish and Tino Zijdel
// MIT license
(function() {
  var lastTime = 0;
  var vendors = ['ms', 'moz', 'webkit', 'o'];
  for(var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
    window.requestAnimationFrame = window[vendors[x]+'RequestAnimationFrame'];
    window.cancelAnimationFrame = window[vendors[x]+'CancelAnimationFrame']
      || window[vendors[x]+'CancelRequestAnimationFrame'];
  }

  if (!window.requestAnimationFrame)
    window.requestAnimationFrame = function(callback, element) {
      var currTime = new Date().getTime();
      var timeToCall = Math.max(0, 16 - (currTime - lastTime));
      var id = window.setTimeout(function() { callback(currTime + timeToCall); },
        timeToCall);
      lastTime = currTime + timeToCall;
      return id;
    };

  if (!window.cancelAnimationFrame)
    window.cancelAnimationFrame = function(id) {
      clearTimeout(id);
    };
}());
// --- requestAnimationFrame polyfill end ---

// --------------------------------
$(document).ready(function() {
  var settingsId = 'cappasity-player-settings';
  var playerUrl = 'https://{API_HOST_PLACEHOLDER}/api/player';

  var $iframes = {};
  var $iframesArr = [];

  var $iframesModal = {};
  var $iframesModalArr = [];

  function thumbId(alt) {
    return 'thumbCpst:' + alt;
  }

  function modalId(alt) {
    return 'modalCpst:' + alt;
  }

  function getBigImageContainer() {
    return $('.js-qv-product-cover').eq(0).parent();
  }

  function getModalImageContainer() {
    var $img = $('img.js-modal-product-cover').eq(0);

    if ($img.parent().attr('id') !== 'cappasity-modal-wrapper') {
      var $wrapper = $('<div />', { id: 'cappasity-modal-wrapper' })
        .css({ position: 'relative' });

      $img.wrap($wrapper);
    }

    return $img.parent();
  }

  function generateIframe(id, alt, settings) {
    return $('<iframe />', {
      id: thumbId(alt),
      src: playerUrl + '/' + id + '/embedded?' + $.param(settings),
      frameborder: 0,
      allowfullscreen: true,
      height: '100%',
      width: '100%'
    }).css({
      display: 'none',
      zIndex: 1000,
      position: 'absolute',
      top: 0
    });
  }

  function generateModalIframe(id, alt, settings) {
    return $('<div />', { id: modalId(alt) })
      .css({
        display: 'none',
        position: 'absolute',
        top: 0,
        bottom: 0,
        left: 0,
        right: 0,
        zIndex: 1
      })
      .append($('<iframe />', {
        src: playerUrl + '/' + id + '/embedded?' + $.param(settings),
        frameborder: 0,
        allowfullscreen: true,
        height: '100%',
        width: '100%'
      })
        .css({
          zIndex: 1,
          position: 'absolute',
          top: 0
        })
      );

  }

  function handleThumb(el, settings) {
    var alt = el.getAttribute('alt');
    var modelId = alt.replace('cappasity:', '');

    if (!$iframes[alt]) {
      $iframesArr.push($iframes[alt] = generateIframe(modelId, alt, settings));
    }

    if (!$iframesModal[alt]) {
      $iframesModalArr.push($iframesModal[alt] = generateModalIframe(modelId, alt, settings));
    }
  }

  function handleThumbClick(ev) {
    var alt = ev.target.getAttribute('alt');
    var $iframe = $iframes[alt];
    var $bigImgContainer = getBigImageContainer();

    if ($iframe) {
      if (!$bigImgContainer.find($iframe).length) {
        $bigImgContainer.prepend($iframe);
      }

      $iframesArr.forEach(function ($el) {
        if ($el === $iframe) {
          $el.css({ display: 'block' });
        } else {
          $el.css({ display: 'none' });
        }
      });
    } else {
      $iframesArr.forEach(function ($el) {
        $el.css({ display: 'none' });
      });
    }
  }

  function handleModalThumbClick(ev) {
    var alt = ev.target.getAttribute('alt');
    var $iframe = $iframesModal[alt];

    if ($iframe) {
      requestAnimationFrame(function () {
        var $modalImgContainer = getModalImageContainer();

        if (!$modalImgContainer.find($iframe).length) {
          $modalImgContainer.prepend($iframe);
        }

        $iframesModalArr.forEach(function ($el) {
          if ($el === $iframe) {
            $el.css({ display: 'block' })
          } else {
            $el.css({ display: 'none' });
          }
        });
      });
    } else {
      requestAnimationFrame(function () {
        $iframesModalArr.forEach(function ($el) {
          $el.css({ display: 'none' });
        });
      });
    }
  }

  function reset() {
    $iframes = {}
    $iframesArr = [];

    $iframesModal = {};
    $iframesModalArr = [];
  }

  function run() {
    var $bigImgContainer = getBigImageContainer();
    var $modalImgContainer = getModalImageContainer();

    var playerSettings = $('#' + settingsId).data('embed');
    var $cappasityThumbs = $('img.js-thumb[alt^="cappasity:"]');

    if (!playerSettings || !$cappasityThumbs.length) {
      return;
    }

    $cappasityThumbs.each(function (_, el) { handleThumb(el, playerSettings )});

    var alt = $('img.js-thumb.selected').eq(0).attr('alt');
    $iframesArr.forEach(function ($iframe) {
      $iframe.css({ display: $iframe === $iframes[alt] ? 'display' : 'none' });
      if (!$bigImgContainer.find($iframe).length) {
        $bigImgContainer.prepend($iframe);
      }
    });
    $iframesModalArr.forEach(function ($modalIframe) {
      if (!$modalImgContainer.find($modalIframe).length) {
        $modalImgContainer.prepend($modalIframe);
      }
    });

    $('.js-thumb')
      .off('click', handleThumbClick)
      .on('click', handleThumbClick);
    $('body')
      .off('click', '.js-modal-thumb', handleModalThumbClick)
      .on('click', '.js-modal-thumb', handleModalThumbClick);
  }

  window.cappasity = { run: run, reset: reset };
  run();
});
