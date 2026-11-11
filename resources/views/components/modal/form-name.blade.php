{{-- Put inside a form that lives in <x-modal>: after a failed save the modal opens again with the errors. --}}
@props(['name'])
<input type="hidden" name="_modal" value="{{ $name }}">
