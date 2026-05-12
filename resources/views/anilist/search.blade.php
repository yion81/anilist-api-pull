<!DOCTYPE html>
<html>
<head>
    <title>AniList Profile Search</title>
    <link rel="stylesheet" href="{{ asset('css/search.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Overpass:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav>
        <a href="{{ url('/') }}">Home</a>
        <a href="{{ url('/search') }}" class="active">Search User</a>
        <a href="{{ url('/tags') }}">Tag Bypass</a>
        <a href="{{ url('/myMangaProgress') }}">Don't open</a>
    </nav>

    <div class="search-container-centered">
        <form method="POST" action="{{ route('anilist.search') }}">
            @csrf 
            <input type="text" name="username_input" placeholder="Search user..." required>
            <input type="submit" value="Search">
        </form>
    </div>

    @if(isset($userData))
        
        <div class="profile-header">
            <img class="avatar" src="{{ $userData['avatar']['large'] }}" alt="Avatar">
            <div class="header-info">
                <h1 class="username">{{ $userData['name'] }}</h1>
                <a href="{{ $userData['siteUrl'] }}" target="_blank" style="color: #3db4f2; font-size: 0.9rem;">View on AniList &nearr;</a>
            </div>
        </div>

        <div class="content-container">
            
            <div class="sidebar">
                @if(!empty($userData['about']))
                    <div class="card-box bio-text">
                        {!! nl2br(e($userData['about'])) !!}
                    </div>
                @endif
                
                <div class="card-box">
                    <div class="label">Preference</div>
                    <div class="value value-highlight">
                        @if($userData['statistics']['anime']['count'] > $mangaStats['count'])
                            Anime Watcher
                        @else
                            Manga Reader
                        @endif
                    </div>
                </div>

                <div class="card-box">
                    <div class="label">Mood <span style="font-size:0.75rem; color:#8ba0b2;">(last 50 entries)</span></div>
                    <div class="value value-highlight" style="margin-bottom: 10px;">{{ $moodTop }}</div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        @foreach($moodCounts as $type => $count)
                            @if($count > 0)
                                @php $pct = round($count / 50 * 100); @endphp
                                <div>
                                    <div style="display:flex; justify-content:space-between; font-size:0.8rem; color:#8ba0b2; margin-bottom:3px;">
                                        <span>{{ $type }}</span><span>{{ $count }}</span>
                                    </div>
                                    <div style="background:#0b1622; border-radius:4px; height:6px; overflow:hidden;">
                                        <div style="width:{{ $pct }}%; height:100%; background:#3db4f2; border-radius:4px;"></div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                @php
                    $animeAvg = 68.2;
                    $a_score = $userData['statistics']['anime']['meanScore'];
                    $a_diff = $a_score - $animeAvg;
                    
                    // Default
                    $a_title = "The Standard"; $a_color = "#e1e6eb"; 
                    $a_desc = "Your ratings sit right in the global sweet spot (~68%).";

                    // POSITIVE (You rate higher than 68.2)
                    if ($a_diff >= 12) {
                        $a_title = "Hype Machine"; $a_color = "#4cd137";
                        $a_desc = "Everything is PEAK. You are having way more fun than everyone else.";
                    } elseif ($a_diff >= 7) {
                        $a_title = "Anime Enthusiast"; $a_color = "#4cd137";
                        $a_desc = "You genuinely love the medium. You rarely rate anything below a 7.";
                    } elseif ($a_diff >= 3) {
                        $a_title = "Lenient Viewer"; $a_color = "#81ecec";
                        $a_desc = "You tend to forgive bad animation if the story is fun.";
                    } 
                    // NEGATIVE (You rate lower than 68.2)
                    elseif ($a_diff <= -10) {
                        $a_title = "The Gatekeeper"; $a_color = "#e84118";
                        $a_desc = "Do you even watch for fun anymore?";
                    } elseif ($a_diff <= -6) {
                        $a_title = "Hard to Impress"; $a_color = "#e84118";
                        $a_desc = "Average seasonal shows bore you. You need substance.";
                    } elseif ($a_diff <= -2.5) {
                        $a_title = "Critical Eye"; $a_color = "#e1b12c";
                        $a_desc = "You analyze shows more than you simply 'watch' them.";
                    }
                @endphp
                <div class="card-box">
                    <div class="label">Anime Persona</div>
                    <div class="value" style="color: {{ $a_color }}; font-size: 1.1rem;">{{ $a_title }}</div>
                    <div style="font-size: 0.8rem; color: #8ba0b2; margin-top: 5px; line-height: 1.3;">
                        {{ $a_desc }}<br><span style="opacity: 0.7;">(Rate {{ number_format(abs($a_diff), 1) }}% {{ $a_diff > 0 ? 'higher' : 'lower' }} than avg)</span>
                    </div>
                </div>

                @php
                    $mangaAvg = 73.2;
                    $m_score = $mangaStats['meanScore'];
                    $m_diff = $m_score - $mangaAvg;
                    
                    // Default
                    $m_title = "The Standard"; $m_color = "#e1e6eb"; 
                    $m_desc = "Your ratings align perfectly with the manga average (~73%).";

                    // POSITIVE (You rate higher than 73.2)
                    if ($m_diff >= 10) {
                        $m_title = "Masterpiece Hunter"; $m_color = "#4cd137";
                        $m_desc = "You somehow only read 10/10 bangers, or you just love everything.";
                    } elseif ($m_diff >= 5) {
                        $m_title = "Vibes Reader"; $m_color = "#4cd137";
                        $m_desc = "If the art is good, you give it a good score. Simple as that.";
                    } elseif ($m_diff >= 2) {
                        $m_title = "Glass Half Full"; $m_color = "#81ecec";
                        $m_desc = "You prefer to focus on the good parts of a story.";
                    } 
                    // NEGATIVE (You rate lower than 73.2)
                    elseif ($m_diff <= -8) {
                        $m_title = "Scorched Earth"; $m_color = "#e84118";
                        $m_desc = "Do you read for fun? be honest.";
                    } elseif ($m_diff <= -4) {
                        $m_title = "Literary Critic"; $m_color = "#e84118";
                        $m_desc = "You treat manga like classic literature. Plot holes ruin it for you.";
                    } elseif ($m_diff <= -1.5) {
                        $m_title = "Nitpicker"; $m_color = "#e1b12c";
                        $m_desc = "You get annoyed by slow pacing and generic tropes.";
                    }
                @endphp

                @php
                    // --- ANIME CALCS ---
                    $scoreFormat = $userData['mediaListOptions']['scoreFormat'] ?? 'POINT_100';
                    $a_stats = collect($userData['statistics']['anime']['statuses']);
                    $a_comp = $a_stats->where('status', 'COMPLETED')->first()['count'] ?? 0;
                    $a_drop = $a_stats->where('status', 'DROPPED')->first()['count'] ?? 0;
                    $a_plan = $a_stats->where('status', 'PLANNING')->first()['count'] ?? 0;
                    $a_watch = $a_stats->where('status', 'CURRENT')->first()['count'] ?? 0;
                    $a_paused = $a_stats->where('status', 'PAUSED')->first()['count'] ?? 0;
                    
                    // FIX: Calculate Total Manually to prevent >100% errors
                    $a_real_total = $a_comp + $a_drop + $a_plan + $a_watch + $a_paused;
                    
                    $a_start = $a_comp + $a_drop;
                    $a_commit = $a_start > 0 ? round(($a_comp / $a_start) * 100) : 0;
                    $a_plan_pct = $a_real_total > 0 ? round(($a_plan / $a_real_total) * 100) : 0;
                    
                    // Count 10/10s
                    $a_maxScore = match($scoreFormat) {
                        'POINT_3'          => 3,
                        'POINT_5'          => 5,
                        'POINT_10'         => 10,
                        'POINT_10_DECIMAL' => 100, // stored internally as ×10, so 10.0 = 100
                        default            => 100, // POINT_100
                    };
                    $a_scores = collect($userData['statistics']['anime']['scores'] ?? []);
                    $a_tens = 0;
                    foreach($a_scores as $s) {
                        if($s['score'] == $a_maxScore) $a_tens += $s['count'];
                    }

                    // --- MANGA CALCS ---
                    $m_stats = collect($mangaStats['statuses']);
                    $m_comp = $m_stats->where('status', 'COMPLETED')->first()['count'] ?? 0;
                    $m_drop = $m_stats->where('status', 'DROPPED')->first()['count'] ?? 0;
                    $m_plan = $m_stats->where('status', 'PLANNING')->first()['count'] ?? 0;
                    $m_read = $m_stats->where('status', 'CURRENT')->first()['count'] ?? 0;
                    $m_paused = $m_stats->where('status', 'PAUSED')->first()['count'] ?? 0;

                    // FIX: Calculate Total Manually
                    $m_real_total = $m_comp + $m_drop + $m_plan + $m_read + $m_paused;

                    $m_start = $m_comp + $m_drop;
                    $m_commit = $m_start > 0 ? round(($m_comp / $m_start) * 100) : 0;
                    $m_plan_pct = $m_real_total > 0 ? round(($m_plan / $m_real_total) * 100) : 0;

                    // Count 10/10s (manga — same user, same score format as anime)
                    $m_scores = collect($mangaStats['scores']);
                    $m_tens = 0;
                    foreach($m_scores as $s) {
                        if($s['score'] == $a_maxScore) $m_tens += $s['count'];
                    }

                    // Days Logic
                    $now = time();
                    $joined = $userData['createdAt'];
                    $days = max(1, ($now - $joined) / 86400);
                    $years = $days / 365;
                    
                    $lastUpdate = $userData['updatedAt'];
                    $daysInactive = ($now - $lastUpdate) / 86400;

                    $epsDay = round($userData['statistics']['anime']['episodesWatched'] / $days, 2);
                    $chpDay = round($mangaStats['chaptersRead'] / $days, 2);
                    
                    $totalDaysWatched = $userData['statistics']['anime']['minutesWatched'] / 60 / 24;
                @endphp
                <div class="card-box">
                    <div class="label">Manga Persona</div>
                    <div class="value" style="color: {{ $m_color }}; font-size: 1.1rem;">{{ $m_title }}</div>
                    <div style="font-size: 0.8rem; color: #8ba0b2; margin-top: 5px; line-height: 1.3;">
                        {{ $m_desc }}<br><span style="opacity: 0.7;">(Rate {{ number_format(abs($m_diff), 1) }}% {{ $m_diff > 0 ? 'higher' : 'lower' }} than avg)</span>
                    </div>
                </div>

                <div class="card-box">
                    <div class="label">Joined Exact</div>
                    <div class="value" style="font-size: 0.9rem;">
                        {{ gmdate('F j, Y', $userData['createdAt']) }}<br>
                        <span style="color: #8ba0b2; font-size: 0.85rem;">{{ gmdate('H:i:s', $userData['createdAt']) }} UTC</span>
                    </div>
                </div>
                
                <div class="card-box">
                    <div class="label">Last Update</div>
                    <div class="value" style="font-size: 0.9rem;">
                        {{ gmdate('M j, Y', $userData['updatedAt']) }}<br>
                        <span style="color: #8ba0b2; font-size: 0.85rem;">{{ gmdate('H:i:s', $userData['updatedAt']) }} UTC</span>
                    </div>
                </div>
                <div class="card-box">
                    <div class="label">Time Inactive</div>
                    <div class="value" style="font-size: 0.9rem;">
                        @php
                            $totalSeconds = time() - intval($userData['updatedAt']);

                            if ($totalSeconds >= 365 * 86400) {
                                $years = floor($totalSeconds / (365 * 86400));
                                $remainingDays = floor(($totalSeconds % (365 * 86400)) / 86400);
                                $inactiveDisplay = $years . ' ' . ($years == 1 ? 'Year' : 'Years');
                                if ($remainingDays > 0) $inactiveDisplay .= ', ' . $remainingDays . ' ' . ($remainingDays == 1 ? 'Day' : 'Days');
                            } elseif ($totalSeconds >= 30 * 86400) {
                                $months = floor($totalSeconds / (30 * 86400));
                                $remainingDays = floor(($totalSeconds % (30 * 86400)) / 86400);
                                $inactiveDisplay = $months . ' ' . ($months == 1 ? 'Month' : 'Months');
                                if ($remainingDays > 0) $inactiveDisplay .= ', ' . $remainingDays . ' ' . ($remainingDays == 1 ? 'Day' : 'Days');
                            } elseif ($totalSeconds >= 86400) {
                                $days = floor($totalSeconds / 86400);
                                $remainingHours = floor(($totalSeconds % 86400) / 3600);
                                $inactiveDisplay = $days . ' ' . ($days == 1 ? 'Day' : 'Days');
                                if ($remainingHours > 0) $inactiveDisplay .= ', ' . $remainingHours . ' ' . ($remainingHours == 1 ? 'Hour' : 'Hours');
                            } elseif ($totalSeconds >= 3600) {
                                $hours = floor($totalSeconds / 3600);
                                $remainingMins = floor(($totalSeconds % 3600) / 60);
                                $remainingSecs = $totalSeconds % 60;
                                $inactiveDisplay = $hours . ' ' . ($hours == 1 ? 'Hour' : 'Hours');
                                if ($remainingMins > 0) $inactiveDisplay .= ', ' . $remainingMins . ' ' . ($remainingMins == 1 ? 'Minute' : 'Minutes');
                                if ($remainingSecs > 0) $inactiveDisplay .= ', ' . $remainingSecs . ' ' . ($remainingSecs == 1 ? 'Second' : 'Seconds');
                            } elseif ($totalSeconds >= 60) {
                                $minutes = floor($totalSeconds / 60);
                                $remainingSecs = $totalSeconds % 60;
                                $inactiveDisplay = $minutes . ' ' . ($minutes == 1 ? 'Minute' : 'Minutes');
                                if ($remainingSecs > 0) $inactiveDisplay .= ', ' . $remainingSecs . ' ' . ($remainingSecs == 1 ? 'Second' : 'Seconds');
                            } else {
                                $inactiveDisplay = $totalSeconds . ' ' . ($totalSeconds == 1 ? 'Second' : 'Seconds');
                            }
                        @endphp
                        {{ $inactiveDisplay }}
                    </div>
                </div>
                @if(!empty($userData['previousNames']))
                    <div class="card-box">
                        <div class="label">Previous Names</div>
                        <div class="value" style="font-size: 0.85rem; color: #a0b1c5; display: flex; flex-direction: column; gap: 8px;">
                            @foreach($userData['previousNames'] as $prev)
                                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #1a1c29; padding-bottom: 4px;">
                                    <span style="font-weight: bold; color: #bcbedc;">{{ $prev['name'] }}</span>
                                    <span style="font-size: 0.75rem; opacity: 0.5;">{{ gmdate('M Y', $prev['createdAt']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            

            <div class="main-section">
                
                <div>
                    <div class="section-title">Anime Stats</div>
                    <div class="stats-grid" style="grid-template-columns: repeat(5, 1fr);">
                        <div class="stat-block">
                            <h3>Count</h3><p>{{ number_format($userData['statistics']['anime']['count']) }}</p>
                        </div>
                        <div class="stat-block">
                            <h3>Days</h3><p>{{ number_format($animeMinutesBase / 60 / 24, 1) }}</p>
                        </div>
                        <div class="stat-block">
                            <h3>Rewatch Days</h3><p>{{ number_format($animeMinutesRewatch / 60 / 24, 1) }}</p>
                            @if($animeMinutesRewatch > 0)
                                <span style="font-size: 0.75rem; color: #8ba0b2;">
                                    {{ number_format($animeMinutesRewatch / ($animeMinutesBase + $animeMinutesRewatch) * 100, 2) }}% of total
                                </span>
                            @endif
                        </div>
                        <div class="stat-block">
                            <h3>Mean</h3>
                            <p style="color: #3db4f2;">{{ $userData['statistics']['anime']['meanScore'] }}% 
                            @if($userData['statistics']['anime']['meanScore'] > 0)
                                <span style="font-size: 0.8rem; vertical-align: middle; margin-left: 3px; {{ $a_diff > 0 ? 'color: #4cd137;' : ($a_diff < 0 ? 'color: #e84118;' : 'color: #8ba0b2;') }}">
                                    {{ $a_diff > 0 ? '+' : '' }}{{ $a_diff }}%
                                </span>
                            @endif
                            </p>
                        </div>
                        <div class="stat-block">
                            <h3>Deviation</h3><p>{{ $userData['statistics']['anime']['standardDeviation'] }}%</p>
                            @if($userData['statistics']['anime']['meanScore'] > 0)
                                <span style="font-size: 0.75rem; color: #8ba0b2;">
                                        {{ number_format(max(0, $userData['statistics']['anime']['meanScore'] - $userData['statistics']['anime']['standardDeviation']), 2) }}% - {{ number_format(min(100, $userData['statistics']['anime']['meanScore'] + $userData['statistics']['anime']['standardDeviation']), 2) }}%
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div>
                    <div class="section-title">Manga Stats</div>
                    <div class="stats-grid" style="grid-template-columns: repeat(5, 1fr);">
                        <div class="stat-block">
                            <h3>Count</h3><p>{{ number_format($mangaStats['count']) }}</p>
                        </div>
                        <div class="stat-block">
                            <h3>Chapters</h3><p>{{ number_format($mangaStats['chaptersBase']) }}</p>
                        </div>
                        <div class="stat-block">
                            <h3>Reread Ch.</h3><p>{{ number_format($mangaStats['chaptersReread']) }}</p>
                            @if($mangaStats['chaptersReread'] > 0)
                                <span style="font-size: 0.75rem; color: #8ba0b2;">
                                    {{ number_format($mangaStats['chaptersReread'] / ($mangaStats['chaptersBase'] + $mangaStats['chaptersReread']) * 100, 2) }}% of total
                                </span>
                            @endif
                        </div>
                        <div class="stat-block">
                            <h3>Mean</h3>
                            <p style="color: #3db4f2;">{{ $mangaStats['meanScore'] }}%
                            @if($mangaStats['meanScore'] > 0)
                                <span style="font-size: 0.8rem; vertical-align: middle; margin-left: 3px; {{ $m_diff > 0 ? 'color: #4cd137;' : ($m_diff < 0 ? 'color: #e84118;' : 'color: #8ba0b2;') }}">
                                    {{ $m_diff > 0 ? '+' : '' }}{{ $m_diff }}%
                                </span>
                            @endif
                            </p>
                        </div>
                        <div class="stat-block">
                            <h3>Deviation</h3><p>{{ $mangaStats['standardDeviation'] }}%</p>
                            @if($mangaStats['meanScore'] > 0)
                                <span style="font-size: 0.75rem; color: #8ba0b2;">
                                        {{ number_format(max(0, $mangaStats['meanScore'] - $mangaStats['standardDeviation']), 2) }}% - {{ number_format(min(100, $mangaStats['meanScore'] + $mangaStats['standardDeviation']), 2) }}%
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                @php
                    $novelAvg = 80;
                    $ln_diff  = round($novelStats['meanScore'] - $novelAvg, 1);
                @endphp
                <div>
                    <div class="section-title">Light Novel Stats</div>
                    @if($novelStats['count'] === 0)
                        <p style="color: #8ba0b2; font-size: 0.95rem; padding: 10px 0;">No light novels on this list.</p>
                    @else
                        <div class="stats-grid" style="grid-template-columns: repeat(5, 1fr);">
                            <div class="stat-block">
                                <h3>Count</h3><p>{{ number_format($novelStats['count']) }}</p>
                            </div>
                            <div class="stat-block">
                                <h3>Chapters</h3><p>{{ number_format($novelStats['chaptersBase']) }}</p>
                            </div>
                            <div class="stat-block">
                                <h3>Reread Ch.</h3><p>{{ number_format($novelStats['chaptersReread']) }}</p>
                                @if($novelStats['chaptersReread'] > 0)
                                    <span style="font-size: 0.75rem; color: #8ba0b2;">
                                        {{ number_format($novelStats['chaptersReread'] / ($novelStats['chaptersBase'] + $novelStats['chaptersReread']) * 100, 2) }}% of total
                                    </span>
                                @endif
                            </div>
                            <div class="stat-block">
                                <h3>Mean</h3>
                                <p style="color: #3db4f2;">{{ $novelStats['meanScore'] }}%
                                @if($novelStats['meanScore'] > 0)
                                    <span style="font-size: 0.8rem; vertical-align: middle; margin-left: 3px; {{ $ln_diff > 0 ? 'color: #4cd137;' : ($ln_diff < 0 ? 'color: #e84118;' : 'color: #8ba0b2;') }}">
                                        {{ $ln_diff > 0 ? '+' : '' }}{{ $ln_diff }}%
                                    </span>
                                @endif
                                </p>
                            </div>
                            <div class="stat-block">
                                <h3>Deviation</h3><p>{{ $novelStats['standardDeviation'] }}%</p>
                                @if($novelStats['meanScore'] > 0)
                                    <span style="font-size: 0.75rem; color: #8ba0b2;">
                                        {{ number_format(max(0, $novelStats['meanScore'] - $novelStats['standardDeviation']), 2) }}% - {{ number_format(min(100, $novelStats['meanScore'] + $novelStats['standardDeviation']), 2) }}%
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                <div>
                    <div class="section-title">Score Distribution</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 10px;">

                        {{-- ANIME — rendered in user's native score format --}}
                        <div class="card-box" style="padding: 15px;">
                            <div class="label" style="margin-bottom: 12px;">📺 Anime</div>
                            @php
                                $a_score_map = [];
                                foreach($userData['statistics']['anime']['scores'] as $s) {
                                    $a_score_map[$s['score']] = $s['count'];
                                }

                                // Build range, thresholds, and labels based on the user's score format
                                switch($scoreFormat) {
                                    case 'POINT_3':
                                        $a_range      = [1, 2, 3];
                                        $a_high       = 3; $a_mid = 2;
                                        $a_labelFn    = fn($s) => ['1' => '☹', '2' => '😐', '3' => '😊'][$s];
                                        $a_labelSize  = '1rem';
                                        break;
                                    case 'POINT_5':
                                        $a_range      = range(1, 5);
                                        $a_high       = 4; $a_mid = 3;
                                        $a_labelFn    = fn($s) => $s;
                                        $a_labelSize  = '0.7rem';
                                        break;
                                    case 'POINT_10':
                                        $a_range      = range(1, 10);
                                        $a_high       = 7; $a_mid = 4;
                                        $a_labelFn    = fn($s) => $s;
                                        $a_labelSize  = '0.6rem';
                                        break;
                                    case 'POINT_10_DECIMAL':
                                        // AniList stores 6.5 as 65 internally; 0.5 is stored as 5
                                        $a_range      = range(5, 100, 5);
                                        $a_high       = 70; $a_mid = 40;
                                        $a_labelFn    = fn($s) => number_format($s / 10, 1);
                                        $a_labelSize  = '0.5rem';
                                        break;
                                    default: // POINT_100
                                        $a_range      = range(5, 100, 5);
                                        $a_high       = 70; $a_mid = 40;
                                        $a_labelFn    = fn($s) => $s;
                                        $a_labelSize  = '0.6rem';
                                        break;
                                }

                                $a_max_count = !empty($a_score_map) ? max($a_score_map) : 1;
                            @endphp

                            <div style="display: flex; align-items: flex-end; gap: 2px; height: 80px;">
                                @foreach($a_range as $score)
                                    @php
                                        $count    = $a_score_map[$score] ?? 0;
                                        $barColor = $score >= $a_high ? '#3db4f2' : ($score >= $a_mid ? '#e1b12c' : '#e84118');
                                        $barPct   = $a_max_count > 0 ? round(($count / $a_max_count) * 100) : 0;
                                    @endphp
                                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 3px; height: 100%;">
                                        <div style="flex: 1; display: flex; align-items: flex-end; width: 100%;">
                                            <div title="{{ $score }}: {{ $count }}"
                                                style="width: 100%; height: {{ $barPct }}%;
                                                    background: {{ $barColor }};
                                                    border-radius: 3px 3px 0 0;
                                                    min-height: {{ $count > 0 ? '3px' : '0' }};">
                                            </div>
                                        </div>
                                        <div style="font-size: {{ $a_labelSize }}; color: #8ba0b2;">
                                            {{ $a_labelFn($score) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 0.7rem; color: #8ba0b2;">
                                <span><span style="color:#e84118;">■</span> Low</span>
                                <span><span style="color:#e1b12c;">■</span> Mid</span>
                                <span><span style="color:#3db4f2;">■</span> High</span>
                            </div>
                        </div>

                        {{-- MANGA — native score format, same as anime --}}
                        <div class="card-box" style="padding: 15px;">
                            <div class="label" style="margin-bottom: 12px;">📖 Manga</div>
                            @php
                                $m_score_map = [];
                                foreach($mangaStats['scores'] as $s) {
                                    $m_score_map[$s['score']] = $s['count'];
                                }
                                $m_max_count = !empty($m_score_map) ? max($m_score_map) : 1;
                            @endphp
                            <div style="display: flex; align-items: flex-end; gap: 2px; height: 80px;">
                                @foreach($a_range as $score)
                                    @php
                                        $count    = $m_score_map[$score] ?? 0;
                                        $barColor = $score >= $a_high ? '#3db4f2' : ($score >= $a_mid ? '#e1b12c' : '#e84118');
                                        $barPct   = $m_max_count > 0 ? round(($count / $m_max_count) * 100) : 0;
                                    @endphp
                                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 3px; height: 100%;">
                                        <div style="flex: 1; display: flex; align-items: flex-end; width: 100%;">
                                            <div title="{{ $score }}: {{ $count }}"
                                                style="width: 100%; height: {{ $barPct }}%;
                                                    background: {{ $barColor }};
                                                    border-radius: 3px 3px 0 0;
                                                    min-height: {{ $count > 0 ? '3px' : '0' }};">
                                            </div>
                                        </div>
                                        <div style="font-size: {{ $a_labelSize }}; color: #8ba0b2;">
                                            {{ $a_labelFn($score) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 0.7rem; color: #8ba0b2;">
                                <span><span style="color:#e84118;">■</span> Low</span>
                                <span><span style="color:#e1b12c;">■</span> Mid</span>
                                <span><span style="color:#3db4f2;">■</span> High</span>
                            </div>
                        </div>

                        {{-- LIGHT NOVEL — native score format, same as anime --}}
                        <div class="card-box" style="padding: 15px;">
                            <div class="label" style="margin-bottom: 12px;">📚 Light Novel</div>
                            @if($novelStats['count'] === 0)
                                <div style="height: 80px; display: flex; align-items: center; justify-content: center; color: #8ba0b2; font-size: 0.85rem;">
                                    No light novels on this list.
                                </div>
                            @else
                                @php
                                    $ln_score_map = [];
                                    foreach($novelStats['scores'] as $s) {
                                        $ln_score_map[$s['score']] = $s['count'];
                                    }
                                    $ln_max_count = !empty($ln_score_map) ? max($ln_score_map) : 1;
                                @endphp
                                <div style="display: flex; align-items: flex-end; gap: 2px; height: 80px;">
                                    @foreach($a_range as $score)
                                        @php
                                            $count    = $ln_score_map[$score] ?? 0;
                                            $barColor = $score >= $a_high ? '#3db4f2' : ($score >= $a_mid ? '#e1b12c' : '#e84118');
                                            $barPct   = $ln_max_count > 0 ? round(($count / $ln_max_count) * 100) : 0;
                                        @endphp
                                        <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 3px; height: 100%;">
                                            <div style="flex: 1; display: flex; align-items: flex-end; width: 100%;">
                                                <div title="{{ $score }}: {{ $count }}"
                                                    style="width: 100%; height: {{ $barPct }}%;
                                                        background: {{ $barColor }};
                                                        border-radius: 3px 3px 0 0;
                                                        min-height: {{ $count > 0 ? '3px' : '0' }};">
                                                </div>
                                            </div>
                                            <div style="font-size: {{ $a_labelSize }}; color: #8ba0b2;">
                                                {{ $a_labelFn($score) }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 0.7rem; color: #8ba0b2;">
                                    <span><span style="color:#e84118;">■</span> Low</span>
                                    <span><span style="color:#e1b12c;">■</span> Mid</span>
                                    <span><span style="color:#3db4f2;">■</span> High</span>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                <div class="habits-split">
                    <div class="habit-card">
                        <div class="section-title" style="border: none; padding: 0; margin-bottom: 15px;">📺 Anime Habits</div>
                        <div class="habit-row">
                            <span class="habit-label">Commitment</span>
                            <span class="habit-val" style="{{ $a_commit > 80 ? 'color:#4cd137' : ($a_commit < 50 ? 'color:#e84118' : '') }}">{{ $a_commit }}%</span>
                        </div>
                        <div class="habit-row">
                            <span class="habit-label">Planning List</span>
                            <span class="habit-val" style="{{ $a_plan_pct > 40 ? 'color:#e84118' : ($a_plan_pct < 10 ? 'color:#4cd137' : '') }}">{{ $a_plan_pct }}%</span>
                        </div>
                        <div class="habit-row">
                            <span class="habit-label">Daily Avg</span>
                            <span class="habit-val">{{ $epsDay }} eps</span>
                        </div>
                    </div>

                    <div class="habit-card">
                        <div class="section-title" style="border: none; padding: 0; margin-bottom: 15px;">📖 Manga Habits</div>
                        <div class="habit-row">
                            <span class="habit-label">Commitment</span>
                            <span class="habit-val" style="{{ $m_commit > 80 ? 'color:#4cd137' : ($m_commit < 50 ? 'color:#e84118' : '') }}">{{ $m_commit }}%</span>
                        </div>
                        <div class="habit-row">
                            <span class="habit-label">Planning List</span>
                            <span class="habit-val" style="{{ $m_plan_pct > 40 ? 'color:#e84118' : ($m_plan_pct < 10 ? 'color:#4cd137' : '') }}">{{ $m_plan_pct }}%</span>
                        </div>
                        <div class="habit-row">
                            <span class="habit-label">Daily Avg</span>
                            <span class="habit-val">{{ $chpDay }} ch.</span>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="section-title">Achievements</div>
                    
                    @if($a_tens == 1 || $m_tens == 1)
                        <div style="margin-bottom: 20px;">
                            <div class="achievement-card achievement-gold" style="justify-content: center; text-align: center; border-width: 2px; padding: 20px;">
                                <div class="gold-icon" style="font-size: 2.5rem;">💎</div>
                                <div>
                                    <div class="gold-title" style="font-size: 1.3rem;">The Chosen One</div>
                                    <div style="font-size:1rem; color:#fff; margin-bottom: 5px;">You have found the true masterpiece.</div>
                                    <div class="award-note">(Awarded because you have only 1 10/10 in either anime or manga, or both)</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($a_tens == 0 && $m_tens == 0)
                        <div style="margin-bottom: 20px;">
                            <div class="achievement-card achievement-red" style="justify-content: center; text-align: center; border-width: 2px; padding: 20px;">
                                <div class="gold-icon" style="font-size: 2.5rem;">?</div>
                                <div>
                                    <div class="gold-title" style="font-size: 1.3rem;">Erm... not a single 10/10?</div>
                                    <div style="font-size:1rem; color:#fff; margin-bottom: 5px;">How weird...</div>
                                    <div class="award-note">(Awarded because you have no 10/10 in either anime or manga, or both)</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($daysInactive > 365)
                        <div style="margin-bottom: 20px;">
                            <div class="achievement-card achievement-red" style="justify-content: center; text-align: center; border-width: 2px; padding: 20px;">
                                <div class="red-icon" style="font-size: 2.5rem;">👻</div>
                                <div>
                                    <div class="red-title" style="font-size: 1.3rem;">The Ghost</div>
                                    <div style="font-size:1rem; color:#fff; margin-bottom: 5px;">Burnt out? Lol</div>
                                    <div class="award-note">(Inactive for > 1 year)</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="habits-split">
                        
                        <div class="habit-card" style="gap: 15px;">
                            <div class="section-title" style="border: none; padding: 0;">📺 Anime Badges</div>

                            @if($a_real_total > 0 && ($a_tens / $a_real_total) > 0.20)
                                <div class="achievement-card achievement-red">
                                    <div class="red-icon">🧢</div>
                                    <div>
                                        <div class="red-title">Surely You Jest</div>
                                        <div style="font-size:0.9rem; color:#fff;">ALL of these are 10/10s? I doubt it.</div>
                                        <div class="award-note">(Awarded because >20% of your list is 10/10)</div>
                                    </div>
                                </div>
                            @endif

                            @if($a_commit > 80)
                                <div class="achievement-card achievement-gold">
                                    <div class="gold-icon">🏆</div>
                                    <div>
                                        <div class="gold-title">The Finisher</div>
                                        <div style="font-size:0.9rem; color:#fff;">Completionist mindset.</div>
                                        <div class="award-note">(>80% completion rate)</div>
                                    </div>
                                </div>
                            @elseif($a_commit < 50 && $a_total > 10)
                                <div class="achievement-card achievement-red">
                                    <div class="red-icon">✂️</div>
                                    <div>
                                        <div class="red-title">Serial Dropper</div>
                                        <div style="font-size:0.9rem; color:#fff;">Commitment issues detected.</div>
                                        <div class="award-note">(<50% completion rate)</div>
                                    </div>
                                </div>
                            @endif

                             @if($a_plan_pct > 40)
                                <div class="achievement-card achievement-red">
                                    <div class="red-icon">🙄</div>
                                    <div>
                                        <div class="red-title">The Collector</div>
                                        <div style="font-size:0.9rem; color:#fff;">Just watching trailers?</div>
                                        <div class="award-note">(>40% planning list)</div>
                                    </div>
                                </div>
                            @endif

                        </div>

                        <div class="habit-card" style="gap: 15px;">
                            <div class="section-title" style="border: none; padding: 0;">📖 Manga Badges</div>

                            @if($m_real_total > 0 && ($m_tens / $m_real_total) > 0.20)
                                <div class="achievement-card achievement-red">
                                    <div class="red-icon">🧢</div>
                                    <div>
                                        <div class="red-title">Surely You Jest</div>
                                        <div style="font-size:0.9rem; color:#fff;">ALL of these are 10/10s? I doubt it.</div>
                                        <div class="award-note">(Awarded because >20% of your list is 10/10)</div>
                                    </div>
                                </div>
                            @endif

                            @if($chpDay > 10)
                                <div class="achievement-card achievement-gold">
                                    <div class="gold-icon">🏎️</div>
                                    <div>
                                        <div class="gold-title">Speed Reader</div>
                                        <div style="font-size:0.9rem; color:#fff;">10+ chapters every day.</div>
                                        <div class="award-note">(Avg > 10 ch/day)</div>
                                    </div>
                                </div>
                            @endif

                            @if($m_commit > 80)
                                <div class="achievement-card achievement-gold">
                                    <div class="gold-icon">🏆</div>
                                    <div>
                                        <div class="gold-title">The Finisher</div>
                                        <div style="font-size:0.9rem; color:#fff;">No chapter left behind.</div>
                                        <div class="award-note">(>80% completion rate)</div>
                                    </div>
                                </div>
                            @elseif($m_commit < 50 && $m_real_total > 10)
                                <div class="achievement-card achievement-red">
                                    <div class="red-icon">✂️</div>
                                    <div>
                                        <div class="red-title">Serial Dropper</div>
                                        <div style="font-size:0.9rem; color:#fff;">You get bored easily.</div>
                                        <div class="award-note">(<50% completion rate)</div>
                                    </div>
                                </div>
                            @endif

                            @if($m_plan_pct > 40)
                                <div class="achievement-card achievement-red">
                                    <div class="red-icon">📚</div>
                                    <div>
                                        <div class="red-title">Library Builder</div>
                                        <div style="font-size:0.9rem; color:#fff;">You prefer covers over pages.</div>
                                        <div class="award-note">(>40% planning list)</div>
                                    </div>
                                </div>
                            @elseif($m_plan_pct < 10 && $m_real_total > 10)
                                <div class="achievement-card achievement-blue">
                                    <div class="blue-icon">⚡</div>
                                    <div>
                                        <div class="blue-title">Do you even plan?</div>
                                        <div style="font-size:0.9rem; color:#fff;">Impulse reader.</div>
                                        <div class="award-note">(<10% planning list)</div>
                                    </div>
                                </div>
                            @endif

                            @if($m_commit <= 80 && $m_commit >= 50 && $m_plan_pct <= 40 && $chpDay <= 10 && $m_tens == 0)
                                <div class="achievement-card" style="border-color: #3db4f2;">
                                    <div style="font-size: 1.8rem;">🍵</div>
                                    <div>
                                        <div class="blue-title">Casual Reader</div>
                                        <div style="font-size:0.9rem; color:#a0b1c5;">Perfectly balanced habit.</div>
                                        <div class="award-note">(No extremes found)</div>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div style="margin-top: 30px; padding: 0 20px;">
            <div style="width: 100%;">
                <div class="section-title" style="font-size: 1.1rem;">📖 Stat Glossary</div>
                <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-top: 15px;"">

                    <div class="card-box">
                        <div class="label">Preference</div>
                        <div style="font-size: 0.85rem; color: #8ba0b2; line-height: 1.5;">Whether you have more anime or manga entries on your list. Simple count comparison.</div>
                    </div>

                    <div class="card-box">
                        <div class="label">Anime / Manga Persona</div>
                        <div style="font-size: 0.85rem; color: #8ba0b2; line-height: 1.5;">Based on your mean score compared to the global AniList average (<strong style="color:#a0b1c5;">Anime: 68.2 | Manga: 73.2 | LN: 80</strong>). Positive diff = you rate higher than average, negative = stricter.</div>
                    </div>

                    <div class="card-box">
                        <div class="label">Mean Score (+/- diff)</div>
                        <div style="font-size: 0.85rem; color: #8ba0b2; line-height: 1.5;">Your average rating across all scored entries. The colored diff shows how far you are from the global average. <span style="color:#4cd137;">Green = above avg</span>, <span style="color:#e84118;">red = below avg</span>.</div>
                    </div>

                    <div class="card-box">
                        <div class="label">Standard Deviation</div>
                        <div style="font-size: 0.85rem; color: #8ba0b2; line-height: 1.5;">How spread out your scores are. Low = you give similar scores to everything. High = you clearly distinguish between what you love and hate.</div>
                    </div>

                    <div class="card-box">
                        <div class="label">Commitment %</div>
                        <div style="font-size: 0.85rem; color: #8ba0b2; line-height: 1.5;">Of everything you've started (completed + dropped), how much did you actually finish? <span style="color:#4cd137;">>80% = Finisher</span>, <span style="color:#e84118;"><50% = Serial Dropper</span>.</div>
                    </div>

                    <div class="card-box">
                        <div class="label">Planning List %</div>
                        <div style="font-size: 0.85rem; color: #8ba0b2; line-height: 1.5;">How much of your total list is sitting in "Plan to Watch/Read". <span style="color:#e84118;">>40%</span> is questionable</div>
                    </div>

                    <div class="card-box">
                        <div class="label">Daily Avg</div>
                        <div style="font-size: 0.85rem; color: #8ba0b2; line-height: 1.5;">Total episodes watched (or chapters read) divided by the number of days since you joined AniList. Accurate if you have anilist when you startetd watching/reading.</div>
                    </div>

                    <div class="card-box">
                        <div class="label">Time Inactive</div>
                        <div style="font-size: 0.85rem; color: #8ba0b2; line-height: 1.5;">Time since your last list activity on AniList. Calculated from the <code style="color:#3db4f2; font-size:0.8rem;">updatedAt</code> timestamp on your profile.</div>
                    </div>

                    <div class="card-box">
                        <div class="label">Joined Exact</div>
                        <div style="font-size: 0.85rem; color: #8ba0b2; line-height: 1.5;">The exact date and UTC time your AniList account was created, down to the second.</div>
                    </div>

                    <div class="card-box">
                        <div class="label">Achievements & Badges</div>
                        <div style="font-size: 0.85rem; color: #8ba0b2; line-height: 1.5;">Awarded automatically based on your stats. <span style="color:#f9ca24;">Gold</span> = positive trait, <span style="color:#e84118;">Red</span> = questionable habit, <span style="color:#3db4f2;">Blue</span> = neutral observation.</div>
                    </div>

                </div>
            </div>
        </div>
    @else
        <div style="text-align: center; padding-top: 100px;">
            <h1 style="color: #e1e6eb;">Search for a User</h1>
            <p style="color: #8ba0b2;">Enter a username above.</p>
        </div>
    @endif
</body>
</html>