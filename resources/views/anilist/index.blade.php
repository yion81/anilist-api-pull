<!DOCTYPE html>
<html>
<head>
    <title>API Pull Project (Anilist)</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
</head>
<body>
    <nav>
        <a href="{{ url('/') }}">Home</a> | 
        <a href="{{ url('/search') }}">Search User's Anilist</a> | 
        <a href="{{ url('/myMangaProgress') }}">My Currently Reading</a>
    </nav>

    <h1>API Pull Project (Anilist)</h1>

    <div class="welcome-box">
        <p>I am currently learning PHP, APIs and Laravel.</p>
        <p>Click "Search User" above to search user and view some Info</p>
        <p>Click "My Currently Reading" above to see this website pulling my currently reading progress!</p>
    </div>

</body>
</html>