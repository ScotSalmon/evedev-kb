{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}

<table border="0" width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td width="50%" valign="top">
            <div class="kb-date-header">Dropped</div>
            <br/>
            <table class=kb_table_involved width=100% border=0 cellspacing="1">
                <tr class=kb-table-header>
                    <th>Item</th> <th nowrap>Quantity</th> <th nowrap>Value</th> <th nowrap>Total Value</th>
                </tr>
                {foreach from=$Sloot key=key item=l}
                    <tr class="{cycle name=ccl}">
                        <td class=kb-table-cell>
                            {$key}<br/>
                        </td>
                        <td class=kb-table-cell align="right" nowrap>
                            {$l.Quantity}
                        </td>
                        <td class=kb-table-cell align="right" nowrap>
                            {$l.Value}
                        </td>
                        <td class=kb-table-cell align="right" nowrap>
                            {$l.TValue}
                        </td>
                    </tr>
                {/foreach}  
                <tr class="{cycle name=ccl}">
                    <td colspan="3"><b>Total</b></td>
                    <td class=kb-table-cell align="right" nowrap>
                        <b>{$STotal}</b>
                    </td>
                </tr>
            </table>
        </td>
        <td width="50%" valign="top">
            <div class="kb-date-header">Destroyed</div>
            <br/>
            <table class=kb_table_involved width=100% border=0 cellspacing="1">
                <tr class=kb-table-header>
                    <th>Item</th> <th nowrap>Quantity</th> <th nowrap>Value</th> <th nowrap>Total Value</th>
                </tr>
                {foreach from=$Dloot key=key item=l}
                    <tr class="{cycle name=ccl}">
                        <td class=kb-table-cell>
                            {$key}<br/>
                        </td>
                        <td class=kb-table-cell align="right" nowrap>
                            {$l.Quantity}
                        </td>
                        <td class=kb-table-cell align="right" nowrap>
                            {$l.Value}
                        </td>
                        <td class=kb-table-cell align="right" nowrap>
                            {$l.TValue}
                        </td>
                    </tr>
                {/foreach}  
                <tr class="{cycle name=ccl}">
                    <td colspan="3"><b>Total</b></td>
                    <td class=kb-table-cell align="right" nowrap>
                        <b>{$DTotal}</b>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
 
