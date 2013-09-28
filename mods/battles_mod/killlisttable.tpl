{section name=day loop=$killlist}
{if $daybreak}
    <br/>
    <table align="center" cellpadding="0" cellspacing="0" width="100%">
        <td align="left" width="38%">
            <div class="kb-date-header">{"l, F jS"|date:$killlist[day].date}</div>
        </td>
        <td align="left" width="31%">
            <table class="kb-table" width="100%" align="right" cellspacing="1">
                <tr class="kb-table-header">
                    <td class="kb-table-cell" colspan="3" align="center" ><b>Top Killer</b></td>
                </tr>
                <tr class="kb-table-row-even">
                    <td width="32"><img src="{$killlist[day].killer.portrait}" border="0" width="32" heigth="32"></td>
                    <td align="middle"><a href="?a=pilot_detail&amp;plt_id={$killlist[day].killer.id}">{$killlist[day].killer.pilota|truncate:25}</a><br/>{$killlist[day].killer.corp|truncate:25}</td>
                    <td width="40" align="center">{$killlist[day].killer.punti}<br/>points</td>
                </tr>
            </table>
        </td>
        <td align="right" width="31%">
            <table class="kb-table" width="100%" align="right" cellspacing="1">
                <tr class="kb-table-header" >
                    <td class="kb-table-cell" colspan="3" align="center">Top Loser</td>
                </tr>
                <tr class="kb-table-row-even">
                    <td width="32" align="center"><img src="{$killlist[day].loser.portrait}" border="0" width="32" heigth="32"></td>
                    <td align="middle"><a href="?a=pilot_detail&amp;plt_id={$killlist[day].loser.id}">{$killlist[day].loser.pilota|truncate:25}</a><br/>{$killlist[day].loser.corp|truncate:25}</td>
                    <td width="40" align="center">{$killlist[day].loser.punti}<br/>points</td>
                </tr>
            </table>
        </td>
        </tr>
    </table>       
    <br/>
{/if}
<table class="kb-table" width="100%" align="center" cellspacing="1">
    <tr class="kb-table-header">
        <td class="kb-table-header" colspan="2" align="center">Ship type</td>
        <td class="kb-table-header"{if $config->get('killlist_alogo')} colspan="3"{/if} align="center">Victim</td>
        <td class="kb-table-header" colspan="2" align="center">Final blow</td>
        <td class="kb-table-header" align="center">System</td>
        {if $config->get('killlist_involved')}
            <td class="kb-table-header" align="center">Inv.</td>
        {/if}
        <td class="kb-table-header" align="center">Time</td>
<!--    {if $comments_count}
        <td class="kb-table-header" align="center"><img src="{$img_url}/comment{$comment_white}.gif"></td>
    {/if}-->
        <td class="kb-table-header" align="center">BS</td>
    </tr>

{section name=kill loop=$killlist[day].kills}
{assign var="k" value=$killlist[day].kills[kill]}

<tr class="{cycle advance=false name=ccl}" onmouseout="this.className='{cycle name=ccl}';" style="height: 34px; cursor: pointer;"
            onmouseover="this.className='kb-table-row-hover';" onClick="window.location.href='?a=kill_detail&kll_id={$k.id}';">

        <td width="32" align="center"><img src="{$k.victimshipimage}" border="0" width="32" heigth="32"></td>
        <td height="32" width="140" valign="middle"><div class="kb-shiptype"><b>{$k.victimshipname}</b><br>{$k.victimshipclass}</div><div class="kb-shipicon"><img src="{$k.victimshipindicator}" border="0"></div></td>
        <td width="32" align="center"><img src="{$k.victimportrait}" border="0" width="32" heigth="32"></td>
        {if $config->get('killlist_alogo')}
            {if $k.allianceexists}
            <td width="32" align="center"><img src="{$img_url}/alliances/{$k.victimallianceicon}.png" border="0" width="32" height="32" title="{$k.victimalliancename}"></td>
            {elseif $k.victimalliancename != "None"}
            <td width="32" align="center"><img src="{$img_url}/alliances/default.gif" border="0" width="32" height="32" title="{$k.victimalliancename}"></td>
            {else}
            <td width="32" align="center"><img src="{$img_url}/alliances/empty.png" border="0" width="32" height="32" title="No Ally"></td>
            {/if}
        {/if}
        <td width="200" class="kb-table-cell"><b>{$k.victim}</b><br/>{$k.victimcorp|truncate:30}</td>
        <td width="32" align="center"><img src="?a=thumb&amp;id={$k.fbplext}&amp;size=32" border="0" width="32" heigth="32"></td>
        
        <td width="200" class="kb-table-cell"><b>{$k.fb}</b><br>{$k.fbcorp|truncate:30}</td>
        <td width="100" class="kb-table-cell" align="center"><b>{$k.system|truncate:10}</b><br/>({$k.systemsecurity|max:0|string_format:"%01.1f"})</td>
        {if $config->get('killlist_involved')}
            <td width="30" align="center" class="kb-table-cell"><b>{$k.inv}</b></td>
        {/if}
        {if $daybreak}
        <td class="kb-table-cell" align="center"><b>{$k.timestamp|date_format:"%H:%M"}</b></td>
        {else}
        <td class="kb-table-cell" align="center" width=80><b>{$k.timestamp|date_format:"%Y-%m-%d"}<br>{$k.timestamp|date_format:"%H:%M"}</b></td>
        {/if}
<!--        {if $comments_count}
        <td width="10" class="kb-table-cell" align="center"><b>{$k.commentcount}</b></td>
        {/if} -->
        <td width="10" class="kb-table-cell" align="center"><b>{$k.BS}</b></td>    </tr>
    {/section}
</table>
{sectionelse}
<p>No data.
{/section}