<?php

namespace LastFmApi\Api;

/**
 * Allows access to the api requests relating to users
 */
class UserApi extends BaseApi
{

    /**
     * Get a list of upcoming events that this user is attending
     * @deprecated as of march 15 2016, 'user.getevents' method is not available
     * @param array $methodVars An array with the following required values: <i>user</i>
     * @return array
     */
    public function getEvents($methodVars)
    {
        // Check for required variables
        if (!empty($methodVars['user'])) {
            $vars = array(
                'method' => 'user.getevents',
                'api_key' => $this->auth->apiKey
            );
            $vars = array_merge($vars, $methodVars);

            if ($call = $this->apiGetCall($vars)) {
                if ($call->events['total'] > 0) {
                    $i = 0;
                    foreach ($call->events->event as $event) {
                        $events[$i]['id'] = (string) $event->id;
                        $events[$i]['title'] = (string) $event->title;
                        $ii = 0;
                        foreach ($event->artists->artist as $artist) {
                            $events[$i]['artists'][$ii] = (string) $artist;
                            $ii++;
                        }
                        $events[$i]['headliner'] = (string) $event->artists->headliner;
                        $events[$i]['venue']['name'] = (string) $event->venue->name;
                        $events[$i]['venue']['location']['city'] = (string) $event->venue->location->city;
                        $events[$i]['venue']['location']['country'] = (string) $event->venue->location->country;
                        $events[$i]['venue']['location']['street'] = (string) $event->venue->location->street;
                        $events[$i]['venue']['location']['postalcode'] = (string) $event->venue->location->postalcode;
                        $geoPoints = $event->venue->location->children('http://www.w3.org/2003/01/geo/wgs84_pos#');
                        $events[$i]['venue']['location']['geopoint']['lat'] = (string) $geoPoints->point->lat;
                        $events[$i]['venue']['location']['geopoint']['long'] = (string) $geoPoints->point->long;
                        $events[$i]['venue']['timezone'] = (string) $event->venue->location->timezone;
                        $events[$i]['startDate'] = strtotime(trim((string) $event->startDate));
                        $events[$i]['description'] = (string) $event->description;
                        $events[$i]['images']['small'] = (string) $event->image[0];
                        $events[$i]['images']['medium'] = (string) $event->image[1];
                        $events[$i]['images']['large'] = (string) $event->image[2];
                        $events[$i]['attendance'] = (string) $event->attendance;
                        $events[$i]['reviews'] = (string) $event->reviews;
                        $events[$i]['tag'] = (string) $event->tag;
                        $events[$i]['url'] = (string) $event->url;
                        $i++;
                    }
                    return $events;
                } else {
                    $this->handleError(90, 'This user has no events');
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // Give a 91 error if incorrect variables are used
            $this->handleError(91, 'You must include artist variable in the call for this method');
            return false;
        }
    }

    /**
     * Get a list of the user's friends on Last.fm
     * @param array $methodVars An array with the following required values: <i>user</i>
     * @return array
     */
    public function getFriends($methodVars)
    {
        // Check for required variables
        if (!empty($methodVars['user'])) {
            $vars = array(
                'method' => 'user.getfriends',
                'api_key' => $this->auth->apiKey
            );
            $vars = array_merge($vars, $methodVars);

            if ($call = $this->apiGetCall($vars)) {
                if (count($call->friends->user) > 0) {
                    $i = 0;
                    foreach ($call->friends->user as $user) {
                        $friends[$i]['name'] = (string) $user->name;
                        $friends[$i]['images']['small'] = (string) $user->image[0];
                        $friends[$i]['images']['medium'] = (string) $user->image[1];
                        $friends[$i]['images']['large'] = (string) $user->image[2];
                        $friends[$i]['url'] = (string) $user->url;
                        if (!empty($recentTracks)) {
                            $friends[$i]['recenttrack']['artist']['name'] = (string) $user->recenttrack->artist->name;
                            $friends[$i]['recenttrack']['artist']['mbid'] = (string) $user->recenttrack->artist->mbid;
                            $friends[$i]['recenttrack']['artist']['url'] = (string) $user->recenttrack->artist->url;
                            $friends[$i]['recenttrack']['name'] = (string) $user->recenttrack->name;
                            $friends[$i]['recenttrack']['mbid'] = (string) $user->recenttrack->mbid;
                            $friends[$i]['recenttrack']['url'] = (string) $user->recenttrack->url;
                        }
                        $i++;
                    }

                    return $friends;
                } else {
                    $this->handleError(90, 'This user has no friends');
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // Give a 91 error if incorrect variables are used
            $this->handleError(91, 'You must include artist variable in the call for this method');
            return false;
        }
    }

    /**
     * Get information about a user profile (Requires full auth)
     * @param array $methodVars An array with the following required values: <i>user</i>
     * @return array
     */
    public function getInfo($methodVars = Array())
    {

        $vars = array(
            'method' => 'user.getinfo',
            'api_key' => $this->auth->apiKey
        );
        $vars = array_merge($vars, $methodVars);
        $apiSig = $this->apiSig($this->auth->secret, $vars);
        $vars['api_sig'] = $apiSig;

        if ($call = $this->apiGetCall($vars)) {
            if(isset($call->user))
            {
                $img = (array)$call->user->image[2];
                $img = $img["#text"];
                $info['name'] = (string) $call->user->name;
                //$info['realname'] = (string) $call->user->realname;
                $info['url'] = (string) $call->user->url;
                $info['image'] = (string) $img;
                $info['country'] = (string) $call->user->country;
                $info['age'] = (string) $call->user->age;
                $info['gender'] = (string) $call->user->gender;
                $info['subscriber'] = (string) $call->user->subscriber;
                $info['playcount'] = (string) $call->user->playcount;
                $info['playlists'] = (string) $call->user->playlists;
                $info['registered'] = (int) $call->user->registered->unixtime;

                return $info;
            }
            else
            {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Get the last 50 tracks loved by a user
     * @param array $methodVars An array with the following required values: <i>user</i>
     * @return array
     */
    public function getLovedTracks($methodVars)
    {
        // Check for required variables
        if (!empty($methodVars['user'])) {
            $vars = array(
                'method' => 'user.getlovedtracks',
                'api_key' => $this->auth->apiKey
            );
            $vars = array_merge($vars, $methodVars);

            if ($call = $this->apiGetCall($vars)) {
                if (count($call->lovedtracks->track) > 0) {
                    $i = 0;
                    foreach ($call->lovedtracks->track as $track) {
                        $lovedTracks[$i]['name'] = (string) $track->name;
                        $lovedTracks[$i]['mbid'] = (string) $track->mbid;
                        $lovedTracks[$i]['url'] = (string) $track->url;
                        $lovedTracks[$i]['date'] = (string) $track->date['uts'];
                        $lovedTracks[$i]['artist']['name'] = (string) $track->artist->name;
                        $lovedTracks[$i]['artist']['mbid'] = (string) $track->artist->mbid;
                        $lovedTracks[$i]['artist']['url'] = (string) $track->artist->url;
                        $lovedTracks[$i]['images']['small'] = (string) $track->image[0];
                        $lovedTracks[$i]['images']['medium'] = (string) $track->image[1];
                        $lovedTracks[$i]['images']['large'] = (string) $track->image[2];
                        $i++;
                    }

                    return $lovedTracks;
                } else {
                    $this->handleError(90, 'This user has no loved tracks');
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // Give a 91 error if incorrect variables are used
            $this->handleError(91, 'You must include artist variable in the call for this method');
            return false;
        }
    }

    /**
     * Get a list of a user's neighbours on Last.fm
     * @deprecated as of march 15 2016, 'user.getneighbours' method is not available
     * @param array $methodVars An array with the following required values: <i>user</i>
     * @return array
     */
    public function getNeighbours($methodVars)
    {
        // Check for required variables
        if (!empty($methodVars['user'])) {
            $vars = array(
                'method' => 'user.getneighbours',
                'api_key' => $this->auth->apiKey
            );
            $vars = array_merge($vars, $methodVars);
            if (!empty($methodVars['limit'])) {
                $vars['limit'] = $methodVars['limit'];
            }

            if ($call = $this->apiGetCall($vars)) {
                if (count($call->neighbours->user) > 0) {
                    $i = 0;
                    foreach ($call->neighbours->user as $user) {
                        $neighbours[$i]['name'] = (string) $user->name;
                        $neighbours[$i]['url'] = (string) $user->url;
                        $neighbours[$i]['image'] = (string) $user->image;
                        $neighbours[$i]['match'] = (string) $user->match;
                        $i++;
                    }

                    return $neighbours;
                } else {
                    $this->handleError(90, 'This user has no neighbours');
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // Give a 91 error if incorrect variables are used
            $this->handleError(91, 'You must include artist variable in the call for this method');
            return false;
        }
    }

    /**
     * Get a paginated list of all events a user has attended in the past
     * @deprecated as of march 15 2016, 'user.getpastevents' method is not available
     * @param array $methodVars An array with the following required values: <i>user</i>
     * @return array
     */
    public function getPastEvents($methodVars)
    {
        // Check for required variables
        if (!empty($methodVars['user'])) {
            $vars = array(
                'method' => 'user.getpastevents',
                'api_key' => $this->auth->apiKey
            );
            $vars = array_merge($vars, $methodVars);

            if ($call = $this->apiGetCall($vars)) {
                if ($call->events['total'] > 0) {
                    $i = 0;
                    foreach ($call->events->event as $event) {
                        $pastEvents[$i]['id'] = (string) $event->id;
                        $pastEvents[$i]['title'] = (string) $event->title;
                        $ii = 0;
                        foreach ($event->artists->artist as $artist) {
                            $pastEvents[$i]['artists'][$ii] = (string) $artist;
                            $ii++;
                        }
                        $pastEvents[$i]['headliner'] = (string) $event->artists->headliner;
                        $pastEvents[$i]['venue']['name'] = (string) $event->venue->name;
                        $pastEvents[$i]['venue']['location']['city'] = (string) $event->venue->location->city;
                        $pastEvents[$i]['venue']['location']['country'] = (string) $event->venue->location->country;
                        $pastEvents[$i]['venue']['location']['street'] = (string) $event->venue->location->street;
                        $pastEvents[$i]['venue']['location']['postalcode'] = (string) $event->venue->location->postalcode;
                        $geoPoints = $event->venue->location->children('http://www.w3.org/2003/01/geo/wgs84_pos#');
                        $pastEvents[$i]['venue']['location']['geopoint']['lat'] = (string) $geoPoints->point->lat;
                        $pastEvents[$i]['venue']['location']['geopoint']['long'] = (string) $geoPoints->point->long;
                        $pastEvents[$i]['startDate'] = strtotime(trim((string) $event->startDate));
                        $pastEvents[$i]['description'] = (string) $event->description;
                        $pastEvents[$i]['images']['small'] = (string) $event->image[0];
                        $pastEvents[$i]['images']['medium'] = (string) $event->image[1];
                        $pastEvents[$i]['images']['large'] = (string) $event->image[2];
                        $pastEvents[$i]['attendance'] = (string) $event->attendance;
                        $pastEvents[$i]['reviews'] = (string) $event->reviews;
                        $pastEvents[$i]['tag'] = (string) $event->tag;
                        $pastEvents[$i]['url'] = (string) $event->url;
                        $i++;
                    }

                    return $pastEvents;
                } else {
                    $this->handleError(90, 'This user has no past events');
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // Give a 91 error if incorrect variables are used
            $this->handleError(91, 'You must include artist variable in the call for this method');
            return false;
        }
    }

    /**
     * Get a list of a user's playlists on Last.fm
     * @deprecated as of march 15 2016, 'user.getplaylists' method is not available
     * @param array $methodVars An array with the following required values: <i>user</i>
     * @return array
     */
    public function getPlaylists($methodVars)
    {
        // Check for required variables
        if (!empty($methodVars['user'])) {
            $vars = array(
                'method' => 'user.getplaylists',
                'api_key' => $this->auth->apiKey
            );
            $vars = array_merge($vars, $methodVars);

            if ($call = $this->apiGetCall($vars)) {
                if (count($call->playlists->playlist) > 0) {
                    $i = 0;
                    foreach ($call->playlists->playlist as $playlist) {
                        $playlists[$i]['id'] = (string) $playlist->id;
                        $playlists[$i]['title'] = (string) $playlist->title;
                        $playlists[$i]['date'] = strtotime(trim((string) $playlist->date));
                        $playlists[$i]['size'] = (string) $playlist->size;
                        $playlists[$i]['streamalbe'] = (string) $playlist->streamable;
                        $playlists[$i]['creator'] = (string) $playlist->creator;
                        $i++;
                    }

                    return $playlists;
                } else {
                    $this->handleError(90, 'This user has no past events');
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // Give a 91 error if incorrect variables are used
            $this->handleError(91, 'You must include artist variable in the call for this method');
            return false;
        }
    }

    /**
     * Get a list of the recent tracks listened to by this user. Indicates now playing track if the user is currently listening
     * @param array $methodVars An array with the following required values: <i>user</i>
     * @return array
     */
    public function getRecentTracks($methodVars)
    {
        // Check for required variables
        if (!empty($methodVars['user'])) {
            $vars = array(
                'method' => 'user.getrecenttracks',
                'api_key' => $this->auth->apiKey
            );
            $vars = array_merge($vars, $methodVars);

            if ($call = $this->apiGetCall($vars)) {
                if (property_exists($call, "recenttracks") && count($call->recenttracks->track) > 0) {
                    $i = 0;
                    $recentTracks = [];
                    foreach ($call->recenttracks->track as $track) {
                        $artist = (array) $track->artist;
                        $album = (array) $track->album;
                        $image[0] = (array) $track->image[0];
                        $image[1] = (array) $track->image[1];
                        $image[2] = (array) $track->image[2];
                        $recentTracks[$i]['name'] = (string) $track->name;
                        $recentTracks[$i]['mbid'] = (string) $track->mbid;
                        $recentTracks[$i]['url'] = (string) $track->url;
                        //$recentTracks[$i]['date'] = (string) $track->date['uts'];
                        $recentTracks[$i]['streamable'] = (string) $track->streamable;
                        $recentTracks[$i]['artist']['name'] = (string) $artist["#text"];
                        $recentTracks[$i]['artist']['mbid'] = (string) $artist['mbid'];
                        $recentTracks[$i]['album']['name'] = (string) $album['#text'];
                        $recentTracks[$i]['album']['mbid'] = (string) $album['mbid'];
                        $recentTracks[$i]['images']['small'] = (string) $image[0]['#text'];
                        $recentTracks[$i]['images']['medium'] = (string) $image[1]['#text'];
                        $recentTracks[$i]['images']['large'] = (string) $image[2]['#text'];
                        $i++;
                    }

                    return $recentTracks;
                } else {
                    $this->handleError(90, 'This user has no recent tracks');
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // Give a 91 error if incorrect variables are used
            $this->handleError(91, 'You must include artist variable in the call for this method');
            return false;
        }
    }

    /**
     * Get Last.fm artist recommendations for a user (Requires full auth)
     * @deprecated as of march 15 2016, 'user.getrecommendedartists' method is not available
     * @param array $methodVars An array with the following optional values: <i>limit</i>, <i>page</i>
     * @return array
     */
    public function getRecommendedArtists($methodVars = Array())
    {
        // Only allow full authed calls
        if ($this->fullAuth == true) {
            $vars = array(
                'method' => 'user.getrecommendedartists',
                'api_key' => $this->auth->apiKey,
                'sk' => $this->auth->sessionKey
            );
            $vars = array_merge($vars, $methodVars);
            $apiSig = $this->apiSig($this->auth->secret, $vars);
            $vars['api_sig'] = $apiSig;

            if ($call = $this->apiGetCall($vars)) {
                if (count($call->recommendations->artist) > 0) {
                    $reccomendedArtists['user'] = (string) $call->recommendations['user'];
                    $reccomendedArtists['page'] = (string) $call->recommendations['page'];
                    $reccomendedArtists['perPage'] = (string) $call->recommendations['perPage'];
                    $reccomendedArtists['totalPages'] = (string) $call->recommendations['totalPages'];
                    $reccomendedArtists['total'] = (string) $call->recommendations['total'];

                    $i = 0;
                    foreach ($call->recommendations->artist as $artist) {
                        $reccomendedArtists['artists'][$i]['name'] = (string) $artist->name;
                        $reccomendedArtists['artists'][$i]['mbid'] = (string) $artist->mbid;
                        $reccomendedArtists['artists'][$i]['url'] = (string) $artist->url;
                        $reccomendedArtists['artists'][$i]['streamable'] = (string) $artist->streamable;
                        $reccomendedArtists['artists'][$i]['image']['small'] = (string) $artist->image[0];
                        $reccomendedArtists['artists'][$i]['image']['medium'] = (string) $artist->image[1];
                        $reccomendedArtists['artists'][$i]['image']['large'] = (string) $artist->image[2];
                        $i++;
                    }

                    return $reccomendedArtists;
                } else {
                    $this->handleError(90, 'This user has no recommendations');
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // Give a 92 error if not fully authed
            $this->handleError(92, 'Method requires full auth. Call auth.getSession using lastfmApiAuth class');
            return false;
        }
    }

    /**
     * Get a paginated list of all events recommended to a user by Last.fm, based on their listening profile (Requires full auth)
     * @deprecated as of march 15 2016, 'user.getrecommendedevents' method is not available
     * @param array $methodVars An array with the following optional values: <i>page</i>, <i>limit</i>
     * @return array
     */
    public function getRecommendedEvents($methodVars = Array())
    {
        // Only allow full authed calls
        if ($this->fullAuth == true) {
            $vars = array(
                'method' => 'user.getrecommendedevents',
                'api_key' => $this->auth->apiKey,
                'sk' => $this->auth->sessionKey
            );
            $vars = array_merge($vars, $methodVars);
            $apiSig = $this->apiSig($this->auth->secret, $vars);
            $vars['api_sig'] = $apiSig;

            if ($call = $this->apiGetCall($vars)) {
                if (count($call->events->event) > 0) {
                    $reccomendedEvents['user'] = (string) $call->events['user'];
                    $reccomendedEvents['page'] = (string) $call->events['page'];
                    $reccomendedEvents['perPage'] = (string) $call->events['perPage'];
                    $reccomendedEvents['totalPages'] = (string) $call->events['totalPages'];
                    $reccomendedEvents['total'] = (string) $call->events['total'];

                    $i = 0;
                    foreach ($call->events->event as $event) {
                        $reccomendedEvents['events'][$i]['id'] = (string) $event->id;
                        $reccomendedEvents['events'][$i]['title'] = (string) $event->title;
                        $ii = 0;
                        foreach ($event->artists->artist as $artist) {
                            $reccomendedEvents['events'][$i]['artists'][$ii] = (string) $artist;
                            $ii++;
                        }
                        $reccomendedEvents['events'][$i]['headliner'] = (string) $event->artists->headliner;
                        $reccomendedEvents['events'][$i]['venue']['name'] = (string) $event->venue->name;
                        $reccomendedEvents['events'][$i]['venue']['location']['city'] = (string) $event->venue->location->city;
                        $reccomendedEvents['events'][$i]['venue']['location']['country'] = (string) $event->venue->location->country;
                        $reccomendedEvents['events'][$i]['venue']['location']['street'] = (string) $event->venue->location->street;
                        $reccomendedEvents['events'][$i]['venue']['location']['postcode'] = (string) $event->venue->location->postalcode;
                        $geopoint = $event->venue->location->children('http://www.w3.org/2003/01/geo/wgs84_pos#');
                        $reccomendedEvents['events'][$i]['venue']['location']['geopoint']['lat'] = (string) $geopoint->point->lat;
                        $reccomendedEvents['events'][$i]['venue']['location']['geopoint']['long'] = (string) $geopoint->point->long;
                        $reccomendedEvents['events'][$i]['venue']['location']['timezone'] = (string) $event->venue->location->timezone;
                        $reccomendedEvents['events'][$i]['venue']['url'] = (string) $call->venue->url;
                        $reccomendedEvents['events'][$i]['startdate'] = strtotime(trim((string) $event->startDate));
                        $reccomendedEvents['events'][$i]['description'] = (string) $event->description;
                        $reccomendedEvents['events'][$i]['image']['small'] = (string) $event->image[0];
                        $reccomendedEvents['events'][$i]['image']['medium'] = (string) $event->image[1];
                        $reccomendedEvents['events'][$i]['image']['large'] = (string) $event->image[2];
                        $reccomendedEvents['events'][$i]['attendance'] = (string) $event->attendance;
                        $reccomendedEvents['events'][$i]['reviews'] = (string) $event->reviews;
                        $reccomendedEvents['events'][$i]['tag'] = (string) $event->tag;
                        $reccomendedEvents['events'][$i]['url'] = (string) $event->url;
                        $i++;
                    }

                    return $reccomendedEvents;
                } else {
                    $this->handleError(90, 'This user has no recommendations');
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // Give a 92 error if not fully authed
            $this->handleError(92, 'Method requires full auth. Call auth.getSession using lastfmApiAuth class');
            return false;
        }
    }

    /**
     * Get shouts for this user
     * @param array $methodVars An array with the following required values: <i>user</i>
     * @deprecated as of march 15 2016, 'user.getshouts' method is not available
     * @return array
     */
    public function getShouts($methodVars)
    {
        if (!empty($methodVars['user'])) {
            $vars = array(
                'method' => 'user.getshouts',
                'api_key' => $this->auth->apiKey
            );
            $vars = array_merge($vars, $methodVars);

            if ($call = $this->apiGetCall($vars)) {
                $shouts['user'] = (string) $call->shouts['user'];
                $shouts['total'] = (string) $call->shouts['total'];
                $i = 0;
                foreach ($call->shouts->shout as $shout) {
                    $shouts['shouts'][$i]['body'] = (string) $shout->body;
                    $shouts['shouts'][$i]['author'] = (string) $shout->author;
                    $shouts['shouts'][$i]['date'] = strtotime((string) $shout->date);
                    $i++;
                }

                return $shouts;
            } else {
                return false;
            }
        } else {
            // Give a 91 error if incorrect variables are used
            $this->handleError(91, 'You must include artist variable in the call for this method');
            return false;
        }
    }

    /**
     * Get the top albums listened to by a user. You can stipulate a time period. Sends the overall chart by default
     * @param array $methodVars An array with the following required values: <i>user</i> and optional value: <i>period</i>
     * @return array
     */
    public function getTopAlbums($methodVars)
    {
        // Check for required variables
        if (!empty($methodVars['user'])) {
            $vars = array(
                'method' => 'user.gettopalbums',
                'api_key' => $this->auth->apiKey
            );
            $vars = array_merge($vars, $methodVars);

            if ($call = $this->apiGetCall($vars)) {
                $topalbums = [];
                if (property_exists($call, "topalbums") && count($call->topalbums->album) > 0) {
                    $info = (array)($call->topalbums);
                    $info = $info["@attr"];

                    $topalbums["info"] = $info;
                    $i = 0;
                    foreach ($call->topalbums->album as $album) {
                        $a = (array)$album;
                        $image[0] = (array) $album->image[0];
                        $image[1] = (array) $album->image[1];
                        $image[2] = (array) $album->image[2];
                        $topalbums[$i]['name'] = (string) $album->name;
                        $topalbums[$i]['rank'] = (string) $a["@attr"]->rank;
                        $topalbums[$i]['playcount'] = (string) $album->playcount;
                        $topalbums[$i]['mbid'] = (string) $album->mbid;
                        $topalbums[$i]['url'] = (string) $album->url;
                        $topalbums[$i]['artist']['name'] = (string) $album->artist->name;
                        $topalbums[$i]['artist']['mbid'] = (string) $album->artist->mbid;
                        $topalbums[$i]['artist']['url'] = (string) $album->artist->url;
                        $topalbums[$i]['images']['small'] = (string) $image[0]["#text"];
                        $topalbums[$i]['images']['medium'] = (string) $image[1]["#text"];
                        $topalbums[$i]['images']['large'] = (string) $image[2]["#text"];
                        $i++;
                    }

                    return $topalbums;
                } else {
                    $this->handleError(90, 'This user has no top albums');
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // Give a 91 error if incorrect variables are used
            $this->handleError(91, 'You must include artist variable in the call for this method');
            return false;
        }
    }

    /**
     * Get the top artists listened to by a user. You can stipulate a time period. Sends the overall chart by default
     * @param array $methodVars An array with the following required values: <i>user</i> and optional value: <i>period</i>
     * @return array
     */
    public function getTopArtists($methodVars)
    {
        // Check for required variables
        if (!empty($methodVars['user'])) {
            $vars = array(
                'method' => 'user.gettopartists',
                'api_key' => $this->auth->apiKey
            );
            $vars = array_merge($vars, $methodVars);

            if ($call = $this->apiGetCall($vars)) {
                $topartists = [];
                if (property_exists($call, "topartists") && count($call->topartists->artist) > 0) {
                    $info = (array)($call->topartists);
                    $info = $info["@attr"];

                    $topartists["info"] = $info;
                    $i = 0;
                    foreach ($call->topartists->artist as $artist) {
                        $a = (array)$artist;
                        $img = (array)$a["image"];
                        $image[0] = (array)$img[0];
                        $image[1] = (array)$img[1];
                        $image[2] = (array)$img[2];
                        $image[3] = (array)$img[3];
                        $image[4] = (array)$img[4];
                        $topartists[$i]['name'] = (string) $artist->name;
                        $topartists[$i]['rank'] = (string) $a["@attr"]->rank;
                        $topartists[$i]['playcount'] = (string) $artist->playcount;
                        $topartists[$i]['mbid'] = (string) $artist->mbid;
                        $topartists[$i]['url'] = (string) $artist->url;
                        $topartists[$i]['streamable'] = (string) $artist->streamable;
                        $topartists[$i]['images']['small'] = (string) $image[0]["#text"];
                        $topartists[$i]['images']['medium'] = (string) $image[1]["#text"];
                        $topartists[$i]['images']['large'] = (string) $image[2]["#text"];
                        $topartists[$i]['images']['extralarge'] = (string) $image[3]["#text"];
                        $topartists[$i]['images']['mega'] = (string) $image[4]["#text"];
                        $i++;
                    }

                    return $topartists;
                } else {
                    $this->handleError(90, 'This user has no top artists');
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // Give a 91 error if incorrect variables are used
            $this->handleError(91, 'You must include artist variable in the call for this method');
            return false;
        }
    }

    /**
     * Get the top tags used by this user
     * @param array $methodVars An array with the following required values: <i>user</i> and optional value: <i>limit</i>
     * @return array
     */
    public function getTopTags($methodVars)
    {
        // Check for required variables
        if (!empty($methodVars['user'])) {
            $vars = array(
                'method' => 'user.gettoptags',
                'api_key' => $this->auth->apiKey
            );
            $vars = array_merge($vars, $methodVars);

            if ($call = $this->apiGetCall($vars)) {
                if (count($call->toptags->tag) > 0) {
                    $i = 0;
                    foreach ($call->toptags->tag as $tag) {
                        $toptags[$i]['name'] = (string) $tag->name;
                        $toptags[$i]['count'] = (string) $tag->count;
                        $toptags[$i]['url'] = (string) $tag->url;
                        $i++;
                    }

                    return $toptags;
                } else {
                    $this->handleError(90, 'This user has no top tags');
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // Give a 91 error if incorrect variables are used
            $this->handleError(91, 'You must include artist variable in the call for this method');
            return false;
        }
    }

    /**
     * Get the top tracks listened to by a user. You can stipulate a time period. Sends the overall chart by default
     * @param array $methodVars An array with the following required values: <i>user</i> and optional value: <i>period</i>
     * @return array
     */
    public function getTopTracks($methodVars)
    {
        // Check for required variables
        if (!empty($methodVars['user'])) {
            $vars = array(
                'method' => 'user.gettoptracks',
                'api_key' => $this->auth->apiKey
            );
            $vars = array_merge($vars, $methodVars);

            if ($call = $this->apiGetCall($vars)) {
                $toptracks = [];
                if (property_exists($call, "toptracks") && count($call->toptracks->track) > 0) {
                    $info = (array)($call->toptracks);
                    $info = $info["@attr"];

                    $toptracks["info"] = $info;
                    $i = 0;
                    foreach ($call->toptracks->track as $track) {
                        $t = (array)$track;
                        $image[0] = (array) $track->image[0];
                        $image[1] = (array) $track->image[1];
                        $image[2] = (array) $track->image[2];

                        $toptracks[$i]['name'] = (string) $track->name;
                        $toptracks[$i]['rank'] = (string) $t["@attr"]->rank;
                        $toptracks[$i]['playcount'] = (string) $track->playcount;
                        $toptracks[$i]['mbid'] = (string) $track->mbid;
                        $toptracks[$i]['url'] = (string) $track->url;
                        //$toptracks[$i]['streamable'] = (string) $track->streamable;
                        //$toptracks[$i]['fulltrack'] = (string) $track->streamable['fulltrack'];
                        $toptracks[$i]['artist']['name'] = (string) $track->artist->name;
                        $toptracks[$i]['artist']['mbid'] = (string) $track->artist->mbid;
                        $toptracks[$i]['artist']['url'] = (string) $track->artist->url;
                        $toptracks[$i]['images']['small'] = (string) $image[0]['#text'];
                        $toptracks[$i]['images']['medium'] = (string) $image[1]['#text'];
                        $toptracks[$i]['images']['large'] = (string) $image[2]['#text'];
                        $i++;
                    }

                    return $toptracks;
                } else {
                    $this->handleError(90, 'This user has no top tracks');
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // Give a 91 error if incorrect variables are used
            $this->handleError(91, 'You must include artist variable in the call for this method');
            return false;
        }
    }

    /**
     * Get an album chart for a user profile, for a given date range. If no date range is supplied, it will return the most recent album chart for this user
     * @param array $methodVars An array with the following required values: <i>user</i> and optional values: <i>to</i>, <i>from</i>
     * @return array
     */
    public function getWeeklyAlbumChart($methodVars)
    {
        // Check for required variables
        if (!empty($methodVars['user'])) {
            $vars = array(
                'method' => 'user.getweeklyalbumchart',
                'api_key' => $this->auth->apiKey
            );
            $vars = array_merge($vars, $methodVars);

            if ($call = $this->apiGetCall($vars)) {
                $i = 0;
                $weeklyalbums = array();
                foreach ($call->weeklyalbumchart->album as $album) {
                    $a = (array)$album;
                    $ac = (array)$a['artist'];
                    $weeklyalbums[$i]['name'] = (string) $album->name;
                    $weeklyalbums[$i]['rank'] = (string) $a["@attr"]->rank;
                    $weeklyalbums[$i]['artist']['name'] = (string) $ac['#text'];
                    $weeklyalbums[$i]['artist']['mbid'] = (string) $ac['mbid'];
                    $weeklyalbums[$i]['mbid'] = (string) $album->mbid;
                    $weeklyalbums[$i]['playcount'] = (string) $album->playcount;
                    $weeklyalbums[$i]['url'] = (string) $album->url;
                    $i++;
                }

                return $weeklyalbums;
            } else {
                return false;
            }
        } else {
            // Give a 91 error if incorrect variables are used
            $this->handleError(91, 'You must include artist variable in the call for this method');
            return false;
        }
    }

    /**
     * Get an artist chart for a user profile, for a given date range. If no date range is supplied, it will return the most recent artist chart for this user
     * @param array $methodVars An array with the following required values: <i>user</i> and optional values: <i>to</i>, <i>from</i>
     * @return array
     */
    public function getWeeklyArtistChart($methodVars)
    {
        // Check for required variables
        if (!empty($methodVars['user'])) {
            $vars = array(
                'method' => 'user.getweeklyartistchart',
                'api_key' => $this->auth->apiKey
            );
            $vars = array_merge($vars, $methodVars);

            if ($call = $this->apiGetCall($vars)) {
                $i = 0;
                $weeklyartists = array();
                foreach ($call->weeklyartistchart->artist as $artist) {
                    $a = (array)$artist;
                    $weeklyartists[$i]['name'] = (string) $artist->name;
                    $weeklyartists[$i]['rank'] = (string) $a["@attr"]->rank;
                    $weeklyartists[$i]['mbid'] = (string) $artist->mbid;
                    $weeklyartists[$i]['playcount'] = (string) $artist->playcount;
                    $weeklyartists[$i]['url'] = (string) $artist->url;
                    $i++;
                }

                return $weeklyartists;
            } else {
                return false;
            }
        } else {
            // Give a 91 error if incorrect variables are used
            $this->handleError(91, 'You must include artist variable in the call for this method');
            return false;
        }
    }

    /**
     * Get a list of available charts for this user, expressed as date ranges which can be sent to the chart services
     * @param array $methodVars An array with the following required values: <i>user</i>
     * @return array
     */
    public function getWeeklyChartList($methodVars)
    {
        // Check for required variables
        if (!empty($methodVars['user'])) {
            $vars = array(
                'method' => 'user.getweeklychartlist',
                'api_key' => $this->auth->apiKey
            );
            $vars = array_merge($vars, $methodVars);

            if ($call = $this->apiGetCall($vars)) {
                $i = 0;
                foreach ($call->weeklychartlist->chart as $chart) {
                    $weeklychartlist[$i]['from'] = (string) $chart->from;
                    $weeklychartlist[$i]['to'] = (string) $chart->to;
                    $i++;
                }

                return $weeklychartlist;
            } else {
                return false;
            }
        } else {
            // Give a 91 error if incorrect variables are used
            $this->handleError(91, 'You must include artist variable in the call for this method');
            return false;
        }
    }

    /**
     * Get a track chart for a user profile, for a given date range. If no date range is supplied, it will return the most recent track chart for this user
     * @param array $methodVars An array with the following required values: <i>user</i> and optional values: <i>to</i>, <i>from</i>
     * @return array
     */
    public function getWeeklyTrackChart($methodVars)
    {
        // Check for required variables
        if (!empty($methodVars['user'])) {
            $vars = array(
                'method' => 'user.getweeklytrackchart',
                'api_key' => $this->auth->apiKey
            );
            $vars = array_merge($vars, $methodVars);

            if ($call = $this->apiGetCall($vars)) {
                $i = 0;
                $weeklytracks = array();
                foreach ($call->weeklytrackchart->track as $track) {
                    $t = (array)$track;
                    $a = (array)$t['artist'];
                    $weeklytracks[$i]['name'] = (string) $track->name;
                    $weeklytracks[$i]['rank'] = (string) $t["@attr"]->rank;
                    $weeklytracks[$i]['artist']['name'] = (string) $a["#text"];
                    $weeklytracks[$i]['artist']['mbid'] = (string) $a['mbid'];
                    $weeklytracks[$i]['mbid'] = (string) $track->mbid;
                    $weeklytracks[$i]['playcount'] = (string) $track->playcount;
                    $weeklytracks[$i]['url'] = (string) $track->url;
                    $i++;
                }

                return $weeklytracks;
            } else {
                return false;
            }
        } else {
            // Give a 91 error if incorrect variables are used
            $this->handleError(91, 'You must include artist variable in the call for this method');
            return false;
        }
    }

    /**
     * Shout on this user's shoutbox (Requires full auth)
     * @param array $methodVars An array with the following required values: <i>user</i>, <i>message</i>
     * @return boolean
     */
    public function shout($methodVars)
    {
        // Only allow full authed calls
        if ($this->fullAuth == true) {
            // Check for required variables
            if (!empty($methodVars['user']) && !empty($methodVars['message'])) {
                $vars = array(
                    'method' => 'user.shout',
                    'api_key' => $this->auth->apiKey,
                    'sk' => $this->auth->sessionKey
                );
                $vars = array_merge($vars, $methodVars);
                $sig = $this->apiSig($this->auth->secret, $vars);
                $vars['api_sig'] = $sig;

                if ($call = $this->apiPostCall($vars)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                // Give a 91 error if incorrect variables are used
                $this->handleError(91, 'You must include user and message variables in the call for this method');
                return false;
            }
        } else {
            // Give a 92 error if not fully authed
            $this->handleError(92, 'Method requires full auth. Call auth.getSession using lastfmApiAuth class');
            return false;
        }
    }

}
