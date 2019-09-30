/**
{LICENSE_PLACEHOLDER}
*/

$(document).ready(function () {
  var settingsId = 'cappasity-player-settings';
  var playerUrl = 'https://{API_HOST_PLACEHOLDER}/api/player';
  var previewUrl = 'https://{API_HOST_PLACEHOLDER}/api/files/preview/';
  var previewFilters = 'w80-h80-cpad-bffffff';
  var iconHref = '/modules/cappasity3d/views/img/logo-3d.jpg';
  var iconBigHref = '/modules/cappasity3d/views/img/logo-3d-thickbox.jpg';

  var playerSettings = $('#' + settingsId).data('embed');
  var $cappasityLinks = $('a[title^="cappasity:"]');
  var $thumbContainer = $('#thumbs_list');
  var $thumbList = $('ul#thumbs_list_frame');

  if (!playerSettings || !$cappasityLinks.length) {
    return;
  }

  var $embeds = {};
  var $embedsArr = [];
  var $iframesArr = [];

  if ($thumbList.length) {
    $cappasityLinks.each(function mapLinks(_, el) {
      var title = el.getAttribute('title');
      var modelId = title.replace('cappasity:', '');
      el.querySelector('img').setAttribute('src', iconHref);

      var $iframe = $('<iframe />', {
        src: playerUrl + '/' + modelId + '/embedded?' + $.param(playerSettings),
        frameborder: 0,
        allowfullscreen: true
      });
      $iframe.attr('width', playerSettings.width);
      $iframe.attr('height', playerSettings.height);

      var $embed = $('<div />', { id: title })
        .append($iframe)
        .css({
          position: 'absolute',
          top: 0,
          bottom: 0,
          left: 0,
          right: 0,
          zIndex: -1
        });

      $(el).attr({
        href: iconBigHref,
        'data-fancybox-type': 'iframe',
        'data-fancybox-width': $iframe.attr('width'),
        'data-fancybox-height': $iframe.attr('height'),
        'data-fancybox-href': $iframe.attr('src')
      });

      if (!$embeds[title]) {
        $embeds[title] = $embed;
        $embedsArr.push($embed);
        $iframesArr.push($iframe);
      }
    });

    var $viewFullSize = $('#view_full_size').eq(0);
    var hasBigPic = $('#bigpic, #view_full_size .jqzoom').eq(0).length;

    if ($viewFullSize.length) {
      $viewFullSize.css({ display: 'block', position: 'relative' });
      $embedsArr.forEach(function appendEmbed($embed) {
        $viewFullSize.append($embed);
      });
    }

    function displayImage($el) {
      var title = $el && $el.attr('title');
      var $bigPic = $('#bigpic, #view_full_size .zoomPad').eq(0);

      function setDimensions() {
        var width = $bigPic.css('width');
        var height = $bigPic.css('height');

        $iframesArr.forEach(function ($iframe) {
          $iframe.attr('width', width);
          $iframe.attr('height', height);
        })
      }

      if ($bigPic.length) {
        if (!$bigPic.width() || !$bigPic.height()) {
          var oldOnload = $bigPic.get(0).onload;

          $bigPic.get(0).onload = function () {
            setDimensions();

            $bigPic.get(0).onload = oldOnload;
            if (typeof oldOnload === 'function') {
              oldOnload.apply(this, arguments);
            }
          };
        } else {
          setDimensions();
        }

        $embedsArr.forEach(function mapEmbeds($embed) {
          $embed.css({ zIndex: title === $embed.attr('id') ? 1 : -1 })
        })
      }
    }

    if (jqZoomEnabled) {
      $cappasityLinks.each(function processRel(_, el) {
        var rel = el.getAttribute('rel');
        el.setAttribute('href', 'javascript:void(0);');

        if (rel) {
          try {
            var relObj = eval("(" + $.trim(rel) + ")");
            var newRelStr = "{gallery: '" + relObj.gallery + "', smallimage: '" + iconHref + "', largeimage: '" + iconBigHref + "'}";
            el.setAttribute('rel', newRelStr);
          } catch (e) {}
        }
      });


      $('.jqzoom').each(function() {
        var api = $(this).data('jqzoom');

        if (!api || !api.swapimage) return;

        var oldSwapImage = api.swapimage;

        api.swapimage = function(link) {
          var args = [].slice.call(arguments);
          displayImage($(link));
          oldSwapImage.apply(this, args);
        };
      });

      return;
    }

    var oldDisplayImage = window.displayImage;

    if(oldDisplayImage && $viewFullSize.length && hasBigPic) {
      window.displayImage = function ($el) {
        var args = [].slice.call(arguments);

        displayImage($el);
        oldDisplayImage.apply(window, args);
      };
    }

    refreshProductImages(0);
    $thumbContainer.trigger('goto', 0);
    displayImage($cappasityLinks.eq(0));
  }
});
