{*
{LICENSE_PLACEHOLDER}
*}
{if $is17}
<style>
#module_cappasity3d ul.pagination {
  list-style: none;
  padding-left: 15px;
}

#module_cappasity3d ul.pagination > li {
  padding: 0 5px;
  display: inline-block;
}

#module_cappasity3d .panel {
  padding: 10px;
}

#module_cappasity3d .btn {
  font-size: .9rem;
  border-width: 1px;
}

.cappasity-preview-container {
  margin: 5px 0;
}
</style>
{/if}
<div class="panel cappasity-preview-container row" style="{($currentFile)?'':'display: none'|escape:'htmlall':'UTF-8'}">
    <h3>{l s='Preview' mod='cappasity3d'}</h3>

    <div class="cappasity-embed col-sm-9">
        {($currentFile)?$currentFile->getEmbed(true):''|escape:'htmlall':'UTF-8'}
    </div>

    <div class="form-horizontal col-sm-3">
        <div class="panel-footer">
            <input class="cappasity-id" type="hidden" name="cappasityId" value="{($currentFile)?$currentFile->getId():''|escape:'htmlall':'UTF-8'}">
            <input class="cappasity-action" type="hidden" name="cappasityAction" value="">

            <button type="submit" name="submitAddproduct" class="btn btn-default pull-right">
                <i class="process-icon-save"></i>Save
            </button>
            {if !$is17}
            <button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right">
                <i class="process-icon-save"></i>Save and stay
            </button>
            {/if}
            <button id="cappasity-action-button" type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right">
                <i class="process-icon-delete"></i>Delete
            </button>
        </div>
    </div>
</div>

<div class="panel">
    <h3>{l s='Cappasity 3D' mod='cappasity3d'}</h3>
    <div class="cappasity-list"
         data-url="{$action|escape:'htmlall':'UTF-8'}&id_product={$productId|escape:'htmlall':'UTF-8'}">
    </div>
</div>

<script>
  {literal}
  function preview() {
    var container = $('.cappasity-preview-container');
    var id = $(this).data('id');
    var embed = $(this).data('embed');

    $('.cappasity-id').val(id);
    $('.cappasity-embed').html(embed);

    container.show();

    $('body, html').animate({ scrollTop: 0 }, 800);
  }

  function paginate(page, query) {
    var listContainer = $('.cappasity-list');
    var url = listContainer.data('url') + '&page=' + page + '&query=' + query;

    listContainer.html('<div><i class="icon-refresh icon-spin"></i></div>');

    $.get(url, function (content) {
      listContainer.html(content);
    });
  }

  function initCappasity() {
    var page = 1;
    var query = '';

    paginate(page, query);

    $(document).on('click', '.cappasity-list .cappasity-model', preview);

    $(document).on('click', '.cappasity-list .cappasity-search', function() {
        var currentQuery = $('.cappasity-search-input').val();

        if (currentQuery !== query) {
          page = 1;
        }

        query = currentQuery;

        paginate(page, query);
    });

    $(document).on('click', '.cappasity-list .cappasity-paginate', function(event) {
      event.preventDefault();
      page = $(this).data('page');

      paginate(page, query);
    });

    $('#cappasity-action-button').on('click', function() {
      $('.cappasity-action').val('remove');
    });
  }

  $(document).ready(initCappasity);
  {/literal}
</script>
