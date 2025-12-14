<!DOCTYPE html>
<html>
<head>
    <title>My Manga App</title>
    <link rel="stylesheet" href="{{ asset('css/manga.css') }}">
</head>
<body>
    <nav>
        <a href="{{ url('/') }}">Home</a> | 
        <a href="{{ url('/search') }}">Search User's Anilist</a> | 
        <a href="{{ url('/myMangaProgress') }}">My Currently Reading</a>
    </nav>

    <div class="grid-container">
        @foreach ($entries as $entry)
            
            @php
                $media = $entry['media'];
                $title = $media['title']['english'] ?: $media['title']['romaji'];
                $progress = $entry['progress'];
            @endphp

            <div class="manga-card">
                <a href="{{ $media['siteUrl'] }}" target="_blank">
                    <img src="{{ $media['coverImage']['large'] }}" alt="{{ $title }}">
                </a>
                
                <h3>{{ $title }}</h3>

                @if ($media['status'] == "FINISHED" && $media['chapters'] != null)
                    <h4>{{ $progress }} / {{ $media['chapters'] }} Chapters</h4>
                @else
                    <h4>Ch. {{ $progress }}</h4>
                @endif
            </div>

        @endforeach
    </div>
</body>
</html>