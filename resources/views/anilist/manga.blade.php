<!DOCTYPE html>
<html>
<head>
    <title>My Manga Progress</title>
    <link rel="stylesheet" href="{{ asset('css/manga.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Overpass:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

    <nav>
        <a href="{{ url('/') }}">Home</a>
        <a href="{{ url('/search') }}">Search User</a>
        <a href="{{ url('/tags') }}">Tag Bypass</a>
        <a href="{{ url('/myMangaProgress') }}" class="active">Don't open</a>
    </nav>

    <div class="container">
        <h1 style="margin-bottom: 30px; color: #e1e6eb;">My Manga Library</h1>

        <div class="manga-grid">
            @foreach ($entries as $entry)
                
                @php
                    $media = $entry['media'];
                    $title = $media['title']['english'] ?: $media['title']['romaji'];
                    $progress = $entry['progress'];
                    $chapters = $media['chapters'];
                    $color = $media['coverImage']['color'] ?? '#3db4f2';
                @endphp

                <div class="manga-card">
                    <a href="{{ $media['siteUrl'] }}" target="_blank" class="manga-card-image">
                        <img src="{{ $media['coverImage']['large'] }}" alt="{{ $title }}">
                    </a>
                    
                    <h3>
                        <a href="{{ $media['siteUrl'] }}" target="_blank">{{ $title }}</a>
                    </h3>

                    @if ($media['status'] == "FINISHED" && $chapters != null)
                        <h4 style="color: #8ba0b2;">Completed: {{ $chapters }} Ch.</h4>
                    @else
                        <h4>Current: Ch. {{ $progress }}</h4>
                    @endif
                </div>

            @endforeach
        </div>
    </div>

</body>
</html>