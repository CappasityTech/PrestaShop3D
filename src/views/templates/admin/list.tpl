{*
{LICENSE_PLACEHOLDER}
*}
<div class="row">
    <div class="input-group col-md-6" style="margin-bottom: 10px">
        <input type="text" class="form-control cappasity-search-input" value="{$query|escape:'htmlall':'UTF-8'}" placeholder="SKU or name">
        <span class="input-group-btn">
            <button class="btn btn-default cappasity-search" type="button">Search</button>
        </span>
    </div>
</div>

<div class="row">
    {foreach from=$files item=file}
        <div class="col-md-4 cappasity-model"
             data-embed="{$file->getEmbed(true)|escape:'html':'UTF-8'}"
             data-id="{$file->getId()|escape:'htmlall':'UTF-8'}">
            <img class="img-thumbnail"
                 style="cursor: pointer"
                 src="https://api.cappasity.com/api/files/preview/{$alias|escape:'htmlall':'UTF-8'}/h200-w200-cfill/{$file->getId()|escape:'htmlall':'UTF-8'}.jpeg"/>
            <h4 style="overflow: hidden;white-space: nowrap;text-overflow: ellipsis;width: 100%;">Name: {$file->getName()|escape:'htmlall':'UTF-8'}</h4>
            <h4 style="overflow: hidden;white-space: nowrap;text-overflow: ellipsis;width: 100%;">SKU: {$file->getAlias()|escape:'htmlall':'UTF-8'}</h4>
        </div>
        {foreachelse}
        {l s='You haven\'t any products to display.' mod='cappasity3d'}
    {/foreach}
</div>

<div class="row">
  <nav>
    <ul class="pagination">
      {for $page=1 to $pagination.pages}
        {if $page eq $pagination.page}
          <li class="active"><span>{$page|escape:'htmlall':'UTF-8'}</span></li>
        {else}
          <li>
            <a class="cappasity-paginate" href="#" data-page="{$page|escape:'htmlall':'UTF-8'}">
              {$page|escape:'htmlall':'UTF-8'}
            </a>
          </li>
        {/if}
      {/for}
    </ul>
  </nav>
</div>
