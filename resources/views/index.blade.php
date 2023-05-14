@extends('layouts.main')

@section('content')

<div class="container mx-auto px-4 pt-16">
    <div class="popular-movies">
        <h2 class="uppercase tracking-wider text-orange-500 text-lg font-semibold">
            Movies
        </h2>

{{--        @foreach($movies as $movie)--}}
{{--            <x-movie-card :movie="$movie"/>--}}
{{--        @endforeach--}}
            <livewire:search-movie>


    </div>
</div>
@endsection
