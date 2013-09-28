<table class="kb-table" width="360" border="0" cellspacing="1" border="0">
  <tr>
    <td width="100%" align="left" valign="top">
      <table width="100%" border="0" cellspacing="0" border="0">
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}{section name=i loop=$comments}
        <tr class="{cycle name=ccl}">
          <td>
            <div style="position: relative;"><a href="?a=search&searchtype=pilot&searchphrase={$comments[i].name}">{$comments[i].name}</a> (<a href="?a=kill_detail&amp;kll_id={$comments[i].kll_id}">killmail</a>):
{if $comments[i].time}
            <span style="position:absolute; right: 0px;">{$comments[i].time}</span>
{/if}
            <p>{$comments[i].comment}</p>
            <p></p>
          </td>
        </tr>
{/section}
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
