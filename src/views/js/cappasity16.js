/**
{LICENSE_PLACEHOLDER}
*/
$(document).ready(function () {
  // Do not delete this line. Although this variable has no visible usages
  // it seems that Prestashop performs static analysis to decide whether to request the large image
  // Deleting this line will break the gallery
  var iconLargeHref = '/modules/cappasity3d/views/img/logo-3d-large.jpg';

  var settingsId = 'cappasity-player-settings';
  var syncedImagesId = 'cappasity-synced-images';
  var playerUrl = 'https://{API_HOST_PLACEHOLDER}/api/player';
  var iconHref = '/modules/cappasity3d/views/img/logo-3d.jpg';
  var iconBigHref = '/modules/cappasity3d/views/img/logo-3d-thickbox.jpg';
  var initialFakeId = 100000000;
  var playerSettings = $('#' + settingsId).data('embed');
  var syncedImages = $('#' + syncedImagesId).data('embed');

  if (!playerSettings || !syncedImages || !syncedImages.length) {
    return;
  }

  var syncedImagesIds = syncedImages.map(idProperty);
  var mockedImagesIds = syncedImagesIds.map(makeMockedImageId);
  var cappasityThumbIds = mockedImagesIds.map(thumbPrefix);

  var thumbIdsToModelIds = Object.create(null);
  syncedImages.reduce(function indexWithThumbId(acc, syncedImage) {
      var originalId = idProperty(syncedImage);
      var thumbId = thumbPrefix(makeMockedImageId(originalId));
      acc[thumbId] = cappasityIdProperty(syncedImage);

      return acc;
  }, thumbIdsToModelIds);

  function idProperty(image) {
    return image['id'];
  }

  function cappasityIdProperty(image) {
    return image['cappasity_id'];
  }

  function makeMockedImageId(cappasityImageId) {
    var parsedId = parseInt(cappasityImageId, 10);
    if (isNaN(parsedId)) {
        return false;
    }

    return String(initialFakeId + parsedId);
  }

  function thumbPrefix(id) {
    return 'thumb_' + id;
  }

  function makeEmbedId(cappasityId) {
    return 'cappasity:' + cappasityId;
  }

  function findThumbImageId(thumbLinkEl) {
    var thumbImgEl = thumbLinkEl.querySelector('img');

    if (thumbImgEl === null) {
      throw new Error('Thumbnail link was expected to have thumbnail image inside');
    }

    return thumbImgEl.getAttribute('id');
  }

  function isCappasityLink(thumbLinkEl) {
    var id = findThumbImageId(thumbLinkEl);

    return cappasityThumbIds.indexOf(id) !== -1;
  }

  function filterCappasityLinks(_, el) { return isCappasityLink(el); }

  function getModelIdByThumbId(thumbId) {
    var cappasityId = thumbIdsToModelIds[thumbId];

    if (cappasityId === undefined) {
      throw Error('Target thumb id not found in haystack: ' + thumbId);
    }

    return cappasityId;
  }

  var $thumbContainer = $('#thumbs_list');
  var $thumbList = $('ul#thumbs_list_frame');
  var $cappasityLinks = $thumbList.find('a').filter(filterCappasityLinks);

  if (!$cappasityLinks.length || !$thumbList.length) {
    return;
  }

  var $embeds = {};
  var $embedsArr = [];
  var $iframesArr = [];

  $cappasityLinks.each(function mapLinks(_, el) {
    var thumbEl = el.querySelector('img');
    var thumbId = thumbEl.getAttribute('id');
    var modelId = getModelIdByThumbId(thumbId);
    var embedId = makeEmbedId(modelId);

    thumbEl.setAttribute('src', iconHref);
    el.querySelector('img').setAttribute('src', iconHref);

    var $iframe = $('<iframe />', {
      src: playerUrl + '/' + modelId + '/embedded?' + $.param(playerSettings),
      frameborder: 0,
      allowfullscreen: true
    });
    $iframe.attr('width', playerSettings.width);
    $iframe.attr('height', playerSettings.height);

    var $embed = $('<div />', { id: embedId })
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

    if (!$embeds[embedId]) {
      $embeds[embedId] = $embed;
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

  function getTargetEmbedId($el) {
    if (!isCappasityLink($el[0])) {
      return $el.attr('title');
    }

    var $img = $el.find('img').eq(0);
    var thumbId = $img.attr('id');
    var modelId = getModelIdByThumbId(thumbId);
    return makeEmbedId(modelId);
  }

  function displayImage($el) {
    var targetEmbedId = getTargetEmbedId($el);
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
        $embed.css({ zIndex: targetEmbedId === $embed.attr('id') ? 1 : -1 })
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
});
