<div class="kb-kills-header"><a href="javascript:toggle_view('balance_of_power');">Balance of Power</a></div>
<div id="balance_of_power">
    <table class=kb_table_involved_big width=100% border=0 cellspacing="1">
        <tr>
            <th colspan=3>Friendly</th>
        </tr>
        <tr class=kb-table-header>
            <th>Alliances</th> <th>Corporations</th> <th>Ships (Number/destroyed)</th>
        </tr>

        {assign var="first" value="true"}

        {foreach from=$GoodAllies key=key item=l}
            <tr class=kb-table-row-even>
                <td class=kb-table-cell>
                    ({$l.quantity}) {$key} <br/>
                </td>
                <td class=kb-table-cell>
                    {foreach from=$l.corps key=key1 item=l1}
                        ({$l1}) {$key1} <br/>
                    {/foreach}
                </td>
                {if $first == "true"}
                    <td rowspan={$GAlliesCount} class=kb-table-cell NOWRAP>
                        {foreach from=$GoodShips key=key item=l}
                            <font color={$l.color}>({$l.times})({$l.destroyed}) {$key} ({$l.shipClass}) </font><br/>
                        {/foreach}
                    </td>

                    {assign var="first" value="false"}
                {/if}
            </tr>
        {/foreach}
    </table>

    <br/>

    <table class=kb_table_involved_big width=100% border=0 cellspacing="1">
        <tr>
            <th colspan=3>Hostile</th>
        </tr>
        <tr class=kb-table-header>
            <th>Alliances</th> <th>Corporations</th> <th>Ships (Number/destroyed)</th>
        </tr>

        {assign var="first" value="true"}

        {foreach from=$BadAllies key=key item=l}
            <tr class=kb-table-row-even>
                <td class=kb-table-cell>
                    ({$l.quantity}) {$key} <br/>
                </td>
                <td class=kb-table-cell>
                    {foreach from=$l.corps key=key1 item=l1}
                        ({$l1}) {$key1} <br/>
                    {/foreach}
                </td>
                {if $first == "true"}
                    <td rowspan={$BAlliesCount} class=kb-table-cell NOWRAP>
                        {foreach from=$BadShips key=key item=l}
                            <font color={$l.color}>({$l.times})({$l.destroyed}) {$key} ({$l.shipClass}) </font><br/>
                        {/foreach}
                    </td>

                    {assign var="first" value="false"}
                {/if}
            </tr>
        {/foreach}
    </table>
</div>

<br/>
<div class="kb-kills-header"><a href="javascript:toggle_view('pilots_and_ships');">Pilots and Ships</a></div>
<div id="pilots_and_ships">

    <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr><td width="49%" valign="top">
            <div class="kb-date-header">Friendly ({$friendlycnt})</div>
                <br/>

            {assign var='loop' value=$pilots_a}
            {assign var='tipo' value='a'}
            {include file="../../../mods/battles_mod/battle_overview_table.tpl"}

            </td><td width="55%" valign="top">
            <div class="kb-date-header">Hostile ({$hostilecnt})</div>
            <br/>

            {assign var='loop' value=$pilots_e}
            {assign var='tipo' value='e'}
            {include file="../../../mods/battles_mod/battle_overview_table.tpl"}

            </td>
        </tr>
    </table>
    <br/>
</div>