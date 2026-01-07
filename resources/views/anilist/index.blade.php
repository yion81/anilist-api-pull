<!DOCTYPE html>
<html>
<head>
    <title>Yion's Anilist Tools</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Overpass:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav>
        <a href="{{ url('/') }}" class="active">Home</a>
        <a href="{{ url('/search') }}">Search User</a>
        <a href="{{ url('/tags') }}">Tag Bypass</a>
        <a href="{{ url('/myMangaProgress') }}">Don't open</a>
    </nav>

    <div class="container">
        
        <div class="hero-section">
            <h1>Yion's Anilist Tools Stuff</h1>
            <p class="subtitle">(through anilist api)</p>
        </div>

        <div class="cta-box">
            <p>
                You are probably here to see the <strong>Anilist Tag Bypass</strong>.
                <br><span style="font-size: 0.9em; color: #8ba0b2;">(Since anilist limits it to 30)</span>
            </p>
            
            <a href="{{ url('/tags') }}" class="btn-primary">Click me to go there &rarr;</a>
        </div>

    </div>

</body>
</html>