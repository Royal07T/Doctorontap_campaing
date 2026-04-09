@extends('layouts.caregiver')

@section('title', 'Communication Center')
@section('page-title', 'Communication Center')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" style="height: calc(100vh - 10rem);">
    @livewire('care-giver.communication-center')
</div>
@endsection
