<div style="float: left">
<table class='kb-table' cellspacing='1' style="margin: 1em">
<tr class='kb-table-header'>
<td class='kb-table-cell' align='center'>Kills</td>
<td class='kb-table-cell' align='center'>Pilot</td>
</tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$pilots item="entity"}
<tr class="{cycle name="ccl"}">
<td class='kb-table-cell' align='right'>{$entity.kills}</td>
<td class='kb-table-cell'><a href="?a=pilot_detail&plt_id={$entity.id}">{$entity.name}</a></td>
</tr>
{/foreach}
</table>
</div>

<div style="float: left">
<table class='kb-table' cellspacing='1' style="margin: 1em">
<tr class='kb-table-header'>
<td class='kb-table-cell' align='center'>Kills</td>
<td class='kb-table-cell' align='center'>Corp</td>
</tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$corps item="entity"}
<tr class="{cycle name="ccl"}">
<td class='kb-table-cell' align='right'>{$entity.kills}</td>
<td class='kb-table-cell'><a href="?a=corp_detail&crp_id={$entity.id}">{$entity.name}</a></td>
</tr>
{/foreach}
</table>
</div>

<div style="float: left">
<table class='kb-table' cellspacing='1' style="margin: 1em">
<tr class='kb-table-header'>
<td class='kb-table-cell' align='center'>Kills</td>
<td class='kb-table-cell' align='center'>Alliance</td>
</tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$alliances item="entity"}
<tr class="{cycle name="ccl"}">
<td class='kb-table-cell' align='right'>{$entity.kills}</td>
<td class='kb-table-cell'><a href="?a=alliance_detail&all_id={$entity.id}">{$entity.name}</a></td>
</tr>
{/foreach}
</table>
</div>
