{* Product preview *}
<div class="preview fn_product">
    <div class="fn_transfer clearfix">

        {* Product image *}
        <a class="preview_image" href="{if $smarty.get.module=='ComparisonView'}{$product->image->filename|resize:800:600:w}{else}{$lang_link}products/{$product->url}{/if}" {if $smarty.get.module=='ComparisonView'}data-fancybox="group" data-caption="{$product->name|escape}"{/if}>
            {if $product->image->filename}
                <img class="fn_img preview_img" src="{$product->image->filename|resize:200:200}" alt="{$product->name|escape}" title="{$product->name|escape}"/>
            {else}
                <img class="fn_img preview_img" src="design/{$settings->theme}/images/no_image.png" width="250" height="250" alt="{$product->name|escape}"/>
            {/if}
            {if $product->special}
                <img class="promo_img" src='files/special/{$product->special}' alt='{$product->special|escape}' title="{$product->special|escape}"/>
            {/if}
        </a>

        {* Product name *}
        <a class="product_name" data-product="{$product->id}" href="{$lang_link}products/{$product->url}">{$product->name|escape}</a>

        <div class="price_container">
            {* Old price *}
            <div class="old_price{if !$product->variant->compare_price} hidden{/if}">
                <span class="fn_old_price">{$product->variant->compare_price|convert}</span> <span>{$currency->sign|escape}</span>
            </div>

            {* Price *}
            <div class="price">
                <span class="fn_price">{$product->variant->price|convert}</span> <span>{$currency->sign|escape}</span>
            </div>
        </div>

        <form class="fn_variants preview_form" action="/{$lang_link}cart">
            {if !$settings->is_preorder}
                {* Out of stock *}
                <p class="fn_not_preorder {if $product->variant->stock > 0} hidden{/if}">
                    <span data-language="out_of_stock">{$lang->out_of_stock}</span>
                </p>
            {else}
                {* Pre-order *}
                <button class="button buy fn_is_preorder{if $product->variant->stock > 0} hidden{/if}" type="submit" data-language="pre_order">{$lang->pre_order}</button>
            {/if}

            {* Submit cart button *}
            <button class="button buy fn_is_stock{if $product->variant->stock < 1} hidden{/if}" type="submit"><span data-language="add_to_cart">{$lang->add_to_cart}</span></button>
            
            {* Product variants *}  
            {if ($product->output_as_radio)}

                <div style="margin-top: 20px">     
                    {foreach $product->variants as $v}

                        <label>
                            <input type="radio"
                            class="fn_variant variant_radio {if $product->variants|count < 2} hidden{/if}"
                            name="variant" 
                            value="{$v->id}"
                            data-price="{$v->price|convert}" 
                            data-stock="{$v->stock}"{if $v->compare_price > 0} 
                            data-cprice="{$v->compare_price|convert}"{/if}{if $v->sku} 
                            data-sku="{$v->sku|escape}"{/if} {if $v->units}
                            data-units="{$v->units}"{/if}
                            {if ($v->id == $product->variant->id)} checked {/if}
                            >
                                {if count($product->variants) > 1}
                                    {if $v->name}{$v->name|escape}{else}{$product->name|escape}{/if}
                                {/if}
                        </label> 

                    {/foreach}
                </div>

            {else}
                <select name="variant" class="fn_variant variant_select {if $product->variants|count == 1}hidden{/if}">

                    {foreach $product->variants as $v}
                        <option value="{$v->id}" 
                        data-price="{$v->price|convert}" 
                        data-stock="{$v->stock}"
                        {if $v->compare_price > 0} data-cprice="{$v->compare_price|convert}"{/if}
                        {if $v->sku} data-sku="{$v->sku|escape}"{/if}
                        >
                            {if $v->name}{$v->name|escape}{else}{$product->name|escape}{/if}
                        </option>
                    {/foreach}

                </select>
            {/if}

        </form>
    </div>
</div>
