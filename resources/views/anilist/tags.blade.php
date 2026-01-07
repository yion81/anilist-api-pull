<!DOCTYPE html>
<html>
<head>
    <title>Anilist Tag Bypass</title>
    <link rel="stylesheet" href="{{ asset('css/tags.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Overpass:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav>
        <a href="{{ url('/') }}">Home</a>
        <a href="{{ url('/search') }}">Search User</a>
        <a href="{{ url('/tags') }}" class="active">Tag Bypass</a>
        <a href="{{ url('/myMangaProgress') }}">Don't open</a>
    </nav>

    <div class="container">
        
        <div class="search-container">
            <h1 style="color: #e1e6eb;">Anilist Tag Bypass</h1>
            <form id="searchForm" method="POST" action="{{ route('anilist.tags_process') }}">
                @csrf 
                <input type="text" name="username_input" placeholder="Enter username" value="{{ $username ?? '' }}" required>
                <input type="submit" id="searchBtn" value="Search">
            </form>
            
            <div id="loading-message" style="display: none; margin-top: 15px; color: #3db4f2; font-weight: bold; font-size: 1.1rem;">
                ⏳ Please wait, calling anilist's api... (This may take a few seconds)
            </div>
        </div>

        @if(isset($animeTags))
            <div class="toggle-container">
                <button class="toggle-btn active" onclick="showView('anime')" id="btn-anime">Anime</button>
                <button class="toggle-btn" onclick="showView('manga')" id="btn-manga">Manga</button>
            </div>

            <div id="view-anime" class="tags-grid active-view">
                @foreach($animeTags as $name => $data)
                    <div class="tag-card">
                        <div class="card-header">
                            <span class="tag-title">{{ $name }}</span>
                            <span class="rank-badge">{{ $loop->iteration }}</span>
                        </div>
                        
                        <div class="stats-row">
                            <div class="stat-item">
                                <strong>{{ $data['count'] }}</strong>
                                Entries
                            </div>
                            <div class="stat-item">
                                <strong>{{ $data['avg'] }}%</strong>
                                Mean Score
                            </div>
                            <div class="stat-item">
                                <strong>{{ number_format($data['percent'], 1) }}%</strong>
                                of List
                            </div>
                        </div>

                        <div class="image-row">
                            @foreach(array_slice($data['topSeries'], 0, 4) as $series)
                                <div class="series-thumb">
                                    <img src="{{ $series['image'] }}" title="{{ $series['title'] }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="view-manga" class="tags-grid">
                @foreach($mangaTags as $name => $data)
                    <div class="tag-card">
                        <div class="card-header">
                            <span class="tag-title">{{ $name }}</span>
                            <span class="rank-badge">{{ $loop->iteration }}</span>
                        </div>
                        
                        <div class="stats-row">
                            <div class="stat-item">
                                <strong>{{ $data['count'] }}</strong>
                                Entries
                            </div>
                            <div class="stat-item">
                                <strong>{{ $data['avg'] }}%</strong>
                                Mean Score
                            </div>
                            <div class="stat-item">
                                <strong>{{ number_format($data['percent'], 1) }}%</strong>
                                of List
                            </div>
                        </div>

                        <div class="image-row">
                            @foreach(array_slice($data['topSeries'], 0, 4) as $series)
                                <div class="series-thumb">
                                    <img src="{{ $series['image'] }}" title="{{ $series['title'] }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        // TOGGLE VIEW SCRIPT
        function showView(type) {
            document.getElementById('view-anime').classList.remove('active-view');
            document.getElementById('view-manga').classList.remove('active-view');
            document.getElementById('btn-anime').classList.remove('active');
            document.getElementById('btn-manga').classList.remove('active');

            document.getElementById('view-' + type).classList.add('active-view');
            document.getElementById('btn-' + type).classList.add('active');
        }

        // LOADING SCRIPT (New)
        document.getElementById('searchForm').addEventListener('submit', function() {
            // Show the loading text
            document.getElementById('loading-message').style.display = 'block';
            
            // Change button text and disable it to prevent double-clicks
            var btn = document.getElementById('searchBtn');
            btn.value = 'Searching...';
            btn.style.opacity = '0.7';
            btn.style.cursor = 'not-allowed';
            // We don't strictly disable=true because sometimes that stops the form submission depending on browser quirks with enter keys, 
            // but the visual change is enough to tell users to wait.
        });
    </script>
</body>
</html>