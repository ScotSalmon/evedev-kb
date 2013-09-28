<div class="block-header">Scouts/Logistics</div>
<table class="kb-table" width="360" border="0" cellspacing="1" border="0">
  <tr>
    <td width="100%" align="left" valign="top">
      <table width="100%" border="0" cellspacing="0" border="0">
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{section name=i loop=$scouts}
        <tr class={cycle name=ccl}>
          <td rowspan=3 width="64"><a href="?a=search&searchtype=pilot&searchphrase={$scouts[i].pilotname}"><img height="64" width="64" src="{$scouts[i].pilotimg}" border="0"></a></td>
          <td rowspan=3 width="64"><img height="64" width="64" src="mods/scout/scout.png" border="0"></a></td>
          <td class=kb-table-cell-pilot style="padding-top: 1px; padding-bottom: 1px;"><a href="?a=search&searchtype=pilot&searchphrase={$scouts[i].pilotname}" style="color: rgb(255, 255, 255);"><b>{$scouts[i].pilotname}</b></a></td>
        </tr>
        <tr class={cycle name=ccl}>
          <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><a href="?a=corp_detail&crp_id={$scouts[i].corpid}">{$scouts[i].corp}</a></td>
        </tr>
        <tr class={cycle name=ccl}>
          <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><a href="?a=alliance_detail&all_id={$scouts[i].allianceid}">{$scouts[i].alliance}</a></td>
        </tr>
{/section}
      </table>
      <table width="100%" border="0" cellspacing="0" border="0">
        <tr class={cycle name=ccl}>
        </tr>
{if $isAdmin && count($scouts) > 0}
        <tr>
          <td colspan=3 class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;">
            <form id="delpost_form" name="delpost_form" method="post" action="">
  {if $confirmDelete}
              Confirm deletion of Scout "{$confirmDeletePilotName}"
              <input type="hidden" name="delscout_scoutID" value="{$confirmDeleteScoutID}">
              <input type="hidden" name="delscout_pilotID" value="{$confirmDeletePilotID}">
              <input class="comment-button" name="delscout_confirm" type="submit" value="Delete scout">
  {else}
              <select name="delscout_scoutinfo">
                <option value="-1">Delete scout...</option>
{section name=i loop=$scouts}
                <option value="{$scouts[i].scoutid}.{$scouts[i].pilotid}.{$scouts[i].pilotname}">{$scouts[i].pilotname}</option>
{/section}
              </select>&nbsp;
              <input class="comment-button" name="delscout_request" type="submit" value="Delete scout">
  {/if}
            </form> 
          </td>
        </tr>
{/if}
        <tr>
          <td class=kb-table-cell><form id="postform" name="postform" method="post" action="">
            <b>Name:</b>
            <input style="position:relative; right:-3px;" class="comment-button" name="scoutname" type="text" size="24" maxlength="24">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          </td>
        </tr>
        <tr>
          <td>
{if $scoutPassReq and !$isAdmin}
            <b>Password:</b>
            <input type="password" name="password" size="19" class="comment-button">&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
            <input class="comment-button" name="scoutsubmit" type="submit" value="Add scout">
            </form>
          </td>
        </tr>
{if $error != ''}
        <tr>
          <td>
            <b>{$error}</b>
          </td>
        </tr>
{/if}
      </table>
    </td>
  </tr>
</table>
