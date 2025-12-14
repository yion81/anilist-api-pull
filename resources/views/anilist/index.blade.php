<!DOCTYPE html>
<html>
<head>
    <title>My Manga App</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
</head>
<body>
    <nav>
        <a href="{{ url('/') }}">Home</a> | 
        <a href="{{ url('/search') }}">Search User's Anilist</a> | 
        <a href="{{ url('/myMangaProgress') }}">My Currently Reading</a>
    </nav>

    <h1>Welcome to Yion's Manga List</h1>

    <div class="welcome-box">
        <p>I am currently learning PHP and APIs.</p>
        <p>Click "Search User" above to try my tool!</p>
    </div>

</body>
</html>