<table class='kb-table' cellspacing='1' style="margin: 1em">
<tr class='kb-table-header'>
<td class='kb-table-cell' align='center'>Kills</td>
<td class='kb-table-cell' align='center'>Region</td>
</tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$regions item="region"}
<tr class="{cycle name="ccl"}">
<td class='kb-table-cell' align='right'>{$region.kills}</td>
<td class='kb-table-cell'>
<a href="?a=top20regions&region_id={$region.id}">
{if $region.name == 'Unknown'}
{$region.name} ({$region.id})
{else}
{$region.name}
{/if}
</a></td>
</tr>
{/foreach}
</table>
