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
        return view('anilist.search', ['userData' => $data['data']['User']]);
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

        return redirect()->route('anilist.tags_result', ['username' => $request->input('username_input')]);
    }

    public function showTags($username)
    {
        $query = '
        query ($name: String) {
            anime: MediaListCollection(userName: $name, type: ANIME) {
                lists { entries { score(format: POINT_100) media { id title { english romaji } coverImage { medium } tags { name } } } }
            }
            manga: MediaListCollection(userName: $name, type: MANGA) {
                lists { entries { score(format: POINT_100) media { id title { english romaji } coverImage { medium } tags { name } } } }
            }
        }';

        $response = Http::post('https://graphql.anilist.co', [
            'query' => $query,
            'variables' => ['name' => $username],
        ]);

        $data = $response->json();
        if (isset($data['errors'])) {
            return redirect()->route('anilist.search')->withErrors(['api_error' => 'User not found or API error.']);
        }

        $processList = function($collection) {
            $stats = [];
            $totalEntries = 0;
            
            foreach ($collection['lists'] as $list) {
                foreach ($list['entries'] as $entry) {
                    $totalEntries++;
                    $score = $entry['score'];
                    $mediaId = $entry['media']['id'];
                    $mediaData = [
                        'title' => $entry['media']['title']['english'] ?? $entry['media']['title']['romaji'],
                        'image' => $entry['media']['coverImage']['medium'],
                        'score' => $score
                    ];

                    foreach ($entry['media']['tags'] as $tag) {
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

            uasort($stats, fn($a, $b) => $b['count'] <=> $a['count']);
            return $stats;
        };

        return view('anilist.tags', [
            'username' => $username,
            'animeTags' => $processList($data['data']['anime']),
            'mangaTags' => $processList($data['data']['manga'])
        ]);
    }
}