<?php

namespace App\Http\Controllers; // this code lives on this route

// imports
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AniListController extends Controller
{
    public function index()
    {
        return view('anilist.index');
    }

    public function searchView()
    {
        return view('anilist.search');
    }

    public function processSearch(Request $request)
    {
        $request->validate([
            'username_input' => 'required|string',
        ]);

        $searchName = $request->input('username_input');
        return redirect()->route('anilist.result', ['username' => $searchName]);
    }

    public function showResult($username)
    {
        $query = '
        query ($name: String) {
            User (name: $name) {
                id
                name
                about
                avatar {
                    large
                }
                siteUrl
                createdAt
                updatedAt              # <--- Needed for "Last Update"
                mediaListOptions {
                    scoreFormat
                }
                statistics {
                    anime {
                        count
                        minutesWatched
                        episodesWatched
                        meanScore
                        standardDeviation
                        
                        # Needed for "The Chosen One" calculation
                        scores {
                            score
                            count
                        }

                        # Needed for "Primary Format"
                        formats(sort: COUNT_DESC) {
                            format
                            count
                        }

                        # Needed for "Commitment" & "Planning" stats
                        statuses(sort: COUNT_DESC) {
                            status
                            count
                        }
                    }
                    manga {
                        count
                        chaptersRead
                        meanScore
                        standardDeviation
                        
                        scores {
                            score
                            count
                        }

                        statuses(sort: COUNT_DESC) {
                            status
                            count
                        }
                    }
                }

                # Needed for "Total Favorites" count
                favourites {
                    anime { pageInfo { total } }
                    manga { pageInfo { total } }
                    characters { pageInfo { total } }
                    staff { pageInfo { total } }
                }
                
                previousNames {
                    name
                    createdAt
                }
            }
        }';

        $response = Http::post('https://graphql.anilist.co', [
            'query' => $query,
            'variables' => [
                'name' => $username,
            ],
        ]);
        // Unpackage
        $data = $response->json();

        if (isset($data['errors'])) {
            return redirect()->route('anilist.search')->withErrors(['api_error' => 'User not found or API error.']);
        }

        // Score format the user has set on their AniList profile
        $scoreFormat = $data['data']['User']['mediaListOptions']['scoreFormat'] ?? 'POINT_100';

        // Multiplier to normalize any native score → POINT_100 for mean/deviation math,
        // so persona comparisons against global averages stay consistent regardless of format.
        // Note: MediaListCollection `score` (no format arg) returns actual decimals for POINT_10_DECIMAL
        // (e.g. 7.5), unlike statistics.scores which returns ×10 integers (75). So we multiply by 10.
        $toHundred = match($scoreFormat) {
            'POINT_3'          => 100 / 3,
            'POINT_5'          => 20.0,
            'POINT_10'         => 10.0,
            'POINT_10_DECIMAL' => 10.0,  // MediaListCollection returns 7.5 → ×10 = 75
            default            => 1.0,   // POINT_100
        };

        // Normalizes a raw MediaListCollection score to the bucket key scale used by the chart.
        // POINT_10_DECIMAL floats (7.5) need ×10 → 75 to match range(5,100,5) like statistics.scores.
        // All other formats are plain integers that already match their chart ranges as-is.
        $toBucketKey = $scoreFormat === 'POINT_10_DECIMAL'
            ? fn($s) => (int) round($s * 10)
            : fn($s) => (int) $s;

        // --- PURE MANGA + LIGHT NOVEL STATS ---
        // AniList's User.statistics.manga bundles novels (format: NOVEL) together with manga.
        // We fetch the full manga-type list and split by format client-side so each gets clean numbers.
        $mangaListQuery = '
        query ($name: String) {
            MediaListCollection(userName: $name, type: MANGA) {
                lists {
                    isCustomList
                    entries {
                        status
                        score
                        progress
                        repeat
                        media { format chapters title { english romaji } }
                    }
                }
            }
        }';

        $mangaListResponse = Http::post('https://graphql.anilist.co', [
            'query'     => $mangaListQuery,
            'variables' => ['name' => $username],
        ]);
        $mangaListData = $mangaListResponse->json();

        $emptyStats  = ['count' => 0, 'chaptersRead' => 0, 'meanScore' => 0, 'standardDeviation' => 0, 'statuses' => [], 'scores' => []];
        $mangaStats  = $emptyStats;
        $novelStats  = $emptyStats;
        if (!isset($mangaListData['errors'])) {
            $pureMangas = [];
            $novels     = [];
            // $allFormats = [];
            foreach ($mangaListData['data']['MediaListCollection']['lists'] as $list) {
                if ($list['isCustomList']) continue; // skip — duplicates of status lists
                foreach ($list['entries'] as $entry) {
                    // $f = $entry['media']['format'] ?? 'NULL';
                    // $allFormats[$f] = ($allFormats[$f] ?? 0) + 1;
                    if ($entry['media']['format'] === 'NOVEL') {
                        $novels[] = $entry;
                    } else {
                        $pureMangas[] = $entry;
                    }
                }
            }
            // dd($allFormats);
            // Computes count, chapters, mean, deviation, statuses, and score buckets from a flat entry array.
            // Mean/deviation are normalized to POINT_100 scale so persona comparisons work for all formats.
            // Score buckets keep native values so the chart bars display what the user actually typed.
            $computeStats = function(array $entries) use ($toHundred, $toBucketKey): array {
                $count    = count($entries);
                $chaptersBase   = 0;
                $chaptersReread = 0;
                foreach ($entries as $e) {
                    $currentRead = ($e['status'] === 'COMPLETED')
                        ? ($e['media']['chapters'] ?? $e['progress'])
                        : $e['progress'];
                    $perRead = $e['media']['chapters'] ?? $currentRead;
                    $chaptersBase   += $currentRead;
                    $chaptersReread += $perRead * $e['repeat'];
                }
                $chapters = $chaptersBase + $chaptersReread;

                // Native scores → for distribution chart buckets
                $nativeScores = array_values(array_filter(array_column($entries, 'score'), fn($s) => $s > 0));

                // Normalized to POINT_100 → for mean/deviation math against global averages
                $normalizedScores = array_map(fn($s) => round($s * $toHundred, 1), $nativeScores);
                $scored = count($normalizedScores);
                $mean   = $scored > 0 ? round(array_sum($normalizedScores) / $scored, 1) : 0;

                $dev = 0;
                if ($scored > 0) {
                    $variance = array_sum(array_map(fn($s) => ($s - $mean) ** 2, $normalizedScores)) / $scored;
                    $dev = round(sqrt($variance), 2);
                }

                // Status breakdown (for habits section in the blade)
                $statusCounts = [];
                foreach ($entries as $e) {
                    $s = $e['status'];
                    $statusCounts[$s] = ($statusCounts[$s] ?? 0) + 1;
                }
                $statuses = array_map(
                    fn($status, $cnt) => ['status' => $status, 'count' => $cnt],
                    array_keys($statusCounts),
                    $statusCounts
                );

                // Score buckets (native values normalized to chart key scale for display)
                $scoreBuckets = [];
                foreach ($entries as $e) {
                    if ($e['score'] > 0) {
                        $sc = $toBucketKey($e['score']);
                        $scoreBuckets[$sc] = ($scoreBuckets[$sc] ?? 0) + 1;
                    }
                }
                $scoreList = array_map(
                    fn($score, $cnt) => ['score' => $score, 'count' => $cnt],
                    array_keys($scoreBuckets),
                    $scoreBuckets
                );

                return [
                    'count'             => $count,
                    'chaptersRead'      => $chapters,
                    'chaptersBase'      => $chaptersBase,
                    'chaptersReread'    => $chaptersReread,
                    'meanScore'         => $mean,
                    'standardDeviation' => $dev,
                    'statuses'          => $statuses,
                    'scores'            => $scoreList,
                ];
            };

            $mangaStats = $computeStats($pureMangas);
            $novelStats = $computeStats($novels);
        }

        // --- ANIME REWATCH STATS ---
        // User.statistics.anime.minutesWatched already includes rewatches.
        // We fetch the raw anime list to split base watch time from rewatch time.
        $animeListQuery = '
        query ($name: String) {
            MediaListCollection(userName: $name, type: ANIME) {
                lists {
                    isCustomList
                    entries {
                        status
                        progress
                        repeat
                        media { episodes duration }
                    }
                }
            }
        }';

        $animeListResponse = Http::post('https://graphql.anilist.co', [
            'query'     => $animeListQuery,
            'variables' => ['name' => $username],
        ]);
        $animeListData = $animeListResponse->json();

        $animeMinutesBase    = 0;
        $animeMinutesRewatch = 0;

        if (!isset($animeListData['errors'])) {
            foreach ($animeListData['data']['MediaListCollection']['lists'] as $list) {
                if ($list['isCustomList']) continue;
                foreach ($list['entries'] as $entry) {
                    $eps = ($entry['status'] === 'COMPLETED')
                        ? ($entry['media']['episodes'] ?? $entry['progress'])
                        : $entry['progress'];
                    $duration = $entry['media']['duration'] ?? 0;
                    $animeMinutesBase    += $eps * $duration;
                    $animeMinutesRewatch += $entry['repeat'] * ($entry['media']['episodes'] ?? $eps) * $duration;
                }
            }
        }

        // --- MOOD (50 most recent activity events via Activity feed) ---
        // Using activity feed instead of MediaListCollection so the same series
        // can be counted multiple times if updated repeatedly (no distinct).
        $userId = $data['data']['User']['id'];
        $activityQuery = '
        query ($userId: Int) {
            Page(perPage: 50) {
                activities(userId: $userId, type: MEDIA_LIST, sort: ID_DESC) {
                    ... on ListActivity {
                        media {
                            type
                            format
                        }
                    }
                }
            }
        }';

        $activityResponse = Http::post('https://graphql.anilist.co', [
            'query'     => $activityQuery,
            'variables' => ['userId' => $userId],
        ]);
        $activityData = $activityResponse->json();

        $moodCounts = ['Anime' => 0, 'Manga' => 0, 'Light Novel' => 0];
        if (!isset($activityData['errors'])) {
            foreach ($activityData['data']['Page']['activities'] as $activity) {
                if (!isset($activity['media'])) continue; // skip non-media activities
                if ($activity['media']['type'] === 'ANIME') {
                    $moodCounts['Anime']++;
                } elseif ($activity['media']['format'] === 'NOVEL') {
                    $moodCounts['Light Novel']++;
                } else {
                    $moodCounts['Manga']++;
                }
            }
        }
        arsort($moodCounts);
        $moodTop = array_key_first($moodCounts);

        return view('anilist.search', [
            'userData'            => $data['data']['User'],
            'mangaStats'          => $mangaStats,
            'novelStats'          => $novelStats,
            'animeMinutesBase'    => $animeMinutesBase,
            'animeMinutesRewatch' => $animeMinutesRewatch,
            'moodCounts'          => $moodCounts,
            'moodTop'             => $moodTop,
        ]);
    }

    public function myMangaProgress()
    {
        $username = 'yion';
        $query = '
        query ($name: String) {
            MediaListCollection(userName: $name, type: MANGA, status: CURRENT){
                lists {
                    entries {
                        progress
                        media {
                            title {
                                english
                                romaji
                            }
                            coverImage {
                                large
                            }
                            siteUrl
                            chapters
                            status
                        }
                    }
                }
            }
        }';
        $response = Http::post('https://graphql.anilist.co', [
            'query' => $query,
            'variables' => [
                'name' => $username,
            ],
        ]);
        $data = $response->json();
        if (isset($data['errors'])) {
            return back()->withErrors(['api_error' => $data['errors'][0]['message']]);
        }
        return view('anilist.manga', ['entries' => $data['data']['MediaListCollection']['lists'][0]['entries']]);
    }

    public function tagsView()
    {
        return view('anilist.tags');
    }

    public function processTagSearch(Request $request)
    {
        $request->validate([
            'username_input' => 'required|string',
        ]);

        $request->validate([
            'tag_relevance' => 'required|int',
        ]);


        return redirect()->route('anilist.tags_result', [
            'username' => $request->input('username_input'),
            'sort_by'  => $request->input('sort_by', 'count'),
            'sort_dir' => $request->input('sort_dir', 'desc'),
            'tagrel'   => $request->input('tag_relevance'),
        ]);
    }

    public function showTags(Request $request, $username)
    {
        $sortBy  = $request->input('sort_by', 'count');   // 'count', 'avg', or 'percent'
        $sortDir = $request->input('sort_dir', 'desc');   // 'desc' or 'asc'
        $tagrel = (int) $request->input('tagrel', 48); // 48 default
        $query = '
        query ($name: String) {
            anime: MediaListCollection(userName: $name, type: ANIME) {
                lists { isCustomList entries { score(format: POINT_100) media { id title { english romaji } coverImage { medium } tags { name rank } } } }
            }
            manga: MediaListCollection(userName: $name, type: MANGA) {
                lists { isCustomList entries { score(format: POINT_100) media { id format title { english romaji } coverImage { medium } tags { name rank } } } }
            }
        }';

        $response = Http::timeout(60)->post('https://graphql.anilist.co', [
            'query' => $query,
            'variables' => ['name' => $username],
        ]);

        $data = $response->json();
        if (isset($data['errors'])) {
            return redirect()->route('anilist.search')->withErrors(['api_error' => 'User not found or API error.']);
        }

        // Split manga collection into pure manga and novels before processing
        $pureMangaData = ['lists' => []];
        $novelData     = ['lists' => []];
        foreach ($data['data']['manga']['lists'] as $list) {
            if ($list['isCustomList']) continue;
            $pureMangaData['lists'][] = ['isCustomList' => false, 'entries' => array_values(array_filter($list['entries'], fn($e) => $e['media']['format'] !== 'NOVEL'))];
            $novelData['lists'][]     = ['isCustomList' => false, 'entries' => array_values(array_filter($list['entries'], fn($e) => $e['media']['format'] === 'NOVEL'))];
        }

        $processList = function($collection) use ($sortBy, $sortDir, $tagrel) {
            $stats = [];
            $totalEntries = 0;
            
            foreach ($collection['lists'] as $list) {
                if ($list['isCustomList']) continue;
                foreach ($list['entries'] as $entry) {
                    $score = $entry['score'];
                    
                    if ($score == 0) continue;
                    $totalEntries++;
                    
                    $mediaId = $entry['media']['id'];
                    $mediaData = [
                        'title' => $entry['media']['title']['english'] ?? $entry['media']['title']['romaji'],
                        'image' => $entry['media']['coverImage']['medium'],
                        'score' => $score
                    ];

                    foreach ($entry['media']['tags'] as $tag) {
                        // Tag Relevance
                        if ($tag['rank'] < $tagrel) continue;
                        $name = $tag['name'];
                        if (!isset($stats[$name])) {
                            $stats[$name] = ['count' => 0, 'totalScore' => 0, 'scoredCount' => 0, 'topSeries' => []];
                        }
                        $stats[$name]['count']++;
                        if ($score > 0) {
                            $stats[$name]['totalScore'] += $score;
                            $stats[$name]['scoredCount']++;
                        }
                        // Prevent repeated series within the same tag
                        $stats[$name]['topSeries'][$mediaId] = $mediaData;
                    }
                }
            }

            foreach ($stats as $name => &$tagData) {
                $tagData['avg'] = $tagData['scoredCount'] > 0 ? round($tagData['totalScore'] / $tagData['scoredCount'], 1) : 0;
                $tagData['percent'] = $totalEntries > 0 ? ($tagData['count'] / $totalEntries) * 100 : 0;
                
                // Sort top series by user score and take only top 5
                uasort($tagData['topSeries'], fn($a, $b) => $b['score'] <=> $a['score']);
                $tagData['topSeries'] = array_slice($tagData['topSeries'], 0, 5);
            }

            uasort($stats, function($a, $b) use ($sortBy, $sortDir) {
                if ($sortDir === 'asc') {
                    return $a[$sortBy] <=> $b[$sortBy];
                }
                return $b[$sortBy] <=> $a[$sortBy];
            });
            return $stats;
        };

        return view('anilist.tags', [
            'username'  => $username,
            'animeTags' => $processList($data['data']['anime']),
            'mangaTags' => $processList($pureMangaData),
            'novelTags' => $processList($novelData),
            'sortBy'    => $sortBy,
            'sortDir'   => $sortDir,
            'tagrel'    => $tagrel,
        ]);
    }
}