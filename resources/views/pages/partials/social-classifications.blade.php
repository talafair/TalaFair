@php($prefix = $formPrefix ?? '')
<div class="col-12 col-md-4">
  <input type="hidden" name="is_4ps_member" value="0">
  <div class="form-check mt-md-4">
    <input class="form-check-input" type="checkbox" name="is_4ps_member" value="1" id="{{ $prefix }}is_4ps_member" @checked(old('is_4ps_member', $user->is_4ps_member ?? false))>
    <label class="form-check-label" for="{{ $prefix }}is_4ps_member">4Ps member</label>
  </div>
</div>
<div class="col-12 col-md-4">
  <input type="hidden" name="is_solo_parent" value="0">
  <div class="form-check mt-md-4">
    <input class="form-check-input" type="checkbox" name="is_solo_parent" value="1" id="{{ $prefix }}is_solo_parent" @checked(old('is_solo_parent', $user->is_solo_parent ?? false))>
    <label class="form-check-label" for="{{ $prefix }}is_solo_parent">Solo parent</label>
  </div>
</div>
