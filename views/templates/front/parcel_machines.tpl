<div class="form-group row">
  <label for="qwqer_parcel_machine" class="col-md-3">{l s='Parcel Machines' mod='qwqer'}</label>
  <div class="col-md-9">
    <select name="qwqer_parcel_machine" id="qwqer_parcel_machine" class="form-control">
        {foreach $parcel_machines as $parcel_machine}
          <option value="{$parcel_machine['id']}"{if $selected_parcel_machine_id == $parcel_machine['id']}selected{/if}>
              {$parcel_machine['name']}
          </option>
        {/foreach}
    </select>
  </div>
</div>
