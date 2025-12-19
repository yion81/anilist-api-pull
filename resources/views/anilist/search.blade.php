<!DOCTYPE html>
<html>
<head>
    <title>AniList Profile Search</title>
    <link rel="stylesheet" href="{{ asset('css/search.css') }}">
</head>
<body>
    <nav>
        <a href="{{ url('/') }}">Home</a> | 
        <a href="{{ url('/search') }}">Search User's Anilist</a> | 
        <a href="{{ url('/myMangaProgress') }}">My Currently Reading</a>
    </nav>
    <div class="container">
        <h1>Search User</h1>
        
        <form method="POST" action="{{ route('anilist.search') }}">
            @csrf <input type="text" name="username_input" placeholder="Enter username (e.g. yion)" required>
            <input type="submit" value="Search">
        </form>

        @if($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        @if(isset($userData))
            <div class="result">
                <img src="{{ $userData['avatar']['large'] }}" alt="Avatar">
                <h2 style="text-align: center;">{{ $userData['name'] }}</h2>
                <p style="text-align: center;">
                    <a href="{{ $userData['siteUrl'] }}" target="_blank">View on AniList</a>
                </p>  
                
                <div class="stat-box">
                    <h3>📅 Join Date</h3>
                    <p>{{ date("l, F j, Y g:i A", $userData['createdAt']) }}</p>
                </div>

                <div class="stat-box">
                    @if($userData['statistics']['anime']['count'] > $userData['statistics']['manga']['count'])
                        <h3>This person is more of an anime watcher than a manga reader!</h3>
                    @else
                        <h3>This person is more of a manga reader than an anime watcher!</h3>
                    @endif
                </div>

                <div class="stat-box">
                    <h3>📺 Anime Stats</h3>
                    <p>Count: {{ $userData['statistics']['anime']['count'] }}</p>
                    <p>Episodes: {{ $userData['statistics']['anime']['episodesWatched'] }}</p>
                    <p>Days: {{ number_format($userData['statistics']['anime']['minutesWatched'] / 60 / 24, 1) }}</p>
                    <p>Mean Score: {{ $userData['statistics']['anime']['meanScore'] }}%</p>
                </div>

                <div class="stat-box">
                    <h3>📺 Manga Stats</h3>
                    <p>Count: {{ $userData['statistics']['manga']['count'] }}</p>
                    <p>Episodes: {{ $userData['statistics']['manga']['chaptersRead'] }}</p>
                    <p>Mean Score: {{ $userData['statistics']['manga']['meanScore'] }}%</p>
                </div>

                <div class="stat-box">
                    <h3>🏷️ Top 5 Anime Tags</h3>
                    @foreach($userData['statistics']['anime']['tags'] as $tagStat)
                        <p>
                            <strong>{{ $tagStat['tag']['name'] }}:</strong> 
                            {{ $tagStat['count'] }} entries 
                            (Avg: {{ $tagStat['meanScore'] }}%)
                        </p>
                    @endforeach
                </div>

                @if(!empty($userData['previousNames']))
                    <div class="stat-box">
                        <h3>Previous Username</h3>
                        @foreach($userData['previousNames'] as $prevName)
                            <p>
                                <strong>{{ $prevName['name']}}</strong><br>
                                <small>{{ date("l, F j, Y g:i A ", $prevName['createdAt'])}}</small>
                            </p>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</body>
</html>