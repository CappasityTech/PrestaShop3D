/**
{LICENSE_PLACEHOLDER}
*/

$(document).ready(function () {
  var label = $('#sync-wrapper .bootstrap:first-child');

  if (label) {
    var url = $('#sync-wrapper').data('url');
    var interval = setInterval(function() {
      $.get(url, function(count) {
        if (count === '0') {
          clearInterval(interval);
          label.removeClass('module_warning alert alert-warning');
          label.addClass('module_confirmation conf confirm alert alert-success');
          label.text('Synchronization complete');
        }
      });
    }, 1000 * 5);
  }
});
