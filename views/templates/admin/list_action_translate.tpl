<div class="translate-block">
    <button type="button" class="btn btn-default dropdown-toggle manage_translations" tabindex="-1" data-toggle="dropdown">
        <i class="icon-flag"></i>
        {l s='Manage translations'}
        {* <span class="caret"></span> *}
    </button>
    <ul class="dropdown-menu">
        {foreach from=$module_languages item=language}
            <li>
                <a href="{$translateLinks[$language.iso_code]|escape:'html':'UTF-8'}" target="_blank">{$language.name|escape:'html':'UTF-8'}</a>
            </li>
        {/foreach}
    </ul>
</div>