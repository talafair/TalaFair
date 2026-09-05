@extends('layouts.app')

@section('title', 'Add Resident')

@section('content')
  <div class="page-header p-4 p-lg-5 mb-4">
    <div class="row align-items-center position-relative" style="z-index: 1;">
      <div class="col"><h2 class="fw-bold mb-1"><i class="bi bi-person-plus-fill me-2"></i>Add Resident</h2><p class="mb-0 opacity-75">Create an account for a resident who needs official assistance</p></div>
      <div class="col-auto"><a href="{{ route('users.index') }}" class="btn btn-light fw-semibold"><i class="bi bi-arrow-left me-1"></i>Back to Manage Users</a></div>
    </div>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger rounded-3"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first() }}</div>
  @endif

  <form method="POST" action="{{ route('users.store') }}">
    @csrf
    <div class="card yg-card mb-4"><div class="card-body p-4"><h6 class="fw-bold text-uppercase text-secondary mb-3">Full name</h6><div class="row g-3">
      <div class="col-md-3"><label class="form-label">First name</label><input name="first_name" value="{{ old('first_name') }}" class="form-control" required></div>
      <div class="col-md-3"><label class="form-label">Middle name</label><input name="middle_name" value="{{ old('middle_name') }}" class="form-control"></div>
      <div class="col-md-3"><label class="form-label">Last name</label><input name="last_name" value="{{ old('last_name') }}" class="form-control" required></div>
      <div class="col-md-3"><label class="form-label">Suffix <span class="text-secondary fw-normal">(optional)</span></label><input name="suffix" value="{{ old('suffix') }}" class="form-control"></div>
    </div></div></div>

    <div class="card yg-card mb-4"><div class="card-body p-4"><h6 class="fw-bold text-uppercase text-secondary mb-3">Personal details</h6><div class="row g-3">
      <div class="col-md-6"><span class="form-label d-block">Gender</span><div class="d-flex flex-wrap gap-3">@foreach (['female' => 'Female', 'male' => 'Male', 'others' => 'Others'] as $value => $label)<div class="form-check"><input class="form-check-input" type="radio" name="gender" id="gender_{{ $value }}" value="{{ $value }}" required @checked(old('gender') === $value)><label class="form-check-label" for="gender_{{ $value }}">{{ $label }}</label></div>@endforeach</div><input name="gender_other" value="{{ old('gender_other') }}" class="form-control mt-2" placeholder="Please specify if needed"></div>
      <div class="col-md-3"><label class="form-label">Birthdate</label><input type="date" name="birthdate" value="{{ old('birthdate') }}" max="{{ now()->toDateString() }}" class="form-control" required></div>
      <div class="col-md-3"><label class="form-label">Contact number</label><input name="contact_number" value="{{ old('contact_number') }}" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Occupation <span class="text-secondary fw-normal">(optional)</span></label><input name="occupation" value="{{ old('occupation') }}" class="form-control"></div>
      <div class="col-md-6"><input type="hidden" name="is_student" value="0"><div class="form-check mt-md-4"><input class="form-check-input" type="checkbox" name="is_student" value="1" id="is_student" @checked(old('is_student'))><label class="form-check-label" for="is_student">Is a student</label></div></div>
      <div class="col-12"><label class="form-label">Name of school</label><input name="school" value="{{ old('school') }}" class="form-control"></div>
    </div></div></div>

    <div class="card yg-card mb-4"><div class="card-body p-4"><h6 class="fw-bold text-uppercase text-secondary mb-1">Address</h6><p class="small text-secondary">Fill in the first three. The remaining address is fixed for our barangay.</p><div class="row g-3">
      <div class="col-md-4"><label class="form-label">House / building no.</label><input name="house_no" value="{{ old('house_no') }}" class="form-control" required></div>
      <div class="col-md-4"><label class="form-label">Street</label><input name="street" value="{{ old('street') }}" class="form-control" required></div>
      <div class="col-md-4"><label class="form-label">Zone number</label><input name="zone" value="{{ old('zone') }}" class="form-control" required></div>
    </div><div class="row g-3 mt-1 mx-0 p-3 bg-body-secondary rounded-3">@foreach (['Barangay' => 'San Jose', 'City / Municipality' => 'Iriga City', 'Province' => 'Camarines Sur', 'Country' => 'Philippines', 'Postal code' => '4431'] as $label => $value)<div class="col-6 col-md"><div class="small text-uppercase text-secondary">{{ $label }}</div><div class="fw-semibold">{{ $value }}</div></div>@endforeach</div></div></div>

    <div class="card yg-card mb-4"><div class="card-body p-4"><h6 class="fw-bold text-uppercase text-secondary mb-3">Household</h6><input type="hidden" name="is_head_of_family" value="0"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_head_of_family" value="1" id="is_head_of_family" @checked(old('is_head_of_family'))><label class="form-check-label" for="is_head_of_family">Is the head of the family</label></div><div id="family_link_wrap" class="row g-3 mt-2"><div class="col-md-6"><label class="form-label">Link to a registered family head</label><select name="head_of_family_id" class="form-select"><option value="">Not listed</option>@foreach ($familyHeads as $head)<option value="{{ $head->id }}" @selected(old('head_of_family_id') == $head->id)>{{ $head->full_name }} — {{ $head->house_no }} {{ $head->street }}, Zone {{ $head->zone }}</option>@endforeach</select></div><div class="col-md-6"><label class="form-label">Family head name</label><input name="head_of_family_name" value="{{ old('head_of_family_name') }}" class="form-control"></div></div></div></div>

    <div class="card yg-card mb-4"><div class="card-body p-4"><h6 class="fw-bold text-uppercase text-secondary mb-3">Account</h6><div class="row g-3">
      <div class="col-md-6"><label class="form-label">Username</label><input name="username" value="{{ old('username') }}" class="form-control" required><div class="form-text">Letters, numbers, dashes and underscores only.</div></div>
      <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email') }}" class="form-control" required></div>
      <div class="col-md-6"><label class="form-label">Temporary password</label><input type="password" name="password" class="form-control" required></div>
      <div class="col-md-6"><label class="form-label">Confirm password</label><input type="password" name="password_confirmation" class="form-control" required></div>
    </div><div class="form-text mt-2">Provide the resident with these login details after creating the account.</div></div></div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4"><a href="{{ route('users.index') }}" class="link-secondary small">Cancel</a><button type="submit" class="btn btn-primary fw-semibold px-4"><i class="bi bi-person-check me-1"></i>Create resident</button></div>
  </form>
@endsection

@push('scripts')
<script>
  const head = document.getElementById('is_head_of_family');
  const familyWrap = document.getElementById('family_link_wrap');
  function syncFamilyFields() { familyWrap.hidden = head.checked; familyWrap.querySelectorAll('input, select').forEach((field) => field.disabled = head.checked); }
  head.addEventListener('change', syncFamilyFields);
  syncFamilyFields();
</script>
@endpush
