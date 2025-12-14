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

    // Search request on the index.blade
    public function search(Request $request)
    {
        $request->validate([
            'username_input' => 'required|string',
        ]);

        $searchName = $request->input('username_input');

        $query = '
        query ($name: String) {
            User (name: $name) {
                id
                name
                avatar { large }
                siteUrl
                createdAt
                previousNames { name createdAt }
                statistics {
                    anime {
                        count
                        minutesWatched
                        episodesWatched
                        meanScore
                        tags (limit: 5, sort: COUNT_DESC) { 
                            count
                            meanScore
                            tag { name description }
                        }
                    }
                    manga { count chaptersRead meanScore }
                }
            }
        }';

        $response = Http::post('https://graphql.anilist.co', [
            'query' => $query,
            'variables' => [
                'name' => $searchName,
            ],
        ]);
        // Unpackage
        $data = $response->json();

        if (isset($data['errors'])) {
            return back()->withErrors(['api_error' => $data['errors'][0]['message']]);
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
}