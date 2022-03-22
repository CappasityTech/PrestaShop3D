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
  // 'api.cappasity.com'
  var srcHostPart = '{API_HOST_PLACEHOLDER}'
  var settingsId = 'cappasity-player-settings';
  var playerUrl = 'https://{API_HOST_PLACEHOLDER}/api/player';

  var $iframes = {};
  var $iframesArr = [];

  var $iframesModal = {};
  var $iframesModalArr = [];

  function modalId(cappasityId) {
    return 'modalCpst:' + cappasityId;
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

  function generateIframe(cappasityId, settings) {
    return $('<iframe />', {
      src: playerUrl + '/' + cappasityId + '/embedded?' + $.param(settings),
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

  function generateModalIframe(cappasityId, settings) {
    return $('<div />', { id: modalId(cappasityId) })
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
        src: playerUrl + '/' + cappasityId + '/embedded?' + $.param(settings),
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

  function getImageSrcAttr(el) {
    return el.getAttribute('data-image-large-src');
  }

  /**
   * @param previewSrc Ex.: https://api.cappasity.com/api/files/preview/uname/w800-h800-cpad/dd596de4-ae2b-4d66-a023-242ca7d86b51.jpeg
   * @returns {*}
   */
  function parseCappasityId(previewSrc) {
    if (typeof previewSrc !== 'string' || !previewSrc.length) {
      throw new Error('Preview src expected to be a string');
    }

    return previewSrc.split('/').pop().split('.').shift();
  }

  function hasCappasityPreviewSrc(el) {
    var previewSrc = getImageSrcAttr(el);

    return (previewSrc && previewSrc.indexOf(srcHostPart) !== -1);
  }

  function isCappasityThumb(el) {
    return hasCappasityPreviewSrc(el);
  }

  function getCappasityId(el) {
      var previewSrc = getImageSrcAttr(el);

      if (!previewSrc || previewSrc.indexOf(srcHostPart) === -1) {
        throw new Error('Element is expected to be Cappasity thumbnail');
      }

      return parseCappasityId(previewSrc);
  }

  function handleThumb(el, settings) {
    var cappasityId = getCappasityId(el);

    if (!$iframes[cappasityId]) {
      $iframesArr.push($iframes[cappasityId] = generateIframe(cappasityId, settings));
    }

    if (!$iframesModal[cappasityId]) {
      $iframesModalArr.push($iframesModal[cappasityId] = generateModalIframe(cappasityId, settings));
    }
  }

  function handleThumbClick(ev) {
    if (!isCappasityThumb(ev.target)) {
      $iframesArr.forEach(function ($el) {
        $el.css({ display: 'none' });
      });

      return;
    }

    var targetEmbedId = getCappasityId(ev.target);
    var $iframe = $iframes[targetEmbedId];
    var $bigImgContainer = getBigImageContainer();

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
  }

  function handleModalThumbClick(ev) {
    var targetEmbedId = getCappasityId(ev.target);
    var $iframe = $iframesModal[targetEmbedId];

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
    $iframes = {};
    $iframesArr = [];

    $iframesModal = {};
    $iframesModalArr = [];
  }

  function run() {
    var $bigImgContainer = getBigImageContainer();
    var $modalImgContainer = getModalImageContainer();

    var playerSettings = $('#' + settingsId).data('embed');
    var $cappasityThumbs = $('img.js-thumb[data-image-large-src*="' + srcHostPart + '"]');

    if (!playerSettings || !$cappasityThumbs.length) {
      return;
    }

    $cappasityThumbs.each(function (_, el) { handleThumb(el, playerSettings )});

    var targetThumb = $('img.js-thumb.selected')[0];
    $iframesArr.forEach(function ($iframe) {
      var isTargetIframe = isCappasityThumb(targetThumb) && ($iframe === $iframes[getCappasityId(targetThumb)]);
      $iframe.css({ display: isTargetIframe ? 'block' : 'none' });
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
